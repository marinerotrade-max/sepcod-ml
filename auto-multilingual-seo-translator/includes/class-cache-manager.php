<?php
/**
 * Cache Manager Class
 * Handles caching of translations for performance
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_Cache_Manager {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Cache group
     */
    private $cache_group = 'amst_translations';
    
    /**
     * Cache expiration (24 hours)
     */
    private $cache_expiration = 86400;
    
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
        // Check if caching is enabled
        $options = get_option('amst_options');
        $this->cache_enabled = isset($options['enable_cache']) ? (bool)$options['enable_cache'] : true;
        
        // Add action to clear cache
        add_action('amst_clear_cache', [$this, 'clear_cache']);
    }
    
    /**
     * Get translation from cache
     * 
     * @param string $text Original text
     * @param string $source_lang Source language
     * @param string $target_lang Target language
     * @return string|false Cached translation or false if not found
     */
    public function get_translation($text, $source_lang, $target_lang) {
        if (!$this->cache_enabled) {
            return false;
        }
        
        $cache_key = $this->generate_cache_key($text, $source_lang, $target_lang);
        
        // Try WordPress object cache first
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Try transient cache
        $transient_key = 'amst_' . md5($cache_key);
        $cached = get_transient($transient_key);
        
        if ($cached !== false) {
            // Set in object cache for faster access
            wp_cache_set($cache_key, $cached, $this->cache_group, $this->cache_expiration);
            return $cached;
        }
        
        return false;
    }
    
    /**
     * Save translation to cache
     * 
     * @param string $text Original text
     * @param string $source_lang Source language
     * @param string $target_lang Target language
     * @param string $translation Translated text
     * @return bool Success status
     */
    public function save_translation($text, $source_lang, $target_lang, $translation) {
        if (!$this->cache_enabled) {
            return false;
        }
        
        $cache_key = $this->generate_cache_key($text, $source_lang, $target_lang);
        
        // Save to WordPress object cache
        wp_cache_set($cache_key, $translation, $this->cache_group, $this->cache_expiration);
        
        // Save to transient cache (for persistent cache)
        $transient_key = 'amst_' . md5($cache_key);
        set_transient($transient_key, $translation, $this->cache_expiration);
        
        return true;
    }
    
    /**
     * Generate cache key
     * 
     * @param string $text Text
     * @param string $source_lang Source language
     * @param string $target_lang Target language
     * @return string Cache key
     */
    private function generate_cache_key($text, $source_lang, $target_lang) {
        return md5($text . '_' . $source_lang . '_' . $target_lang);
    }
    
    /**
     * Clear all translation cache
     * 
     * @return bool Success status
     */
    public function clear_cache() {
        global $wpdb;
        
        // Clear WordPress object cache
        wp_cache_flush();
        
        // Clear transient cache
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_amst_%' OR option_name LIKE '_transient_timeout_amst_%'"
        );
        
        return true;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public function get_cache_stats() {
        global $wpdb;
        
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_amst_%'"
        );
        
        return [
            'cached_translations' => (int)$count,
            'cache_enabled' => $this->cache_enabled
        ];
    }
    
    /**
     * Check if cache is enabled
     * 
     * @return bool Cache enabled status
     */
    public function is_cache_enabled() {
        return $this->cache_enabled;
    }
    
    /**
     * Enable cache
     */
    public function enable_cache() {
        $options = get_option('amst_options');
        $options['enable_cache'] = 1;
        update_option('amst_options', $options);
        $this->cache_enabled = true;
    }
    
    /**
     * Disable cache
     */
    public function disable_cache() {
        $options = get_option('amst_options');
        $options['enable_cache'] = 0;
        update_option('amst_options', $options);
        $this->cache_enabled = false;
    }
}
