<?php
/**
 * Centralized Language Context - Single Source of Truth for Language Resolution.
 * 
 * This class resolves the current language exactly once per request based ONLY on URL prefix.
 * All other components must read from this class and NEVER re-detect language themselves.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AMST_Language_Context class.
 * 
 * Singleton that resolves and stores the current language for the entire request.
 */
class AMST_Language_Context {
	
	/**
	 * Single instance.
	 *
	 * @var AMST_Language_Context
	 */
	private static $instance = null;
	
	/**
	 * Current language resolved from URL.
	 *
	 * @var string
	 */
	private $current_language = null;
	
	/**
	 * Default language (always 'en').
	 *
	 * @var string
	 */
	private $default_language = 'en';
	
	/**
	 * Enabled languages from settings.
	 *
	 * @var array
	 */
	private $enabled_languages = null;
	
	/**
	 * Whether language has been resolved.
	 *
	 * @var bool
	 */
	private $resolved = false;
	
	/**
	 * Get singleton instance.
	 *
	 * @return AMST_Language_Context
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Private constructor (singleton pattern).
	 */
	private function __construct() {
		// Initialize on plugins_loaded hook (early enough, before any content rendering)
		add_action( 'plugins_loaded', array( $this, 'resolve_language' ), 1 );
		
		// Add WordPress locale filter
		add_filter( 'locale', array( $this, 'get_wordpress_locale' ), 10, 1 );
	}
	
	/**
	 * Resolve current language from URL - executed ONCE per request.
	 * 
	 * This is the ONLY place where language detection happens.
	 * Language is determined ONLY by URL prefix (e.g., /de/, /fr/, /it/).
	 */
	public function resolve_language() {
		// Prevent multiple resolutions
		if ( $this->resolved ) {
			return;
		}
		
		// Get enabled languages from settings
		$this->enabled_languages = get_option( 'amst_enabled_languages', array( 'en' ) );
		if ( ! is_array( $this->enabled_languages ) ) {
			$this->enabled_languages = array( 'en' );
		}
		
		// Get REQUEST_URI for URL-based detection
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$path = wp_parse_url( $request_uri, PHP_URL_PATH );
		
		// Remove leading slash and get first segment
		$path = ltrim( $path, '/' );
		$path_parts = explode( '/', $path );
		$first_segment = isset( $path_parts[0] ) ? $path_parts[0] : '';
		
		// Check if first segment is an enabled language code
		if ( ! empty( $first_segment ) && in_array( $first_segment, $this->enabled_languages, true ) ) {
			$this->current_language = $first_segment;
		} else {
			// No language prefix = default language
			$this->current_language = $this->default_language;
		}
		
		// Mark as resolved
		$this->resolved = true;
		
		// Set global for backwards compatibility (will be removed in next phase)
		$GLOBALS['amst_current_language'] = $this->current_language;
	}
	
	/**
	 * Get current language.
	 * 
	 * All components MUST use this method to get the current language.
	 * NEVER detect language elsewhere.
	 *
	 * @return string Current language code.
	 */
	public function get_current_language() {
		// Ensure language is resolved
		if ( ! $this->resolved ) {
			$this->resolve_language();
		}
		
		return $this->current_language;
	}
	
	/**
	 * Get default language.
	 * 
	 * Default language is ALWAYS 'en' (English).
	 *
	 * @return string Default language code.
	 */
	public function get_default_language() {
		return $this->default_language;
	}
	
	/**
	 * Get enabled languages.
	 *
	 * @return array Enabled language codes.
	 */
	public function get_enabled_languages() {
		// Ensure language is resolved (which loads enabled languages)
		if ( ! $this->resolved ) {
			$this->resolve_language();
		}
		
		return $this->enabled_languages;
	}
	
	/**
	 * Check if current language is default.
	 *
	 * @return bool True if current language is default.
	 */
	public function is_default_language() {
		return $this->get_current_language() === $this->default_language;
	}
	
	/**
	 * Check if a language is enabled.
	 *
	 * @param string $lang_code Language code.
	 * @return bool True if enabled.
	 */
	public function is_enabled_language( $lang_code ) {
		$enabled = $this->get_enabled_languages();
		return in_array( $lang_code, $enabled, true );
	}
	
