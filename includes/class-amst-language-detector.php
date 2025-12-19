<?php
/**
 * Language detector facade - delegates to AMST_Language_Context.
 * 
 * v1.6.0: This class is now a simple facade that delegates all operations
 * to the centralized AMST_Language_Context class. It exists for backwards
 * compatibility with existing code that uses $language_detector.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AMST_Language_Detector class.
 * 
 * v1.6.0: Refactored to be a facade pattern - all logic moved to Language_Context.
 */
class AMST_Language_Detector {
	
	/**
	 * Language context (centralized language resolution).
	 *
	 * @var AMST_Language_Context
	 */
	private $language_context;
	
	/**
	 * Constructor.
	 * 
	 * @param AMST_Language_Context $language_context Language context instance.
	 */
	public function __construct( $language_context = null ) {
		// v1.6.0: Accept language context or get singleton
		$this->language_context = $language_context ? $language_context : AMST_Language_Context::instance();
		
		// Add query vars for backwards compatibility
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}
	
	/**
	 * Get current language.
	 * 
	 * v1.6.0: Delegates to Language_Context.
	 *
	 * @return string Current language code.
	 */
	public function get_current_language() {
		return $this->language_context->get_current_language();
	}
	
	/**
	 * Get default language.
	 * 
	 * v1.6.0: Delegates to Language_Context.
	 *
	 * @return string Default language code.
	 */
	public function get_default_language() {
		return $this->language_context->get_default_language();
	}
	
	/**
	 * Get enabled languages.
	 * 
	 * v1.6.0: Delegates to Language_Context.
	 *
	 * @return array Enabled language codes.
	 */
	public function get_enabled_languages() {
		return $this->language_context->get_enabled_languages();
	}
	
	/**
	 * Is current language default.
	 * 
	 * v1.6.0: Delegates to Language_Context.
	 *
	 * @return bool True if current language is default.
	 */
	public function is_default_language() {
		return $this->language_context->is_default_language();
	}
	
	/**
	 * Check if a language code is enabled.
	 * 
	 * v1.6.0: Delegates to Language_Context.
	 *
	 * @param string $lang_code Language code to check.
	 * @return bool True if language is enabled.
	 */
	public function is_enabled_language( $lang_code ) {
		return $this->language_context->is_enabled_language( $lang_code );
	}
	
	/**
	 * Remove language prefix from URL.
	 * 
	 * v1.6.0: Delegates to Language_Context.
	 *
	 * @param string $url URL.
	 * @return string URL without language prefix.
	 */
	public function remove_language_prefix( $url ) {
		return $this->language_context->remove_language_prefix( $url );
	}
	
	/**
	 * Get URL for language.
	 * 
	 * v1.6.0: Delegates to Language_Context.
	 *
	 * @param string $url URL.
	 * @param string $lang Language code.
	 * @return string Translated URL.
	 */
	public function get_language_url( $url, $lang ) {
		return $this->language_context->get_language_url( $url, $lang );
	}
	
	/**
	 * Get current URL.
	 *
	 * @return string Current URL.
	 */
	public function get_current_url() {
		$protocol = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return $protocol . '://' . $host . $request_uri;
	}
	
	/**
	 * Add query vars for language detection.
	 *
	 * @param array $vars Query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'lang';
		return $vars;
	}
	
	/**
	 * Get language switcher HTML.
	 * 
	 * v1.6.0: Maintained for backwards compatibility.
	 *
	 * @return string Language switcher HTML.
	 */
	public function get_language_switcher() {
		$current_lang = $this->get_current_language();
		$enabled_languages = $this->get_enabled_languages();
		
		// Get proper current URL - handle homepage specially
		if ( is_front_page() || is_home() ) {
			$current_url = home_url( '/' );
			// Add current language prefix if not default
			$default_lang = $this->get_default_language();
			if ( $current_lang !== $default_lang ) {
				$current_url = home_url( '/' . $current_lang . '/' );
			}
		} else {
			$current_url = $this->get_current_url();
		}
		
		$translator = amst()->translator;
		$supported_languages = $translator->get_supported_languages();
		
		$html = '<div class="amst-language-switcher">';
		$html .= '<ul class="amst-language-list">';
		
		foreach ( $enabled_languages as $lang ) {
			$lang_name = isset( $supported_languages[ $lang ] ) ? $supported_languages[ $lang ] : strtoupper( $lang );
			$lang_url = $this->get_language_url( $current_url, $lang );
			$active_class = $lang === $current_lang ? ' active' : '';
			
			$html .= sprintf(
				'<li class="amst-language-item%s"><a href="%s" hreflang="%s">%s</a></li>',
				esc_attr( $active_class ),
				esc_url( $lang_url ),
				esc_attr( $lang ),
				esc_html( $lang_name )
			);
		}
		
		$html .= '</ul>';
		$html .= '</div>';
		
		return $html;
	}
}
