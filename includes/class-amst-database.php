<?php
/**
 * Database handler.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Database class.
 */
class AMST_Database {
    
    /**
     * Table name for translations.
     *
     * @var string
     */
    private $table_name;
    
    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'amst_translations';
    }
    
    /**
     * Create database tables.
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            content_hash varchar(64) NOT NULL,
            source_language varchar(10) NOT NULL,
            target_language varchar(10) NOT NULL,
            original_text longtext NOT NULL,
            translated_text longtext NOT NULL,
            content_type varchar(50) DEFAULT 'general',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            hits bigint(20) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY content_hash (content_hash),
            KEY language_pair (source_language, target_language),
            KEY content_type (content_type),
            UNIQUE KEY unique_translation (content_hash, source_language, target_language)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
    
    /**
     * Get translation from database.
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @return string|false Translation or false if not found.
     */
    public function get_translation( $content_hash, $source_lang, $target_lang ) {
        global $wpdb;
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT translated_text FROM {$this->table_name} 
                WHERE content_hash = %s 
                AND source_language = %s 
                AND target_language = %s",
                $content_hash,
                $source_lang,
                $target_lang
            )
        );
        
        if ( $result ) {
            // Increment hit counter.
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$this->table_name} SET hits = hits + 1 WHERE content_hash = %s AND source_language = %s AND target_language = %s",
                    $content_hash,
                    $source_lang,
                    $target_lang
                )
            );
            return $result->translated_text;
        }
        
        return false;
    }
    
    /**
     * Save translation to database.
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
        global $wpdb;
        
        $result = $wpdb->replace(
            $this->table_name,
            array(
                'content_hash' => $content_hash,
                'source_language' => $source_lang,
                'target_language' => $target_lang,
                'original_text' => $original_text,
                'translated_text' => $translated_text,
                'content_type' => $content_type,
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
        
        return false !== $result;
    }
    
    /**
     * Delete translations.
     *
     * @param array $args Query arguments.
     * @return bool Success status.
     */
    public function delete_translations( $args = array() ) {
        global $wpdb;
        
        $where = array();
        $where_format = array();
        
        if ( ! empty( $args['target_language'] ) ) {
            $where['target_language'] = $args['target_language'];
            $where_format[] = '%s';
        }
        
        if ( ! empty( $args['content_type'] ) ) {
            $where['content_type'] = $args['content_type'];
            $where_format[] = '%s';
        }
        
        if ( empty( $where ) ) {
            return $wpdb->query( "TRUNCATE TABLE {$this->table_name}" );
        }
        
        return $wpdb->delete( $this->table_name, $where, $where_format );
    }
    
    /**
     * Get translation statistics.
     *
     * @return array Statistics.
     */
    public function get_statistics() {
        global $wpdb;
        
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
        
        $by_language = $wpdb->get_results(
            "SELECT target_language, COUNT(*) as count 
            FROM {$this->table_name} 
            GROUP BY target_language"
        );
        
        $by_type = $wpdb->get_results(
            "SELECT content_type, COUNT(*) as count 
            FROM {$this->table_name} 
            GROUP BY content_type"
        );
        
        $total_hits = $wpdb->get_var( "SELECT SUM(hits) FROM {$this->table_name}" );
        
        return array(
            'total' => intval( $total ),
            'by_language' => $by_language,
            'by_type' => $by_type,
            'total_hits' => intval( $total_hits ),
        );
    }
}
