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
        add_action( 'pre_get_posts', array( $this, 'fix_front_page_query' ), 1 );
        add_filter( 'home_url', array( $this, 'filter_home_url' ), 10, 4 );
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
            
            // CRITICAL: Rule for language prefix only (homepage) - MUST BE FIRST
            // This ensures /de/, /fr/, etc. load the homepage in that language
            add_rewrite_rule(
                '^(' . $lang . ')/?$',
                'index.php?lang=$matches[1]&is_homepage=1',
                'top'
            );
            
            // Rule for language prefix with page slug (e.g., /de/about/)
            add_rewrite_rule(
                '^(' . $lang . ')/([^/]+)/?$',
                'index.php?lang=$matches[1]&pagename=$matches[2]',
                'top'
            );
            
            // Rule for language prefix with deeper paths (e.g., /de/category/post/)
            add_rewrite_rule(
                '^(' . $lang . ')/(.+?)/?$',
                'index.php?lang=$matches[1]&pagename=$matches[2]',
                'top'
            );
        }
        
        // Check if we need to flush rewrite rules
        $rewrite_version = get_option( 'amst_rewrite_version', '0' );
        if ( version_compare( $rewrite_version, AMST_VERSION, '<' ) ) {
            flush_rewrite_rules();
            update_option( 'amst_rewrite_version', AMST_VERSION );
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
        $vars[] = 'is_homepage';
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
    
    /**
     * Fix front page query when language prefix is detected.
     * 
     * This is critical for making /de/, /fr/, etc. load the static front page
     * instead of the blog page.
     *
     * @param WP_Query $query The WP_Query instance.
     */
    public function fix_front_page_query( $query ) {
        // Only run on main query
        if ( ! $query->is_main_query() ) {
            return;
        }
        
        // Check if is_homepage query var is set (from our rewrite rule)
        if ( get_query_var( 'is_homepage' ) ) {
            $page_on_front = get_option( 'page_on_front' );
            
            if ( $page_on_front ) {
                // Set the page_id to the static front page
                $query->set( 'page_id', $page_on_front );
                $query->set( 'is_home', false );
                $query->is_home = false;
                $query->is_front_page = true;
            }
        }
    }
    
    /**
     * Filter home_url to append language prefix for non-default languages.
     * 
     * This ensures home links (like logos) automatically go to the translated homepage.
     *
     * @param string $url The complete home URL including scheme and path.
     * @param string $path Path relative to the home URL.
     * @param string $orig_scheme Scheme to give the home URL context.
     * @param int    $blog_id Blog ID, or null for the current blog.
     * @return string Modified URL with language prefix if needed.
     */
    public function filter_home_url( $url, $path, $orig_scheme, $blog_id ) {
        $current_lang = $this->language_detector->get_current_language();
        $default_lang = $this->language_detector->get_default_language();
        
        // Only modify if we're on a non-default language
        if ( $current_lang === $default_lang ) {
            return $url;
        }
        
        // Don't modify admin URLs
        if ( is_admin() ) {
            return $url;
        }
        
        // If path is empty or just '/', add language prefix
        if ( empty( $path ) || $path === '/' ) {
            $url = trailingslashit( $url ) . $current_lang . '/';
        }
        
        return $url;
    }
}