	/**
	 * Get WordPress locale for current language.
	 * 
	 * Converts language code to WordPress locale format (e.g., 'de' => 'de_DE').
	 *
	 * @param string $locale Current locale (from WordPress).
	 * @return string Modified locale based on current language.
	 */
	public function get_wordpress_locale( $locale ) {
		$current_lang = $this->get_current_language();
		
		// Map language codes to WordPress locales
		$locale_map = array(
			'bg' => 'bg_BG', // Bulgarian
			'hr' => 'hr',    // Croatian
			'cs' => 'cs_CZ', // Czech
			'da' => 'da_DK', // Danish
			'nl' => 'nl_NL', // Dutch
			'en' => 'en_US', // English
			'et' => 'et',    // Estonian
			'fi' => 'fi',    // Finnish
			'fr' => 'fr_FR', // French
			'de' => 'de_DE', // German
			'el' => 'el',    // Greek
			'hu' => 'hu_HU', // Hungarian
			'ga' => 'ga_IE', // Irish
			'it' => 'it_IT', // Italian
			'lv' => 'lv',    // Latvian
			'lt' => 'lt_LT', // Lithuanian
			'mt' => 'mt_MT', // Maltese
			'pl' => 'pl_PL', // Polish
			'pt' => 'pt_PT', // Portuguese
			'ro' => 'ro_RO', // Romanian
			'sk' => 'sk_SK', // Slovak
			'sl' => 'sl_SI', // Slovenian
			'es' => 'es_ES', // Spanish
			'sv' => 'sv_SE', // Swedish
		);
		
		return isset( $locale_map[ $current_lang ] ) ? $locale_map[ $current_lang ] : $locale;
	}
	
	/**
	 * Get language-specific URL.
	 * 
	 * Converts a URL to include the language prefix for non-default languages.
	 * This is for URL GENERATION only, NOT detection.
	 *
	 * @param string $url Base URL.
	 * @param string $lang Language code.
	 * @return string Language-specific URL.
	 */
	public function get_language_url( $url, $lang ) {
		// Parse URL
		$parsed = wp_parse_url( $url );
		$path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
		
		// Remove any existing language prefixes
		$path = $this->remove_language_prefix_from_path( $path );
		
		// Add language prefix for non-default languages
		if ( $lang !== $this->default_language ) {
			$path = '/' . $lang . $path;
		}
		
		// Ensure path starts with slash
		if ( empty( $path ) || '/' !== $path[0] ) {
			$path = '/' . $path;
		}
		
		// Clean up double slashes
		$path = preg_replace( '#/+#', '/', $path );
		
		// Rebuild URL
		$new_url = '';
		if ( isset( $parsed['scheme'] ) ) {
			$new_url .= $parsed['scheme'] . '://';
		}
		if ( isset( $parsed['host'] ) ) {
			$new_url .= $parsed['host'];
		}
		if ( isset( $parsed['port'] ) ) {
			$new_url .= ':' . $parsed['port'];
		}
		$new_url .= $path;
		if ( isset( $parsed['query'] ) ) {
			$new_url .= '?' . $parsed['query'];
		}
		if ( isset( $parsed['fragment'] ) ) {
			$new_url .= '#' . $parsed['fragment'];
		}
		
		return $new_url;
	}
	
	/**
	 * Remove language prefix from path.
	 * 
	 * Helper method to strip ALL language prefixes from a path.
	 *
	 * @param string $path URL path.
	 * @return string Path without language prefix.
	 */
	private function remove_language_prefix_from_path( $path ) {
		// Remove leading slash for processing
		$path = ltrim( $path, '/' );
		
		// Split into segments
		$segments = empty( $path ) ? array() : explode( '/', $path );
		
		// Remove ALL language prefixes (prevents stacking)
		$enabled = $this->get_enabled_languages();
		while ( ! empty( $segments[0] ) && in_array( $segments[0], $enabled, true ) ) {
			array_shift( $segments );
			$segments = array_values( $segments ); // Reindex
		}
		
		// Rebuild path
		$clean_path = '/' . implode( '/', $segments );
		
		// Ensure proper slash handling
		if ( '/' !== $clean_path && '/' === substr( $clean_path, -1 ) ) {
			// Keep trailing slash if present
			return $clean_path;
		} elseif ( '/' === $clean_path ) {
			return '/';
		} else {
			// Ensure ends with slash for consistency
			return $clean_path . '/';
		}
	}
	
	/**
	 * Remove language prefix from URL.
	 * 
	 * Public method for removing language prefix from full URL.
	 *
	 * @param string $url URL.
	 * @return string URL without language prefix.
	 */
	public function remove_language_prefix( $url ) {
		$parsed = wp_parse_url( $url );
		$path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
		
		// Remove language prefix from path
		$clean_path = $this->remove_language_prefix_from_path( $path );
		
		// Rebuild URL
		$new_url = '';
		if ( isset( $parsed['scheme'] ) ) {
			$new_url .= $parsed['scheme'] . '://';
		}
		if ( isset( $parsed['host'] ) ) {
			$new_url .= $parsed['host'];
		}
		if ( isset( $parsed['port'] ) ) {
			$new_url .= ':' . $parsed['port'];
		}
		$new_url .= $clean_path;
		if ( isset( $parsed['query'] ) ) {
			$new_url .= '?' . $parsed['query'];
		}
		if ( isset( $parsed['fragment'] ) ) {
			$new_url .= '#' . $parsed['fragment'];
		}
		
		return $new_url;
	}
}
