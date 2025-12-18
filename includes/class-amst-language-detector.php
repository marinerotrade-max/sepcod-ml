<?php
/**
 * Language detector from URL prefix.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Language_Detector class.
 */
class AMST_Language_Detector {
    
    /**
     * Current language.
     *
     * @var string
     */
    private $current_language;
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'detect_language' ), 1 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
    }
    
    /**
     * Detect language from URL and cookie with fresh reading on every page load.
     */
    public function detect_language() {
        $default_lang = get_option( 'amst_default_language', 'en' );
        $enabled_languages = get_option( 'amst_enabled_languages', array( 'en' ) );
        
        // PRIORITY 1: Check URL parameter ?lang=XX (highest priority - overrides cookie)
        $url_param_lang = isset( $_GET['lang'] ) ? sanitize_text_field( wp_unslash( $_GET['lang'] ) ) : '';
        if ( ! empty( $url_param_lang ) && in_array( $url_param_lang, $enabled_languages, true ) ) {
            $this->current_language = $url_param_lang;
            // Overwrite cookie with URL parameter
            $this->set_language_cookie( $url_param_lang );
            $GLOBALS['amst_current_language'] = $this->current_language;
            return;
        }
        
        // PRIORITY 2: Check URL path prefix (e.g., /de/, /fr/)
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $path = wp_parse_url( $request_uri, PHP_URL_PATH );
        
        // Remove leading slash.
        $path = ltrim( $path, '/' );
        
        // Check if path starts with language code.
        $path_parts = explode( '/', $path );
        $potential_lang = isset( $path_parts[0] ) ? $path_parts[0] : '';
        
        if ( ! empty( $potential_lang ) && in_array( $potential_lang, $enabled_languages, true ) ) {
            // Language prefix found in URL path
            $this->current_language = $potential_lang;
            // Update cookie to match URL (always sync cookie with URL)
            $this->set_language_cookie( $potential_lang );
        } else {
            // PRIORITY 3: No URL prefix - read fresh cookie on EVERY page load
            // Force fresh cookie read - don't cache it
            $cookie_lang = $this->get_language_cookie();
            
            if ( $cookie_lang && in_array( $cookie_lang, $enabled_languages, true ) ) {
                // User has a valid language preference cookie
                $this->current_language = $cookie_lang;
            } else {
                // No valid cookie - use default language
                $this->current_language = $default_lang;
                // Clear any invalid cookie
                $this->clear_language_cookie();
            }
        }
        
        // Set as global for easy access.
        $GLOBALS['amst_current_language'] = $this->current_language;
    }
    
    /**
     * Set language preference cookie - ALWAYS overwrites existing cookie.
     *
     * @param string $lang Language code.
     */
    private function set_language_cookie( $lang ) {
        if ( ! headers_sent() ) {
            // Force overwrite with path=/ to ensure cookie is updated site-wide
            setcookie( 'amst_language', $lang, time() + ( 30 * DAY_IN_SECONDS ), '/', COOKIE_DOMAIN, is_ssl(), false );
        }
    }
    
    /**
     * Get language preference from cookie - FRESH READ on every call.
     *
     * @return string|null Language code or null if not set.
     */
    private function get_language_cookie() {
        // Force fresh cookie read - check $_COOKIE superglobal directly
        // This ensures we always get the latest value, not a cached one
        return isset( $_COOKIE['amst_language'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['amst_language'] ) ) : null;
    }
    
    /**
     * Clear language preference cookie.
     */
    private function clear_language_cookie() {
        if ( ! headers_sent() ) {
            setcookie( 'amst_language', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
        }
    }
    
    /**
     * Get current language.
     *
     * @return string Current language code.
     */
    public function get_current_language() {
        if ( ! isset( $this->current_language ) ) {
            $this->current_language = get_option( 'amst_default_language', 'en' );
        }
        return $this->current_language;
    }
    
    /**
     * Get default language.
     *
     * @return string Default language code (always 'en' - English).
     */
    public function get_default_language() {
        // ENFORCED: Default language is always English, ignore database setting
        return 'en';
    }
    
    /**
     * Get enabled languages.
     *
     * @return array Enabled language codes.
     */
    public function get_enabled_languages() {
        $languages = get_option( 'amst_enabled_languages', array( 'en' ) );
        return is_array( $languages ) ? $languages : array( 'en' );
    }
    
    /**
     * Is current language default.
     *
     * @return bool True if current language is default.
     */
    public function is_default_language() {
        return $this->get_current_language() === $this->get_default_language();
    }
    
    /**
     * Check if a language code is enabled.
     *
     * @param string $lang_code Language code to check.
     * @return bool True if language is enabled.
     */
    public function is_enabled_language( $lang_code ) {
        $enabled_languages = $this->get_enabled_languages();
        return in_array( $lang_code, $enabled_languages, true );
    }
    
    /**
     * Remove language prefix from URL.
     *
     * @param string $url URL.
     * @return string URL without language prefix.
     */
    public function remove_language_prefix( $url ) {
        // Parse URL.
        $parsed = wp_parse_url( $url );
        $path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
        
        // Remove language prefix if exists.
        $enabled_languages = $this->get_enabled_languages();
        foreach ( $enabled_languages as $enabled_lang ) {
            // Handle both "/lang/" and "/lang" at the end
            if ( 0 === strpos( $path, '/' . $enabled_lang . '/' ) ) {
                $path = substr( $path, strlen( '/' . $enabled_lang ) );
                break;
            } elseif ( '/' . $enabled_lang === $path ) {
                $path = '/';
                break;
            }
        }
        
        // Ensure path starts with slash
        if ( empty( $path ) || '/' !== $path[0] ) {
            $path = '/' . $path;
        }
        
        // Clean up double slashes
        $path = preg_replace( '#/+#', '/', $path );
        
        // Rebuild URL.
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
     * Get URL for language.
     *
     * @param string $url URL.
     * @param string $lang Language code.
     * @return string Translated URL.
     */
    public function get_language_url( $url, $lang ) {
        $default_lang = $this->get_default_language();
        
        // Parse URL.
        $parsed = wp_parse_url( $url );
        $path = isset( $parsed['path'] ) ? $parsed['path'] : '/';
        
        // Remove current language prefix if exists.
        $enabled_languages = $this->get_enabled_languages();
        foreach ( $enabled_languages as $enabled_lang ) {
            // Handle both "/lang/" and "/lang" at the end
            if ( 0 === strpos( $path, '/' . $enabled_lang . '/' ) ) {
                $path = substr( $path, strlen( '/' . $enabled_lang ) );
                break;
            } elseif ( '/' . $enabled_lang === $path ) {
                $path = '/';
                break;
            }
        }
        
        // Ensure path starts with slash
        if ( empty( $path ) || '/' !== $path[0] ) {
            $path = '/' . $path;
        }
        
        // Add new language prefix (except for default language).
        if ( $lang !== $default_lang ) {
            $path = '/' . $lang . $path;
        }
        
        // Clean up double slashes
        $path = preg_replace( '#/+#', '/', $path );
        
        // Rebuild URL.
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
