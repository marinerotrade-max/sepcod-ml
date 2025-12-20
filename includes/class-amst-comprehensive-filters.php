<?php
/**
 * Comprehensive content filters for consistent language application.
 * Ensures selected language is applied to ALL content: titles, menus, widgets, UI strings.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AMST_Comprehensive_Filters class.
 */
class AMST_Comprehensive_Filters {
	
	/**
	 * Language detector.
	 *
	 * @var AMST_Language_Detector
	 */
	private $language_detector;
	
	/**
	 * Content processor.
	 *
	 * @var AMST_Content_Processor
	 */
	private $content_processor;
	
	/**
	 * Current language.
	 *
	 * @var string
	 */
	private $current_language;
	
	/**
	 * Default language.
	 *
	 * @var string
	 */
	private $default_language;
	
	/**
	 * Constructor.
	 * v1.6.0: Updated to use Language_Context.
	 *
	 * @param AMST_Language_Detector $language_detector Language detector (facade).
	 * @param AMST_Content_Processor $content_processor Content processor.
	 */
	public function __construct( $language_detector, $content_processor ) {
		$this->language_detector = $language_detector;
		$this->content_processor = $content_processor;
		
		// v1.6.0: Get language from centralized context
		$language_context = AMST_Language_Context::instance();
		$this->current_language = $language_context->get_current_language();
		$this->default_language = $language_context->get_default_language();
		
		// Only apply filters if not default language
		if ( $this->current_language !== $this->default_language ) {
			$this->init_filters();
		}
	}
	
	/**
	 * Initialize all content filters.
	 */
	private function init_filters() {
		// Content filters
		add_filter( 'the_content', array( $this, 'translate_content' ), 10 );
		add_filter( 'the_excerpt', array( $this, 'translate_content' ), 10 );
		
		// Title filters
		add_filter( 'the_title', array( $this, 'translate_title' ), 10, 2 );
		add_filter( 'single_post_title', array( $this, 'translate_title' ), 10 );
		add_filter( 'document_title_parts', array( $this, 'translate_document_title' ), 10 );
		
		// Menu filters
		add_filter( 'wp_nav_menu_items', array( $this, 'translate_menu' ), 10, 2 );
		add_filter( 'wp_nav_menu', array( $this, 'translate_menu_html' ), 10 );
		add_filter( 'nav_menu_item_title', array( $this, 'translate_text' ), 10 );
		
		// Widget filters
		add_filter( 'widget_title', array( $this, 'translate_text' ), 10 );
		add_filter( 'widget_text', array( $this, 'translate_content' ), 10 );
		add_filter( 'widget_block_content', array( $this, 'translate_content' ), 10 );
		
		// Site info filters
		add_filter( 'bloginfo', array( $this, 'translate_bloginfo' ), 10, 2 );
		add_filter( 'option_blogname', array( $this, 'translate_text' ), 10 );
		add_filter( 'option_blogdescription', array( $this, 'translate_text' ), 10 );
		
		// Metadata filters
		add_filter( 'get_post_metadata', array( $this, 'translate_post_meta' ), 10, 4 );
		
		// Button and form text
		add_filter( 'submit_button', array( $this, 'translate_content' ), 10 );
		add_filter( 'the_search_query', array( $this, 'translate_text' ), 10 );
		
		// Archive titles
		add_filter( 'get_the_archive_title', array( $this, 'translate_text' ), 10 );
		add_filter( 'get_the_archive_description', array( $this, 'translate_content' ), 10 );
		
		// Comments
		add_filter( 'comment_text', array( $this, 'translate_content' ), 10 );
		add_filter( 'get_comment_excerpt', array( $this, 'translate_text' ), 10 );
		
		// Custom fields (ACF, etc.)
		add_filter( 'acf/load_value', array( $this, 'translate_acf_value' ), 10, 3 );
	}
	
