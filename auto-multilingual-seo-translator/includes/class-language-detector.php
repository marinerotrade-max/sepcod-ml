<?php
/**
 * Language Detector Class
 * Detects user language from browser settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_Language_Detector {
    
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
        add_action('init', [$this, 'detect_and_set_language']);
    }
    
    /**
     * Detect and set user language
     */
    public function detect_and_set_language() {
        // Check if auto-detection is enabled
        $options = get_option('amst_options');
        $auto_detect = isset($options['enable_auto_detect']) ? (bool)$options['enable_auto_detect'] : true;
        
        if (!$auto_detect) {
            return;
        }
        
        // Skip if language is already set by user
        if (isset($_COOKIE['amst_language'])) {
            return;
        }
        
        // Detect language from browser
        $detected_lang = $this->detect_browser_language();
        
        // Check if detected language is enabled
        $enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : ['en'];
        
        if (in_array($detected_lang, $enabled_languages)) {
            $this->set_language($detected_lang);
        }
    }
    
    /**
     * Detect language from browser settings
     * 
     * @return string Language code
     */
    public function detect_browser_language() {
        $lang = 'en'; // Default
        
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return $lang;
        }
        
        $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        
        // Parse Accept-Language header
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $accept_language, $matches);
        
        if (count($matches[1]) > 0) {
            // Create array with language codes and their priorities
            $languages = array_combine($matches[1], $matches[4]);
            
            // Fill in missing priorities with 1
            foreach ($languages as $key => $value) {
                if ($value === '') {
                    $languages[$key] = 1;
                }
            }
            
            // Sort by priority
            arsort($languages);
            
            // Get the first language code
            foreach ($languages as $code => $priority) {
                // Extract primary language code (e.g., 'en' from 'en-US')
                $lang = strtolower(substr($code, 0, 2));
                
                // Check if this language is supported
                $options = get_option('amst_options');
                $enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : ['en'];
                
                if (in_array($lang, $enabled_languages)) {
                    return $lang;
                }
            }
        }
        
        return $lang;
    }
    
    /**
     * Set user language
     * 
     * @param string $language Language code
     */
    public function set_language($language) {
        // Set cookie for 30 days
        setcookie('amst_language', $language, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
        $_COOKIE['amst_language'] = $language;
    }
    
    /**
     * Get current user language
     * 
     * @return string Language code
     */
    public function get_current_language() {
        if (isset($_COOKIE['amst_language'])) {
            return sanitize_text_field($_COOKIE['amst_language']);
        }
        
        $options = get_option('amst_options');
        return isset($options['default_language']) ? $options['default_language'] : 'en';
    }
    
    /**
     * Detect language from IP address (using external service)
     * 
     * @return string Language code
     */
    public function detect_language_from_ip() {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return 'en';
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Skip for localhost
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return 'en';
        }
        
        // You can integrate with IP geolocation services here
        // For now, return English as default
        return 'en';
    }
    
    /**
     * Check if language is RTL
     * 
     * @param string $language Language code
     * @return bool True if RTL
     */
    public function is_rtl_language($language) {
        $rtl_languages = ['ar', 'he', 'fa', 'ur'];
        return in_array($language, $rtl_languages);
    }
}
