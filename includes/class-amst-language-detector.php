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
     * Detect language from URL.
     */
    public function detect_language() {
        $default_lang = get_option( 'amst_default_language', 'en' );
        $enabled_languages = get_option( 'amst_enabled_languages', array( 'en' ) );
        
        // Get current URL path.
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $path = wp_parse_url( $request_uri, PHP_URL_PATH );
        
        // Remove leading slash.
        $path = ltrim( $path, '/' );
        
        // Check if path starts with language code.
        $path_parts = explode( '/', $path );
        $potential_lang = isset( $path_parts[0] ) ? $path_parts[0] : '';
        
        if ( ! empty( $potential_lang ) && in_array( $potential_lang, $enabled_languages, true ) ) {
            $this->current_language = $potential_lang;
        } else {
            $this->current_language = $default_lang;
        }
        
        // Set as global for easy access.
        $GLOBALS['amst_current_language'] = $this->current_language;
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
     * @return string Default language code.
     */
    public function get_default_language() {
        return get_option( 'amst_default_language', 'en' );
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
            if ( 0 === strpos( $path, '/' . $enabled_lang . '/' ) ) {
                $path = substr( $path, strlen( '/' . $enabled_lang ) );
                break;
            }
        }
        
        // Add new language prefix (except for default language).
        if ( $lang !== $default_lang ) {
            $path = '/' . $lang . $path;
        }
        
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
        $current_url = $this->get_current_url();
        
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
