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
     * Get translation from cache (manual translations only from database).
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @param bool   $check_transient Whether to check transient for automatic translations.
     * @return string|false Translation or false if not found.
     */
    public function get_translation( $content_hash, $source_lang, $target_lang, $check_transient = false ) {
        if ( ! get_option( 'amst_enable_cache', true ) ) {
            return false;
        }
        
        // PRIORITY 1: Try database for manual translations ONLY
        $translation = $this->database->get_translation( $content_hash, $source_lang, $target_lang );
        
        if ( false !== $translation ) {
            // Fix UTF-8 encoding issues on retrieval
            $translation = AMST_UTF8_Helper::fix_translation_encoding( $translation );
            
            // Store in object cache for fast retrieval
            $cache_key = $this->get_cache_key( $content_hash, $source_lang, $target_lang );
            $expiry = get_option( 'amst_cache_expiry', 2592000 );
            wp_cache_set( $cache_key, $translation, $this->cache_group, $expiry );
            return $translation;
        }
        
        // PRIORITY 2: Check transient for automatic translations (temporary storage)
        if ( $check_transient ) {
            $transient_key = 'amst_auto_' . $this->get_cache_key( $content_hash, $source_lang, $target_lang );
            $automatic_translation = get_transient( $transient_key );
            if ( false !== $automatic_translation ) {
                // Fix UTF-8 encoding issues on retrieval
                $automatic_translation = AMST_UTF8_Helper::fix_translation_encoding( $automatic_translation );
                return $automatic_translation;
            }
        }
        
        return false;
    }
    
    /**
     * Get automatic translation from transient (temporary storage).
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @return string|false Translation or false if not found.
     */
    public function get_automatic_translation( $content_hash, $source_lang, $target_lang ) {
        $transient_key = 'amst_auto_' . $this->get_cache_key( $content_hash, $source_lang, $target_lang );
        $translation = get_transient( $transient_key );
        
        if ( false !== $translation ) {
            // Fix UTF-8 encoding issues on retrieval
            $translation = AMST_UTF8_Helper::fix_translation_encoding( $translation );
        }
        
        return $translation;
    }
    
    /**
     * Save automatic translation to transient (temporary, NOT database).
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @param string $translated_text Translated text.
     * @param int    $expiration Expiration time in seconds (default: 1 hour).
     * @return bool Success status.
     */
    public function save_automatic_translation( $content_hash, $source_lang, $target_lang, $translated_text, $expiration = 3600 ) {
        $transient_key = 'amst_auto_' . $this->get_cache_key( $content_hash, $source_lang, $target_lang );
        return set_transient( $transient_key, $translated_text, $expiration );
    }
    
    /**
     * Save manual translation to cache (persists to database).
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @param string $original_text Original text.
     * @param string $translated_text Translated text.
     * @param string $content_type Content type.
     * @return bool Success status.
     */
    public function save_manual_translation( $content_hash, $source_lang, $target_lang, $original_text, $translated_text, $content_type = 'general' ) {
        if ( ! get_option( 'amst_enable_cache', true ) ) {
            return false;
        }
        
        // Save to database with manual flag.
        $saved = $this->database->save_manual_translation( $content_hash, $source_lang, $target_lang, $original_text, $translated_text, $content_type );
        
        if ( $saved ) {
            // Save to object cache.
            $cache_key = $this->get_cache_key( $content_hash, $source_lang, $target_lang );
            $expiry = get_option( 'amst_cache_expiry', 2592000 );
            wp_cache_set( $cache_key, $translated_text, $this->cache_group, $expiry );
        }
        
        return $saved;
    }
    
    /**
     * Legacy method - now deprecated, automatic translations are NOT saved to database.
     *
     * @deprecated Use save_manual_translation() or save_automatic_translation() instead.
     */
    public function save_translation( $content_hash, $source_lang, $target_lang, $original_text, $translated_text, $content_type = 'general' ) {
        // For backward compatibility, save as automatic (transient only)
        return $this->save_automatic_translation( $content_hash, $source_lang, $target_lang, $translated_text );
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
