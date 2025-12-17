<?php
/**
 * Manual translation management.
 * Provides interface and API for managing manual translations.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AMST_Manual_Translations class.
 */
class AMST_Manual_Translations {
	
	/**
	 * Cache handler.
	 *
	 * @var AMST_Cache
	 */
	private $cache;
	
	/**
	 * Database handler.
	 *
	 * @var AMST_Database
	 */
	private $database;
	
	/**
	 * Constructor.
	 *
	 * @param AMST_Cache    $cache    Cache handler.
	 * @param AMST_Database $database Database handler.
	 */
	public function __construct( $cache, $database ) {
		$this->cache    = $cache;
		$this->database = $database;
		
		// Register REST API endpoints
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}
	
	/**
	 * Add manual translation.
	 *
	 * @param string $original_text    Original text.
	 * @param string $translated_text  Translated text.
	 * @param string $target_lang      Target language.
	 * @param string $source_lang      Source language.
	 * @param string $content_type     Content type.
	 * @return bool|WP_Error Success status or error.
	 */
	public function add_manual_translation( $original_text, $translated_text, $target_lang, $source_lang = 'en', $content_type = 'general' ) {
		// Validate inputs
		if ( empty( $original_text ) || empty( $translated_text ) ) {
			return new WP_Error( 'invalid_input', __( 'Original and translated text are required.', 'auto-multilingual-seo' ) );
		}
		
		if ( $source_lang === $target_lang ) {
			return new WP_Error( 'same_language', __( 'Source and target languages cannot be the same.', 'auto-multilingual-seo' ) );
		}
		
		// Generate content hash
		$content_hash = hash( 'sha256', $original_text );
		
		// Save manual translation
		$saved = $this->cache->save_manual_translation(
			$content_hash,
			$source_lang,
			$target_lang,
			$original_text,
			$translated_text,
			$content_type
		);
		
		if ( ! $saved ) {
			return new WP_Error( 'save_failed', __( 'Failed to save manual translation.', 'auto-multilingual-seo' ) );
		}
		
		return true;
	}
	
	/**
	 * Update manual translation.
	 *
	 * @param int    $id               Translation ID.
	 * @param string $translated_text  New translated text.
	 * @return bool|WP_Error Success status or error.
	 */
	public function update_manual_translation( $id, $translated_text ) {
		if ( empty( $translated_text ) ) {
			return new WP_Error( 'invalid_input', __( 'Translated text is required.', 'auto-multilingual-seo' ) );
		}
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'amst_translations';
		
		$result = $wpdb->update(
			$table_name,
			array( 'translated_text' => $translated_text ),
			array( 'id' => $id, 'is_manual' => 1 ),
			array( '%s' ),
			array( '%d', '%d' )
		);
		
		if ( false === $result ) {
			return new WP_Error( 'update_failed', __( 'Failed to update manual translation.', 'auto-multilingual-seo' ) );
		}
		
		// Clear cache
		wp_cache_flush();
		
		return true;
	}
	
	/**
	 * Delete manual translation.
	 *
	 * @param int $id Translation ID.
	 * @return bool|WP_Error Success status or error.
	 */
	public function delete_manual_translation( $id ) {
		$deleted = $this->database->delete_manual_translation( $id );
		
		if ( ! $deleted ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete manual translation.', 'auto-multilingual-seo' ) );
		}
		
		// Clear cache
		wp_cache_flush();
		
		return true;
	}
	
	/**
	 * Get manual translations.
	 *
	 * @param array $args Query arguments.
	 * @return array Manual translations.
	 */
	public function get_manual_translations( $args = array() ) {
		return $this->database->get_manual_translations( $args );
	}
	
	/**
	 * Export manual translations.
	 *
	 * @param string $target_lang Target language.
	 * @return array|WP_Error Export data or error.
	 */
	public function export_manual_translations( $target_lang = '' ) {
		$args = array();
		if ( ! empty( $target_lang ) ) {
			$args['target_language'] = $target_lang;
		}
		
		$translations = $this->get_manual_translations( $args );
		
		$export_data = array();
		foreach ( $translations as $translation ) {
			$export_data[] = array(
				'original_text'    => $translation->original_text,
				'translated_text'  => $translation->translated_text,
				'source_language'  => $translation->source_language,
				'target_language'  => $translation->target_language,
				'content_type'     => $translation->content_type,
			);
		}
		
		return $export_data;
	}
	
	/**
	 * Import manual translations.
	 *
	 * @param array $translations Array of translations.
	 * @return array Result with counts.
	 */
	public function import_manual_translations( $translations ) {
		$imported = 0;
		$failed   = 0;
		
		foreach ( $translations as $translation ) {
			if ( empty( $translation['original_text'] ) || empty( $translation['translated_text'] ) ) {
				$failed++;
				continue;
			}
			
			$result = $this->add_manual_translation(
				$translation['original_text'],
				$translation['translated_text'],
				$translation['target_language'] ?? 'en',
				$translation['source_language'] ?? 'en',
				$translation['content_type'] ?? 'general'
			);
			
			if ( is_wp_error( $result ) ) {
				$failed++;
			} else {
				$imported++;
			}
		}
		
		return array(
			'imported' => $imported,
			'failed'   => $failed,
			'total'    => count( $translations ),
		);
	}
	
	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes() {
		register_rest_route(
			'amst/v1',
			'/manual-translations',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_get_translations' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
		
		register_rest_route(
			'amst/v1',
			'/manual-translations',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_add_translation' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
		
		register_rest_route(
			'amst/v1',
			'/manual-translations/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'rest_update_translation' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
		
		register_rest_route(
			'amst/v1',
			'/manual-translations/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'rest_delete_translation' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}
	
	/**
	 * REST: Get translations.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function rest_get_translations( $request ) {
		$args = array();
		if ( $request->get_param( 'target_language' ) ) {
			$args['target_language'] = $request->get_param( 'target_language' );
		}
		
		$translations = $this->get_manual_translations( $args );
		return rest_ensure_response( $translations );
	}
	
	/**
	 * REST: Add translation.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function rest_add_translation( $request ) {
		$result = $this->add_manual_translation(
			$request->get_param( 'original_text' ),
			$request->get_param( 'translated_text' ),
			$request->get_param( 'target_lang' ),
			$request->get_param( 'source_lang' ) ?? 'en',
			$request->get_param( 'content_type' ) ?? 'general'
		);
		
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		
		return rest_ensure_response( array( 'success' => true ) );
	}
	
	/**
	 * REST: Update translation.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function rest_update_translation( $request ) {
		$result = $this->update_manual_translation(
			$request->get_param( 'id' ),
			$request->get_param( 'translated_text' )
		);
		
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		
		return rest_ensure_response( array( 'success' => true ) );
	}
	
	/**
	 * REST: Delete translation.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function rest_delete_translation( $request ) {
		$result = $this->delete_manual_translation( $request->get_param( 'id' ) );
		
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		
		return rest_ensure_response( array( 'success' => true ) );
	}
	
	/**
	 * Check REST API permission.
	 *
	 * @return bool Permission status.
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}
}
