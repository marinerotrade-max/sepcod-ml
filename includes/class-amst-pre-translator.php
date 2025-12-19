<?php
/**
 * Pre-translation tool for batch processing.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AMST_Pre_Translator class.
 * 
 * Provides admin-only tool to pre-generate translations for all content.
 * This improves frontend performance by ensuring translations exist before rendering.
 */
class AMST_Pre_Translator {
	
	/**
	 * Translator instance.
	 *
	 * @var AMST_Translator
	 */
	private $translator;
	
	/**
	 * Cache instance.
	 *
	 * @var AMST_Cache
	 */
	private $cache;
	
	/**
	 * Database instance.
	 *
	 * @var AMST_Database
	 */
	private $database;
	
	/**
	 * Batch size for processing.
	 *
	 * @var int
	 */
	private $batch_size = 5;
	
	/**
	 * Constructor.
	 *
	 * @param AMST_Translator $translator Translator instance.
	 * @param AMST_Cache      $cache Cache instance.
	 * @param AMST_Database   $database Database instance.
	 */
	public function __construct( $translator, $cache, $database ) {
		$this->translator = $translator;
		$this->cache      = $cache;
		$this->database   = $database;
		
		// Register AJAX handlers (admin only).
		add_action( 'wp_ajax_amst_prepare_translations', array( $this, 'ajax_prepare_translations' ) );
		add_action( 'wp_ajax_amst_get_preparation_status', array( $this, 'ajax_get_status' ) );
	}
	
