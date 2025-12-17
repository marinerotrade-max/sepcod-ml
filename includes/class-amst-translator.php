<?php
/**
 * Google Cloud Translation API handler.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Translator class.
 */
class AMST_Translator {
    
    /**
     * Cache handler.
     *
     * @var AMST_Cache
     */
    private $cache;
    
    /**
     * API endpoint.
     *
     * @var string
     */
    private $api_endpoint = 'https://translation.googleapis.com/language/translate/v2';
    
    /**
     * Constructor.
     *
     * @param AMST_Cache $cache Cache handler.
     */
    public function __construct( $cache ) {
        $this->cache = $cache;
    }
    
    /**
     * Translate text.
     *
     * @param string $text Text to translate.
     * @param string $target_lang Target language.
     * @param string $source_lang Source language.
     * @return string|WP_Error Translated text or error.
     */
    public function translate( $text, $target_lang, $source_lang = 'en' ) {
        // Return original if same language.
        if ( $source_lang === $target_lang ) {
            return $text;
        }
        
        // Check if text is empty.
        if ( empty( trim( $text ) ) ) {
            return $text;
        }
        
        // Generate content hash.
        $content_hash = $this->generate_content_hash( $text );
        
        // Try to get from cache.
        $cached = $this->cache->get_translation( $content_hash, $source_lang, $target_lang );
        if ( false !== $cached ) {
            return $cached;
        }
        
        // Get API key.
        $api_key = get_option( 'amst_api_key', '' );
        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', __( 'Google Cloud Translation API key is not configured.', 'auto-multilingual-seo' ) );
        }
        
        // Prepare request.
        $url = add_query_arg( 'key', $api_key, $this->api_endpoint );
        
        $body = array(
            'q' => $text,
            'target' => $target_lang,
            'source' => $source_lang,
            'format' => 'html',
        );
        
