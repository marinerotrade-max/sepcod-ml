<?php
/**
 * Translation Engine Class
 * Handles translation using Google Translate API
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_Translation_Engine {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Google Translate API endpoint
     */
    private $api_endpoint = 'https://translation.googleapis.com/language/translate/v2';
    
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
        // Initialize
    }
    
    /**
     * Translate text
     * 
     * @param string $text Text to translate
     * @param string $source_lang Source language code
     * @param string $target_lang Target language code
     * @return string Translated text
     */
    public function translate($text, $source_lang, $target_lang) {
        // Return original if no text
        if (empty($text)) {
            return $text;
        }
        
        // Return original if source and target are the same
        if ($source_lang === $target_lang) {
            return $text;
        }
        
        // Check cache first
        $cache_manager = AMST_Cache_Manager::get_instance();
        $cached = $cache_manager->get_translation($text, $source_lang, $target_lang);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Get API key
        $options = get_option('amst_options');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        
        if (empty($api_key)) {
            return $text; // Return original if no API key
        }
        
        // Prepare text for translation (preserve HTML)
        $html_preserved = $this->preserve_html($text);
        $plain_text = $html_preserved['text'];
        
        // Translate
        $translated = $this->call_google_translate_api($plain_text, $source_lang, $target_lang, $api_key);
        
        // Restore HTML
        $result = $this->restore_html($translated, $html_preserved['placeholders']);
        
        // Cache the result
        $cache_manager->save_translation($text, $source_lang, $target_lang, $result);
        
        return $result;
    }
    
    /**
     * Call Google Translate API
     * 
     * @param string $text Text to translate
     * @param string $source_lang Source language
     * @param string $target_lang Target language
     * @param string $api_key API key
     * @return string Translated text
     */
    private function call_google_translate_api($text, $source_lang, $target_lang, $api_key) {
        $url = add_query_arg([
            'key' => $api_key,
            'q' => $text,
            'source' => $source_lang,
            'target' => $target_lang,
            'format' => 'text'
        ], $this->api_endpoint);
        
        $response = wp_remote_post($url, [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return $text; // Return original on error
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['data']['translations'][0]['translatedText'])) {
            return $data['data']['translations'][0]['translatedText'];
        }
        
        return $text; // Return original if translation failed
    }
    
    /**
     * Preserve HTML tags and special content
     * 
     * @param string $text Text with HTML
     * @return array Array with plain text and placeholders
     */
    private function preserve_html($text) {
        $placeholders = [];
        $counter = 0;
        
        // Preserve HTML tags
        $text = preg_replace_callback('/<[^>]+>/', function($matches) use (&$placeholders, &$counter) {
            $placeholder = '___HTML_TAG_' . $counter . '___';
            $placeholders[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        }, $text);
        
        // Preserve URLs
        $text = preg_replace_callback('/https?:\/\/[^\s]+/', function($matches) use (&$placeholders, &$counter) {
            $placeholder = '___URL_' . $counter . '___';
            $placeholders[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        }, $text);
        
        // Preserve shortcodes
        $text = preg_replace_callback('/\[([^\]]+)\]/', function($matches) use (&$placeholders, &$counter) {
            $placeholder = '___SHORTCODE_' . $counter . '___';
            $placeholders[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        }, $text);
        
        return [
            'text' => $text,
            'placeholders' => $placeholders
        ];
    }
    
    /**
     * Restore preserved HTML and special content
     * 
     * @param string $text Translated text with placeholders
     * @param array $placeholders Placeholder mappings
     * @return string Text with restored HTML
     */
    private function restore_html($text, $placeholders) {
        foreach ($placeholders as $placeholder => $original) {
            $text = str_replace($placeholder, $original, $text);
        }
        return $text;
    }
    
    /**
     * Batch translate multiple texts
     * 
     * @param array $texts Array of texts to translate
     * @param string $source_lang Source language
     * @param string $target_lang Target language
     * @return array Array of translated texts
     */
    public function batch_translate($texts, $source_lang, $target_lang) {
        $translated = [];
        
        foreach ($texts as $key => $text) {
            $translated[$key] = $this->translate($text, $source_lang, $target_lang);
        }
        
        return $translated;
    }
    
    /**
     * Detect language of text
     * 
     * @param string $text Text to detect language
     * @return string Language code
     */
    public function detect_language($text) {
        $options = get_option('amst_options');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        
        if (empty($api_key) || empty($text)) {
            return 'en'; // Default to English
        }
        
        $url = 'https://translation.googleapis.com/language/translate/v2/detect';
        $url = add_query_arg([
            'key' => $api_key,
            'q' => substr($text, 0, 500) // Only check first 500 chars
        ], $url);
        
        $response = wp_remote_post($url);
        
        if (is_wp_error($response)) {
            return 'en';
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['data']['detections'][0][0]['language'])) {
            return $data['data']['detections'][0][0]['language'];
        }
        
        return 'en';
    }
}
