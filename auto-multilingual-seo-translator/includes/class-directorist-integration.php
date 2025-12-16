<?php
/**
 * Directorist Integration Class
 * Handles translation of Directorist directory listings
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_Directorist_Integration {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        if (!class_exists('Directorist_Base')) {
            return;
        }
        
        // Translate listing content
        add_filter('atbdp_listing_content', [$this, 'translate_listing_content'], 999);
        add_filter('atbdp_listing_title', [$this, 'translate_listing_title'], 999);
        add_filter('atbdp_listing_excerpt', [$this, 'translate_listing_excerpt'], 999);
        
        // Translate custom fields
        add_filter('atbdp_custom_field_value', [$this, 'translate_custom_field'], 999, 2);
        
        // Translate categories and tags
        add_filter('atbdp_listing_category_name', [$this, 'translate_term_name'], 999);
        add_filter('atbdp_listing_tag_name', [$this, 'translate_term_name'], 999);
        
        // Translate search and filter labels
        add_filter('atbdp_search_form_labels', [$this, 'translate_search_labels'], 999);
    }
    
    /**
     * Translate listing content
     * 
     * @param string $content Listing content
     * @return string Translated content
     */
    public function translate_listing_content($content) {
        if (empty($content)) {
            return $content;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $content;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($content, $default_lang, $current_lang);
    }
    
    /**
     * Translate listing title
     * 
     * @param string $title Listing title
     * @return string Translated title
     */
    public function translate_listing_title($title) {
        if (empty($title)) {
            return $title;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $title;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($title, $default_lang, $current_lang);
    }
    
    /**
     * Translate listing excerpt
     * 
     * @param string $excerpt Listing excerpt
     * @return string Translated excerpt
     */
    public function translate_listing_excerpt($excerpt) {
        if (empty($excerpt)) {
            return $excerpt;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $excerpt;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($excerpt, $default_lang, $current_lang);
    }
    
    /**
     * Translate custom field value
     * 
     * @param string $value Field value
     * @param array $field Field data
     * @return string Translated value
     */
    public function translate_custom_field($value, $field) {
        if (empty($value) || !is_string($value)) {
            return $value;
        }
        
        // Skip translation for certain field types
        $skip_types = ['email', 'url', 'date', 'time', 'number', 'checkbox', 'radio'];
        if (isset($field['type']) && in_array($field['type'], $skip_types)) {
            return $value;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $value;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($value, $default_lang, $current_lang);
    }
    
    /**
     * Translate term name (categories, tags)
     * 
     * @param string $name Term name
     * @return string Translated name
     */
    public function translate_term_name($name) {
        if (empty($name)) {
            return $name;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $name;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($name, $default_lang, $current_lang);
    }
    
    /**
     * Translate search form labels
     * 
     * @param array $labels Search labels
     * @return array Translated labels
     */
    public function translate_search_labels($labels) {
        if (empty($labels)) {
            return $labels;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $labels;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        
        foreach ($labels as $key => $label) {
            if (!empty($label) && is_string($label)) {
                $labels[$key] = $translation_engine->translate($label, $default_lang, $current_lang);
            }
        }
        
        return $labels;
    }
    
    /**
     * Add language selector to Directorist listings
     */
    public function add_language_selector_to_listing() {
        echo do_shortcode('[language_switcher]');
    }
}
