<?php
/**
 * Compatibility Manager Class
 * Handles compatibility with LiteSpeed Cache, Rank Math, and Wordfence
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_Compatibility_Manager {
    
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
        $this->init_litespeed_compatibility();
        $this->init_rank_math_compatibility();
        $this->init_wordfence_compatibility();
    }
    
    /**
     * Initialize LiteSpeed Cache compatibility
     */
    private function init_litespeed_compatibility() {
        // Check if LiteSpeed Cache is active
        if (!defined('LSCWP_V') && !defined('LSCACHE_ADV_CACHE')) {
            return;
        }
        
        // Add language to cache vary
        add_filter('litespeed_vary_name', [$this, 'litespeed_add_language_vary']);
        add_filter('litespeed_cache_vary', [$this, 'litespeed_cache_by_language']);
        
        // Exclude translation AJAX requests from cache
        add_filter('litespeed_cache_get_options', [$this, 'litespeed_exclude_ajax']);
        
        // Purge cache when translations are updated
        add_action('amst_clear_cache', [$this, 'litespeed_purge_all']);
    }
    
    /**
     * Add language to LiteSpeed vary
     * 
     * @param array $vary_names Vary names
     * @return array Modified vary names
     */
    public function litespeed_add_language_vary($vary_names) {
        $vary_names[] = 'amst_language';
        return $vary_names;
    }
    
    /**
     * Cache by language in LiteSpeed
     * 
     * @param array $varies Cache varies
     * @return array Modified varies
     */
    public function litespeed_cache_by_language($varies) {
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $varies['amst_language'] = $current_lang;
        
        return $varies;
    }
    
    /**
     * Exclude AJAX from LiteSpeed cache
     * 
     * @param array $options Cache options
     * @return array Modified options
     */
    public function litespeed_exclude_ajax($options) {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'amst_') === 0) {
                $options['cache'] = false;
            }
        }
        
        return $options;
    }
    
    /**
     * Purge all LiteSpeed cache
     */
    public function litespeed_purge_all() {
        if (class_exists('LiteSpeed_Cache_API')) {
            LiteSpeed_Cache_API::purge_all();
        }
    }
    
    /**
     * Initialize Rank Math SEO compatibility
     */
    private function init_rank_math_compatibility() {
        // Check if Rank Math is active
        if (!class_exists('RankMath')) {
            return;
        }
        
        // Hook into Rank Math filters (already handled in SEO Manager)
        // Additional compatibility hooks
        add_filter('rank_math/sitemap/enable', '__return_true');
        add_filter('rank_math/frontend/breadcrumb/items', [$this, 'translate_breadcrumbs']);
    }
    
    /**
     * Translate breadcrumbs for Rank Math
     * 
     * @param array $crumbs Breadcrumb items
     * @return array Translated breadcrumbs
     */
    public function translate_breadcrumbs($crumbs) {
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $crumbs;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        
        foreach ($crumbs as $key => $crumb) {
            if (isset($crumb[0]) && !empty($crumb[0])) {
                $crumbs[$key][0] = $translation_engine->translate($crumb[0], $default_lang, $current_lang);
            }
        }
        
        return $crumbs;
    }
    
    /**
     * Initialize Wordfence compatibility
     */
    private function init_wordfence_compatibility() {
        // Check if Wordfence is active
        if (!class_exists('wordfence')) {
            return;
        }
        
        // Whitelist translation requests
        add_filter('wordfence_ls_require_captcha', [$this, 'wordfence_whitelist_translation']);
        
        // Exclude language cookie from security scan
        add_filter('wordfence_security_event_ignore', [$this, 'wordfence_ignore_language_cookie'], 10, 2);
    }
    
    /**
     * Whitelist translation requests from Wordfence
     * 
     * @param bool $require_captcha Whether to require captcha
     * @return bool Modified requirement
     */
    public function wordfence_whitelist_translation($require_captcha) {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'amst_') === 0) {
                return false;
            }
        }
        
        return $require_captcha;
    }
    
    /**
     * Ignore language cookie in Wordfence security scan
     * 
     * @param bool $ignore Whether to ignore
     * @param array $event Security event
     * @return bool Modified ignore status
     */
    public function wordfence_ignore_language_cookie($ignore, $event) {
        if (isset($event['type']) && $event['type'] === 'cookie') {
            if (isset($event['name']) && $event['name'] === 'amst_language') {
                return true;
            }
        }
        
        return $ignore;
    }
    
    /**
     * Check compatibility with active plugins
     * 
     * @return array Compatibility status
     */
    public function get_compatibility_status() {
        return [
            'litespeed_cache' => defined('LSCWP_V') || defined('LSCACHE_ADV_CACHE'),
            'rank_math' => class_exists('RankMath'),
            'wordfence' => class_exists('wordfence'),
            'elementor' => defined('ELEMENTOR_VERSION'),
            'directorist' => class_exists('Directorist_Base')
        ];
    }
}