        // Make API request.
        $response = wp_remote_post(
            $url,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode( $body ),
                'timeout' => 30,
            )
        );
        
        // Check for errors.
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $status_code ) {
            $body = wp_remote_retrieve_body( $response );
            $error_data = json_decode( $body, true );
            $error_message = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : __( 'Translation API error', 'auto-multilingual-seo' );
            return new WP_Error( 'api_error', $error_message, array( 'status' => $status_code ) );
        }
        
        // Parse response.
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( ! isset( $data['data']['translations'][0]['translatedText'] ) ) {
            return new WP_Error( 'invalid_response', __( 'Invalid API response', 'auto-multilingual-seo' ) );
        }
        
        $translated_text = $data['data']['translations'][0]['translatedText'];
        
        // Save to cache.
        $this->cache->save_translation( $content_hash, $source_lang, $target_lang, $text, $translated_text );
        
        return $translated_text;
    }
    
    /**
     * Translate multiple texts in batch.
     *
     * @param array  $texts Array of texts to translate.
     * @param string $target_lang Target language.
     * @param string $source_lang Source language.
     * @return array|WP_Error Array of translations or error.
     */
    public function translate_batch( $texts, $target_lang, $source_lang = 'en' ) {
        if ( empty( $texts ) || ! is_array( $texts ) ) {
            return array();
        }
        
        // Return original if same language.
        if ( $source_lang === $target_lang ) {
            return $texts;
        }
        
        $translations = array();
        $texts_to_translate = array();
        $text_indexes = array();
        
        // Check cache for each text.
        foreach ( $texts as $index => $text ) {
            if ( empty( trim( $text ) ) ) {
                $translations[ $index ] = $text;
                continue;
            }
            
            $content_hash = $this->generate_content_hash( $text );
            $cached = $this->cache->get_translation( $content_hash, $source_lang, $target_lang );
            
            if ( false !== $cached ) {
                $translations[ $index ] = $cached;
            } else {
                $texts_to_translate[] = $text;
                $text_indexes[] = $index;
            }
        }
        
        // If all texts are cached, return.
        if ( empty( $texts_to_translate ) ) {
            return $translations;
        }
        
        // Get API key.
        $api_key = get_option( 'amst_api_key', '' );
        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', __( 'Google Cloud Translation API key is not configured.', 'auto-multilingual-seo' ) );
        }
        
        // Prepare request.
        $url = add_query_arg( 'key', $api_key, $this->api_endpoint );
        
        $body = array(
            'q' => $texts_to_translate,
            'target' => $target_lang,
            'source' => $source_lang,
            'format' => 'html',
        );
        
        // Make API request.
        $response = wp_remote_post(
            $url,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode( $body ),
                'timeout' => 60,
            )
        );
        
        // Check for errors.
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $status_code ) {
            $body = wp_remote_retrieve_body( $response );
            $error_data = json_decode( $body, true );
            $error_message = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : __( 'Translation API error', 'auto-multilingual-seo' );
            return new WP_Error( 'api_error', $error_message, array( 'status' => $status_code ) );
        }
        
        // Parse response.
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( ! isset( $data['data']['translations'] ) ) {
            return new WP_Error( 'invalid_response', __( 'Invalid API response', 'auto-multilingual-seo' ) );
        }
        
        // Process translations.
        foreach ( $data['data']['translations'] as $key => $translation ) {
            $index = $text_indexes[ $key ];
            $translated_text = $translation['translatedText'];
            $translations[ $index ] = $translated_text;
            
            // Save to cache.
            $content_hash = $this->generate_content_hash( $texts_to_translate[ $key ] );
            $this->cache->save_translation( $content_hash, $source_lang, $target_lang, $texts_to_translate[ $key ], $translated_text );
        }
        
        return $translations;
    }
    
    /**
     * Generate content hash.
     *
     * @param string $content Content.
     * @return string Hash.
     */
    private function generate_content_hash( $content ) {
        return hash( 'sha256', $content );
    }
    
    /**
     * Get supported languages (EU countries only).
     *
     * @return array Supported languages.
     */
    public function get_supported_languages() {
        return array(
            'bg' => __( 'Bulgarian', 'auto-multilingual-seo' ),
            'hr' => __( 'Croatian', 'auto-multilingual-seo' ),
            'cs' => __( 'Czech', 'auto-multilingual-seo' ),
            'da' => __( 'Danish', 'auto-multilingual-seo' ),
            'nl' => __( 'Dutch', 'auto-multilingual-seo' ),
            'en' => __( 'English', 'auto-multilingual-seo' ),
            'et' => __( 'Estonian', 'auto-multilingual-seo' ),
            'fi' => __( 'Finnish', 'auto-multilingual-seo' ),
            'fr' => __( 'French', 'auto-multilingual-seo' ),
            'de' => __( 'German', 'auto-multilingual-seo' ),
            'el' => __( 'Greek', 'auto-multilingual-seo' ),
            'hu' => __( 'Hungarian', 'auto-multilingual-seo' ),
            'ga' => __( 'Irish', 'auto-multilingual-seo' ),
            'it' => __( 'Italian', 'auto-multilingual-seo' ),
            'lv' => __( 'Latvian', 'auto-multilingual-seo' ),
            'lt' => __( 'Lithuanian', 'auto-multilingual-seo' ),
            'mt' => __( 'Maltese', 'auto-multilingual-seo' ),
            'pl' => __( 'Polish', 'auto-multilingual-seo' ),
            'pt' => __( 'Portuguese', 'auto-multilingual-seo' ),
            'ro' => __( 'Romanian', 'auto-multilingual-seo' ),
            'sk' => __( 'Slovak', 'auto-multilingual-seo' ),
            'sl' => __( 'Slovenian', 'auto-multilingual-seo' ),
            'es' => __( 'Spanish', 'auto-multilingual-seo' ),
            'sv' => __( 'Swedish', 'auto-multilingual-seo' ),
        );
    }
}
