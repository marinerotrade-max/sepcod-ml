<?php
/**
 * Third-party plugin integrations (Elementor, Directorist, LiteSpeed).
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Integrations class.
 */
class AMST_Integrations {
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init_integrations' ) );
    }
    
    /**
     * Initialize integrations.
     */
    public function init_integrations() {
        // Elementor integration.
        if ( defined( 'ELEMENTOR_VERSION' ) ) {
            $this->init_elementor_integration();
        }
        
        // Directorist integration.
        if ( defined( 'DIRECTORIST_VERSION' ) ) {
            $this->init_directorist_integration();
        }
        
        // LiteSpeed Cache integration.
        if ( defined( 'LSCWP_V' ) ) {
            $this->init_litespeed_integration();
        }
    }
    
    /**
     * Initialize Elementor integration.
     */
    private function init_elementor_integration() {
        // Hook into Elementor content rendering.
        add_filter( 'elementor/frontend/the_content', array( $this, 'translate_elementor_content' ) );
        add_filter( 'elementor/widget/render_content', array( $this, 'translate_elementor_widget' ), 10, 2 );
    }
    
    /**
     * Translate Elementor content.
     *
     * @param string $content Content.
     * @return string Translated content.
     */
    public function translate_elementor_content( $content ) {
        if ( empty( $content ) ) {
            return $content;
        }
        
        $language_detector = amst()->language_detector;
        $current_lang = $language_detector->get_current_language();
        $default_lang = $language_detector->get_default_language();
        
        if ( $current_lang === $default_lang ) {
            return $content;
        }
        
        $content_processor = amst()->content_processor;
        return $content_processor->translate_content( $content, $current_lang );
    }
    
    /**
     * Translate Elementor widget.
     *
     * @param string $content Widget content.
     * @param object $widget Widget object.
     * @return string Translated content.
     */
    public function translate_elementor_widget( $content, $widget ) {
        return $this->translate_elementor_content( $content );
    }
    
    /**
     * Initialize Directorist integration.
     */
    private function init_directorist_integration() {
        // Hook into Directorist content filters.
        add_filter( 'atbdp_listing_content', array( $this, 'translate_directorist_content' ) );
        add_filter( 'atbdp_listing_title', array( $this, 'translate_directorist_title' ) );
        add_filter( 'atbdp_listing_excerpt', array( $this, 'translate_directorist_content' ) );
    }
    
    /**
     * Translate Directorist content.
     *
     * @param string $content Content.
     * @return string Translated content.
     */
    public function translate_directorist_content( $content ) {
        if ( empty( $content ) ) {
            return $content;
        }
        
        $language_detector = amst()->language_detector;
        $current_lang = $language_detector->get_current_language();
        $default_lang = $language_detector->get_default_language();
        
        if ( $current_lang === $default_lang ) {
            return $content;
        }
        
        $content_processor = amst()->content_processor;
        return $content_processor->translate_content( $content, $current_lang );
    }
    
    /**
     * Translate Directorist title.
     *
     * @param string $title Title.
     * @return string Translated title.
     */
    public function translate_directorist_title( $title ) {
        if ( empty( $title ) ) {
            return $title;
        }
        
        $language_detector = amst()->language_detector;
        $current_lang = $language_detector->get_current_language();
        $default_lang = $language_detector->get_default_language();
        
        if ( $current_lang === $default_lang ) {
            return $title;
        }
        
        $translator = amst()->translator;
        $translated = $translator->translate( $title, $current_lang, $default_lang );
        
        if ( is_wp_error( $translated ) ) {
            return $title;
        }
        
        return $translated;
    }
    
    /**
     * Initialize LiteSpeed Cache integration.
     */
    private function init_litespeed_integration() {
        // Add language to cache vary.
        add_filter( 'litespeed_vary_name', array( $this, 'add_language_vary' ) );
        add_filter( 'litespeed_cache_tag', array( $this, 'add_language_cache_tag' ) );
        
        // Purge cache when translations are cleared.
        add_action( 'amst_translations_cleared', array( $this, 'purge_litespeed_cache' ) );
    }
    
    /**
     * Add language to LiteSpeed Cache vary.
     *
     * @param array $vary_names Vary names.
     * @return array Modified vary names.
     */
    public function add_language_vary( $vary_names ) {
        $language_detector = amst()->language_detector;
        $current_lang = $language_detector->get_current_language();
        
        if ( ! is_array( $vary_names ) ) {
            $vary_names = array();
        }
        
        $vary_names[] = 'amst_lang_' . $current_lang;
        
        return $vary_names;
    }
    
    /**
     * Add language to cache tag.
     *
     * @param array $tags Cache tags.
     * @return array Modified tags.
     */
    public function add_language_cache_tag( $tags ) {
        $language_detector = amst()->language_detector;
        $current_lang = $language_detector->get_current_language();
        
        if ( ! is_array( $tags ) ) {
            $tags = array();
        }
        
        $tags[] = 'amst_lang_' . $current_lang;
        
        return $tags;
    }
    
    /**
     * Purge LiteSpeed cache.
     */
    public function purge_litespeed_cache() {
        if ( ! defined( 'LSCWP_V' ) ) {
            return;
        }
        
        do_action( 'litespeed_purge_all' );
    }
}
