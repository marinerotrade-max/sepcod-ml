<?php
/**
 * Cache handler with database integration.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Cache class.
 */
class AMST_Cache {
    
    /**
     * Database handler.
     *
     * @var AMST_Database
     */
    private $database;
    
    /**
     * Cache group.
     *
     * @var string
     */
    private $cache_group = 'amst_translations';
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->database = new AMST_Database();
    }
    
    /**
     * Get translation from cache.
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @return string|false Translation or false if not found.
     */
    public function get_translation( $content_hash, $source_lang, $target_lang ) {
        if ( ! get_option( 'amst_enable_cache', true ) ) {
            return false;
        }
        
        // Try WordPress object cache first.
        $cache_key = $this->get_cache_key( $content_hash, $source_lang, $target_lang );
        $cached = wp_cache_get( $cache_key, $this->cache_group );
        
        if ( false !== $cached ) {
            return $cached;
        }
        
        // Try database.
        $translation = $this->database->get_translation( $content_hash, $source_lang, $target_lang );
        
        if ( false !== $translation ) {
            // Store in object cache.
            $expiry = get_option( 'amst_cache_expiry', 2592000 );
            wp_cache_set( $cache_key, $translation, $this->cache_group, $expiry );
            return $translation;
        }
        
        return false;
    }
    
    /**
     * Save translation to cache.
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @param string $original_text Original text.
     * @param string $translated_text Translated text.
     * @param string $content_type Content type.
     * @return bool Success status.
     */
    public function save_translation( $content_hash, $source_lang, $target_lang, $original_text, $translated_text, $content_type = 'general' ) {
        if ( ! get_option( 'amst_enable_cache', true ) ) {
            return false;
        }
        
        // Save to database.
        $saved = $this->database->save_translation( $content_hash, $source_lang, $target_lang, $original_text, $translated_text, $content_type );
        
        if ( $saved ) {
            // Save to object cache.
            $cache_key = $this->get_cache_key( $content_hash, $source_lang, $target_lang );
            $expiry = get_option( 'amst_cache_expiry', 2592000 );
            wp_cache_set( $cache_key, $translated_text, $this->cache_group, $expiry );
        }
        
        return $saved;
    }
    
    /**
     * Clear cache.
     *
     * @param array $args Clear arguments.
     * @return bool Success status.
     */
    public function clear_cache( $args = array() ) {
        // Clear object cache.
        wp_cache_flush();
        
        // Clear database.
        return $this->database->delete_translations( $args );
    }
    
    /**
     * Get cache key.
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @return string Cache key.
     */
    private function get_cache_key( $content_hash, $source_lang, $target_lang ) {
        return sprintf( '%s_%s_%s', $content_hash, $source_lang, $target_lang );
    }
    
    /**
     * Get cache statistics.
     *
     * @return array Statistics.
     */
    public function get_statistics() {
        return $this->database->get_statistics();
    }
    
    /**
     * Purge LiteSpeed cache.
     */
    public function purge_litespeed_cache() {
        if ( ! defined( 'LSCWP_V' ) ) {
            return;
        }
        
        // LiteSpeed Cache purge.
        do_action( 'litespeed_purge_all' );
    }
}