	/**
	 * AJAX handler for preparing translations.
	 */
	public function ajax_prepare_translations() {
		check_ajax_referer( 'amst-prepare-translations', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'auto-multilingual-seo' ) ) );
		}
		
		// Safety check: Ensure required objects are initialized.
		if ( null === $this->translator || null === $this->cache ) {
			wp_send_json_error( array(
				'message' => __( 'Translation system not properly initialized. Please refresh the page and try again.', 'auto-multilingual-seo' ),
			) );
		}
		
		$batch = isset( $_POST['batch'] ) ? absint( $_POST['batch'] ) : 0;
		$type  = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'pages';
		
		$result = $this->process_batch( $type, $batch );
		
		wp_send_json_success( $result );
	}
	
	/**
	 * AJAX handler for getting preparation status.
	 */
	public function ajax_get_status() {
		check_ajax_referer( 'amst-prepare-translations', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'auto-multilingual-seo' ) ) );
		}
		
		$status = $this->get_preparation_status();
		
		wp_send_json_success( $status );
	}
	
	/**
	 * Process a batch of content items.
	 *
	 * @param string $type Content type (pages, posts, directorist, menus, widgets, ui_strings).
	 * @param int    $batch Batch number.
	 * @return array Processing result.
	 */
	private function process_batch( $type, $batch ) {
		$enabled_languages = get_option( 'amst_enabled_languages', array( 'en', 'de', 'fr' ) );
		$default_language  = get_option( 'amst_default_language', 'en' );
		
		// Remove default language from target languages.
		$target_languages = array_diff( $enabled_languages, array( $default_language ) );
		
		if ( empty( $target_languages ) ) {
			return array(
				'complete'   => true,
				'processed'  => 0,
				'skipped'    => 0,
				'translated' => 0,
				'message'    => __( 'No target languages configured.', 'auto-multilingual-seo' ),
			);
		}
		
		$items = $this->get_content_items( $type, $batch );
		
		if ( empty( $items ) ) {
			return array(
				'complete'   => true,
				'processed'  => 0,
				'skipped'    => 0,
				'translated' => 0,
				'message'    => __( 'All items processed.', 'auto-multilingual-seo' ),
			);
		}
		
		$processed  = 0;
		$skipped    = 0;
		$translated = 0;
		
		foreach ( $items as $item ) {
			$processed++;
			
			// Extract content based on type.
			$content = $this->extract_content( $item, $type );
			
			if ( empty( $content ) ) {
				$skipped++;
				continue;
			}
			
			// Generate translations for each target language.
			foreach ( $target_languages as $target_lang ) {
				$translation_generated = $this->generate_translation_if_missing(
					$content,
					$default_language,
					$target_lang
				);
				
				if ( $translation_generated ) {
					$translated++;
				}
			}
		}
		
		return array(
			'complete'   => count( $items ) < $this->batch_size,
			'processed'  => $processed,
			'skipped'    => $skipped,
			'translated' => $translated,
			'message'    => sprintf(
				/* translators: 1: processed count, 2: translated count */
				__( 'Processed %1$d items, generated %2$d translations.', 'auto-multilingual-seo' ),
				$processed,
				$translated
			),
		);
	}
	
	/**
	 * Get content items for processing.
	 *
	 * @param string $type Content type.
	 * @param int    $batch Batch number.
	 * @return array Content items.
	 */
	private function get_content_items( $type, $batch ) {
		$offset = $batch * $this->batch_size;
		
		switch ( $type ) {
			case 'pages':
				return $this->get_pages( $offset );
				
			case 'posts':
				return $this->get_posts( $offset );
				
			case 'directorist':
				return $this->get_directorist_listings( $offset );
				
			case 'menus':
				return $this->get_menu_items( $offset );
			
			case 'widgets':
				return $this->get_widget_items( $offset );
			
			case 'ui_strings':
				return $this->get_ui_string_items( $offset );
			
			default:
				return array();
		}
	}
	
	/**
	 * Get pages for processing.
	 *
	 * @param int $offset Offset for pagination.
	 * @return array Pages.
	 */
	private function get_pages( $offset ) {
		return get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => $this->batch_size,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		) );
	}
	
	/**
	 * Get posts for processing.
	 *
	 * @param int $offset Offset for pagination.
	 * @return array Posts.
	 */
	private function get_posts( $offset ) {
		return get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $this->batch_size,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		) );
	}
	
	/**
	 * Get Directorist listings for processing.
	 *
	 * @param int $offset Offset for pagination.
	 * @return array Listings.
	 */
	private function get_directorist_listings( $offset ) {
		// Check if Directorist is active.
		if ( ! function_exists( 'directorist' ) ) {
			return array();
		}
		
		return get_posts( array(
			'post_type'      => 'at_biz_dir',
			'post_status'    => 'publish',
			'posts_per_page' => $this->batch_size,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		) );
	}
	
	/**
	 * Get menu items for processing.
	 *
	 * @param int $offset Offset for pagination.
	 * @return array Menu items.
	 */
	private function get_menu_items( $offset ) {
		// Get all registered navigation menus.
		$nav_menus = wp_get_nav_menus();
		
		if ( empty( $nav_menus ) ) {
			return array();
		}
		
		$all_menu_items = array();
		
		// Iterate through all menus and collect menu items.
		foreach ( $nav_menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );
			
			if ( ! empty( $menu_items ) && is_array( $menu_items ) ) {
				$all_menu_items = array_merge( $all_menu_items, $menu_items );
			}
		}
		
		// Apply offset and limit for batch processing.
		return array_slice( $all_menu_items, $offset, $this->batch_size );
	}
	
	/**
	 * Get widget items for processing.
	 *
	 * @param int $offset Offset for pagination.
	 * @return array Widget items.
	 */
	private function get_widget_items( $offset ) {
		// Get all active widgets from all sidebars.
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );
		
		if ( empty( $sidebars_widgets ) || ! is_array( $sidebars_widgets ) ) {
			return array();
		}
		
		$all_widget_items = array();
		
		// Iterate through all sidebars and collect widgets.
		foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {
			// Skip inactive widgets and special keys.
			if ( 'wp_inactive_widgets' === $sidebar_id || 'array_version' === $sidebar_id || empty( $widget_ids ) || ! is_array( $widget_ids ) ) {
				continue;
			}
			
			foreach ( $widget_ids as $widget_id ) {
				// Parse widget ID to get base and number.
				if ( preg_match( '/^(.+?)-(\d+)$/', $widget_id, $matches ) ) {
					$id_base = $matches[1];
					$widget_number = intval( $matches[2] );
					
					// Get widget options.
					$widget_options = get_option( 'widget_' . $id_base, array() );
					
					if ( isset( $widget_options[ $widget_number ] ) ) {
						$widget_data = $widget_options[ $widget_number ];
						
						// Create widget item object.
						$widget_item = (object) array(
							'id'       => $widget_id,
							'id_base'  => $id_base,
							'number'   => $widget_number,
							'sidebar'  => $sidebar_id,
							'data'     => $widget_data,
						);
						
						$all_widget_items[] = $widget_item;
					}
				}
			}
		}
		
		// Apply offset and limit for batch processing.
		return array_slice( $all_widget_items, $offset, $this->batch_size );
	}
	
	/**
	 * Get common theme UI strings for pre-translation.
	 *
	 * This method returns a fixed set of common UI strings that appear in themes.
	 * These strings can be extended by adding more items to the array.
	 *
	 * @return array Array of common UI strings.
	 */
	private function get_common_ui_strings() {
		// Fixed array of common theme UI strings.
		// This list can be easily extended by adding more strings here.
		return array(
			'Read more',
			'Search',
			'Submit',
			'Next',
			'Previous',
			'View details',
			'Continue reading',
			'Learn more',
			'Get started',
			'Contact us',
			'About us',
			'Home',
			'Blog',
			'Services',
			'Products',
			'Portfolio',
			'Team',
			'Testimonials',
			'FAQ',
			'Privacy Policy',
			'Terms of Service',
			'Newsletter',
			'Subscribe',
			'Follow us',
			'Share',
			'Comment',
			'Reply',
			'Edit',
			'Delete',
			'Cancel',
			'Save',
			'Login',
			'Register',
			'Logout',
			'Forgot password',
			'Back to top',
		);
	}
	
	/**
	 * Get UI string items for processing.
	 *
	 * @param int $offset Offset for pagination.
	 * @return array UI string items.
	 */
	private function get_ui_string_items( $offset ) {
		$common_strings = $this->get_common_ui_strings();
		
		// Convert strings to objects for consistent processing.
		$ui_string_items = array();
		$id = 1;
		
		foreach ( $common_strings as $string ) {
			$ui_string_items[] = (object) array(
				'id'   => $id++,
				'text' => $string,
			);
		}
		
		// Apply offset and limit for batch processing.
		return array_slice( $ui_string_items, $offset, $this->batch_size );
	}
	
	/**
	 * Extract content from item.
	 *
	 * @param WP_Post|object $item Content item.
	 * @param string         $type Content type.
	 * @return array Content data.
	 */
	private function extract_content( $item, $type ) {
		// Handle menu items differently.
		if ( 'menus' === $type ) {
			// Menu items are objects with title property.
			if ( isset( $item->title ) && ! empty( $item->title ) ) {
				return array(
					'id'    => isset( $item->ID ) ? $item->ID : 0,
					'title' => $item->title,
					'type'  => $type,
				);
			}
			return array();
		}
		
		// Handle widget items differently.
		if ( 'widgets' === $type ) {
			// Widgets are objects with data property containing widget-specific fields.
			$widget_content = array(
				'id'   => isset( $item->id ) ? $item->id : 0,
				'type' => $type,
			);
			
			// Extract text-based fields from widget data.
			if ( isset( $item->data ) && is_array( $item->data ) ) {
				// Common text fields in widgets.
				if ( ! empty( $item->data['title'] ) ) {
					$widget_content['title'] = $item->data['title'];
				}
				if ( ! empty( $item->data['text'] ) ) {
					$widget_content['content'] = $item->data['text'];
				}
				if ( ! empty( $item->data['content'] ) ) {
					$widget_content['content'] = $item->data['content'];
				}
			}
			
			// Return empty if no translatable content found.
			if ( empty( $widget_content['title'] ) && empty( $widget_content['content'] ) ) {
				return array();
			}
			
			return $widget_content;
		}
		
		// Handle UI strings differently.
		if ( 'ui_strings' === $type ) {
			// UI strings are simple objects with text property.
			if ( isset( $item->text ) && ! empty( $item->text ) ) {
				return array(
					'id'    => isset( $item->id ) ? $item->id : 0,
					'title' => $item->text,
					'type'  => $type,
				);
			}
			return array();
		}
		
		// Handle standard post types.
		if ( ! $item instanceof WP_Post ) {
			return array();
		}
		
		return array(
			'id'      => $item->ID,
			'title'   => $item->post_title,
			'content' => $item->post_content,
			'excerpt' => $item->post_excerpt,
			'type'    => $type,
		);
	}
	
	/**
	 * Generate translation if missing.
	 *
	 * @param array  $content Content data.
	 * @param string $source_lang Source language.
	 * @param string $target_lang Target language.
	 * @return bool Whether translation was generated.
	 */
	private function generate_translation_if_missing( $content, $source_lang, $target_lang ) {
		// Defensive guard: Prevent method calls on null objects.
		if ( null === $this->translator || null === $this->cache ) {
			return false;
		}
		
		$generated = false;
		
		// Process title.
		if ( ! empty( $content['title'] ) ) {
			$title_hash = md5( $content['title'] );
			$existing   = $this->cache->get_translation( $title_hash, $source_lang, $target_lang, true );
			
			if ( false === $existing ) {
				$translation = $this->translator->translate( $content['title'], $target_lang, $source_lang );
				
				if ( ! is_wp_error( $translation ) && ! empty( $translation ) ) {
					// Pre-generated translations persist for 30 days (2592000 seconds)
					$this->cache->save_automatic_translation(
						$title_hash,
						$source_lang,
						$target_lang,
						$translation,
						2592000
					);
					$generated = true;
				}
			}
		}
		
		// Process content.
		if ( ! empty( $content['content'] ) ) {
			$content_hash = md5( $content['content'] );
			$existing     = $this->cache->get_translation( $content_hash, $source_lang, $target_lang, true );
			
			if ( false === $existing ) {
				$translation = $this->translator->translate( $content['content'], $target_lang, $source_lang );
				
				if ( ! is_wp_error( $translation ) && ! empty( $translation ) ) {
					// Pre-generated translations persist for 30 days (2592000 seconds)
					$this->cache->save_automatic_translation(
						$content_hash,
						$source_lang,
						$target_lang,
						$translation,
						2592000
					);
					$generated = true;
				}
			}
		}
		
		// Process excerpt.
		if ( ! empty( $content['excerpt'] ) ) {
			$excerpt_hash = md5( $content['excerpt'] );
			$existing     = $this->cache->get_translation( $excerpt_hash, $source_lang, $target_lang, true );
			
			if ( false === $existing ) {
				$translation = $this->translator->translate( $content['excerpt'], $target_lang, $source_lang );
				
				if ( ! is_wp_error( $translation ) && ! empty( $translation ) ) {
					// Pre-generated translations persist for 30 days (2592000 seconds)
					$this->cache->save_automatic_translation(
						$excerpt_hash,
						$source_lang,
						$target_lang,
						$translation,
						2592000
					);
					$generated = true;
				}
			}
		}
		
		return $generated;
	}
	
	/**
	 * Get preparation status.
	 *
	 * @return array Status data.
	 */
	private function get_preparation_status() {
		$enabled_languages = get_option( 'amst_enabled_languages', array( 'en', 'de', 'fr' ) );
		
		// Count total items.
		$pages_count = wp_count_posts( 'page' )->publish;
		$posts_count = wp_count_posts( 'post' )->publish;
		
		$directorist_count = 0;
		if ( function_exists( 'directorist' ) ) {
			$directorist_count = wp_count_posts( 'at_biz_dir' )->publish;
		}
		
		// Count menu items.
		$menus_count = 0;
		$nav_menus   = wp_get_nav_menus();
		if ( ! empty( $nav_menus ) ) {
			foreach ( $nav_menus as $menu ) {
				$menu_items = wp_get_nav_menu_items( $menu->term_id );
				if ( ! empty( $menu_items ) && is_array( $menu_items ) ) {
					$menus_count += count( $menu_items );
				}
			}
		}
		
		// Count widgets with text content.
		$widgets_count = 0;
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );
		if ( ! empty( $sidebars_widgets ) && is_array( $sidebars_widgets ) ) {
			foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {
				// Skip inactive widgets and special keys.
				if ( 'wp_inactive_widgets' === $sidebar_id || 'array_version' === $sidebar_id || empty( $widget_ids ) || ! is_array( $widget_ids ) ) {
					continue;
				}
				
				foreach ( $widget_ids as $widget_id ) {
					// Parse widget ID to get base and number.
					if ( preg_match( '/^(.+?)-(\d+)$/', $widget_id, $matches ) ) {
						$id_base = $matches[1];
						$widget_number = intval( $matches[2] );
						
						// Get widget options.
						$widget_options = get_option( 'widget_' . $id_base, array() );
						
						if ( isset( $widget_options[ $widget_number ] ) ) {
							$widget_data = $widget_options[ $widget_number ];
							
							// Check if widget has text content.
							if ( ! empty( $widget_data['title'] ) || ! empty( $widget_data['text'] ) || ! empty( $widget_data['content'] ) ) {
								$widgets_count++;
							}
						}
					}
				}
			}
		}
		
		// Count UI strings.
		$ui_strings_count = count( $this->get_common_ui_strings() );
		
		return array(
			'pages_count'       => $pages_count,
			'posts_count'       => $posts_count,
			'directorist_count' => $directorist_count,
			'menus_count'       => $menus_count,
			'widgets_count'     => $widgets_count,
			'ui_strings_count'  => $ui_strings_count,
			'total_count'       => $pages_count + $posts_count + $directorist_count + $menus_count + $widgets_count + $ui_strings_count,
			'enabled_languages' => $enabled_languages,
			'batch_size'        => $this->batch_size,
		);
	}
}
