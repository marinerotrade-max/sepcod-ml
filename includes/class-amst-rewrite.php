<?php
/**
 * Rewrite Rules Handler
 *
 * Manages URL rewrite rules for language prefixes
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Rewrite class.
 */
class AMST_Rewrite {
    
    /**
     * Language detector instance.
     *
     * @var AMST_Language_Detector
     */
    private $language_detector;
    
    /**
     * Constructor.
     *
     * @param AMST_Language_Detector $language_detector Language detector instance.
     */
    public function __construct( $language_detector ) {
        $this->language_detector = $language_detector;
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'add_rewrite_rules' ), 1 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'prevent_canonical_redirect' ), 1 );
    }
    
    /**
     * Add rewrite rules for language prefixes.
     */
    public function add_rewrite_rules() {
        $languages = $this->language_detector->get_enabled_languages();
        $default_language = $this->language_detector->get_default_language();
        
        foreach ( $languages as $lang ) {
            // Skip default language
            if ( $lang === $default_language ) {
                continue;
            }
            
            // Add rewrite tag
            add_rewrite_tag( '%lang%', '([^/]+)' );
            
            // Rule for language prefix with any path
            add_rewrite_rule(
                '^(' . $lang . ')/(.+?)/?$',
                'index.php?lang=$matches[1]&pagename=$matches[2]',
                'top'
            );
            
            // Rule for language prefix only (homepage)
            add_rewrite_rule(
                '^(' . $lang . ')/?$',
                'index.php?lang=$matches[1]',
                'top'
            );
            
            // Rule for language prefix with post name
            add_rewrite_rule(
                '^(' . $lang . ')/([^/]+)/?$',
                'index.php?lang=$matches[1]&name=$matches[2]',
                'top'
            );
        }
    }
    
    /**
     * Add custom query vars.
     *
     * @param array $vars Existing query vars.
     * @return array Modified query vars.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'lang';
        return $vars;
    }
    
    /**
     * Prevent WordPress from redirecting language-prefixed URLs.
     */
    public function prevent_canonical_redirect() {
        $current_lang = $this->language_detector->get_current_language();
        $default_lang = $this->language_detector->get_default_language();
        
        // If we're on a translated page, prevent redirect
        if ( $current_lang !== $default_lang ) {
            remove_filter( 'template_redirect', 'redirect_canonical' );
        }
    }
}