	/**
	 * Translate content.
	 *
	 * @param string $content Content.
	 * @return string Translated content.
	 */
	public function translate_content( $content ) {
		if ( empty( $content ) || is_admin() ) {
			return $content;
		}
		
		return $this->content_processor->translate_content( $content, $this->current_language, $this->default_language );
	}
	
	/**
	 * Translate text (simple string).
	 *
	 * @param string $text Text.
	 * @return string Translated text.
	 */
	public function translate_text( $text ) {
		if ( empty( $text ) || is_admin() ) {
			return $text;
		}
		
		// Use content processor for simple text
		return $this->content_processor->translate_content( $text, $this->current_language, $this->default_language );
	}
	
	/**
	 * Translate title.
	 *
	 * @param string $title Title.
	 * @param int    $post_id Post ID.
	 * @return string Translated title.
	 */
	public function translate_title( $title, $post_id = null ) {
		if ( empty( $title ) || is_admin() ) {
			return $title;
		}
		
		return $this->translate_text( $title );
	}
	
	/**
	 * Translate document title parts.
	 *
	 * @param array $title_parts Title parts.
	 * @return array Translated title parts.
	 */
	public function translate_document_title( $title_parts ) {
		if ( is_admin() ) {
			return $title_parts;
		}
		
		foreach ( $title_parts as $key => $part ) {
			if ( ! empty( $part ) ) {
				$title_parts[ $key ] = $this->translate_text( $part );
			}
		}
		
		return $title_parts;
	}
	
	/**
	 * Translate menu items.
	 *
	 * @param string $items Menu items HTML.
	 * @param object $args Menu args.
	 * @return string Translated menu items.
	 */
	public function translate_menu( $items, $args = null ) {
		if ( is_admin() ) {
			return $items;
		}
		
		return $this->translate_content( $items );
	}
	
	/**
	 * Translate menu HTML.
	 *
	 * @param string $menu_html Menu HTML.
	 * @return string Translated menu HTML.
	 */
	public function translate_menu_html( $menu_html ) {
		if ( is_admin() ) {
			return $menu_html;
		}
		
		return $this->translate_content( $menu_html );
	}
	
	/**
	 * Translate bloginfo.
	 *
	 * @param string $output The requested non-URL site information.
	 * @param string $show   Type of information requested.
	 * @return string Translated bloginfo.
	 */
	public function translate_bloginfo( $output, $show ) {
		if ( is_admin() ) {
			return $output;
		}
		
		// Only translate name and description
		if ( in_array( $show, array( 'name', 'description' ), true ) ) {
			return $this->translate_text( $output );
		}
		
		return $output;
	}
	
	/**
	 * Translate post metadata.
	 *
	 * @param mixed  $value     Metadata value.
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key.
	 * @param bool   $single    Whether to return a single value.
	 * @return mixed Translated metadata.
	 */
	public function translate_post_meta( $value, $object_id, $meta_key, $single ) {
		// Skip if admin or value is null (not set yet)
		if ( is_admin() || is_null( $value ) ) {
			return $value;
		}
		
		// Only translate specific meta fields (text-based)
		$translatable_meta = array( '_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_excerpt' );
		
		if ( in_array( $meta_key, $translatable_meta, true ) && is_string( $value ) ) {
			return $this->translate_text( $value );
		}
		
		return $value;
	}
	
	/**
	 * Translate ACF field value.
	 *
	 * @param mixed $value   Field value.
	 * @param int   $post_id Post ID.
	 * @param array $field   Field array.
	 * @return mixed Translated value.
	 */
	public function translate_acf_value( $value, $post_id, $field ) {
		if ( is_admin() || empty( $value ) ) {
			return $value;
		}
		
		// Only translate text-based fields
		$translatable_types = array( 'text', 'textarea', 'wysiwyg' );
		
		if ( isset( $field['type'] ) && in_array( $field['type'], $translatable_types, true ) ) {
			if ( is_string( $value ) ) {
				return $field['type'] === 'wysiwyg' ? $this->translate_content( $value ) : $this->translate_text( $value );
			}
		}
		
		return $value;
	}
}
