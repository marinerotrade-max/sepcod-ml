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
            translation_type ENUM('manual', 'automatic') DEFAULT 'automatic',
            is_manual tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            hits bigint(20) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY content_hash (content_hash),
            KEY language_pair (source_language, target_language),
            KEY content_type (content_type),
            KEY translation_type (translation_type),
            KEY is_manual (is_manual),
            UNIQUE KEY unique_translation (content_hash, source_language, target_language)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        
        // Run migration for existing installations
        $this->migrate_add_translation_type();
    }
    
    /**
     * Migrate existing database to add translation_type column.
     */
    private function migrate_add_translation_type() {
        global $wpdb;
        
        // Check if column exists
        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = %s 
                AND TABLE_NAME = %s 
                AND COLUMN_NAME = 'translation_type'",
                DB_NAME,
                $this->table_name
            )
        );
        
        if ( empty( $column_exists ) ) {
            $wpdb->query(
                "ALTER TABLE {$this->table_name} 
                ADD COLUMN translation_type ENUM('manual', 'automatic') DEFAULT 'automatic' AFTER content_type,
                ADD COLUMN is_manual tinyint(1) DEFAULT 0 AFTER translation_type,
                ADD KEY translation_type (translation_type),
                ADD KEY is_manual (is_manual)"
            );
        }
    }
    
    /**
     * Get translation from database (manual translations only).
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @return string|false Translation or false if not found.
     */
    public function get_translation( $content_hash, $source_lang, $target_lang ) {
        global $wpdb;
        
        // PRIORITY: Get manual translation first
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT translated_text, translation_type FROM {$this->table_name} 
                WHERE content_hash = %s 
                AND source_language = %s 
                AND target_language = %s 
                AND (translation_type = 'manual' OR is_manual = 1)
                ORDER BY translation_type DESC
                LIMIT 1",
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
     * Check if manual translation exists.
     *
     * @param string $content_hash Content hash.
     * @param string $source_lang Source language.
     * @param string $target_lang Target language.
     * @return bool True if manual translation exists.
     */
    public function has_manual_translation( $content_hash, $source_lang, $target_lang ) {
        global $wpdb;
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} 
                WHERE content_hash = %s 
                AND source_language = %s 
                AND target_language = %s 
                AND (translation_type = 'manual' OR is_manual = 1)",
                $content_hash,
                $source_lang,
                $target_lang
            )
        );
        
        return intval( $result ) > 0;
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
     * @param bool   $is_manual Whether this is a manual translation.
     * @return bool Success status.
     */
    public function save_translation( $content_hash, $source_lang, $target_lang, $original_text, $translated_text, $content_type = 'general', $is_manual = false ) {
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
                'translation_type' => $is_manual ? 'manual' : 'automatic',
                'is_manual' => $is_manual ? 1 : 0,
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
            )
        );
        
        return false !== $result;
    }
    
    /**
     * Save manual translation (always persists to database).
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
        return $this->save_translation( $content_hash, $source_lang, $target_lang, $original_text, $translated_text, $content_type, true );
    }
    
    /**
     * Get all manual translations.
     *
     * @param array $args Query arguments.
     * @return array Manual translations.
     */
    public function get_manual_translations( $args = array() ) {
        global $wpdb;
        
        $where = array( "(translation_type = 'manual' OR is_manual = 1)" );
        $params = array();
        
        if ( ! empty( $args['target_language'] ) ) {
            $where[] = 'target_language = %s';
            $params[] = $args['target_language'];
        }
        
        if ( ! empty( $args['content_type'] ) ) {
            $where[] = 'content_type = %s';
            $params[] = $args['content_type'];
        }
        
        $where_clause = implode( ' AND ', $where );
        $query = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY updated_at DESC";
        
        if ( ! empty( $params ) ) {
            $query = $wpdb->prepare( $query, $params );
        }
        
        return $wpdb->get_results( $query );
    }
    
    /**
     * Delete manual translation.
     *
     * @param int $id Translation ID.
     * @return bool Success status.
     */
    public function delete_manual_translation( $id ) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array( 'id' => $id, 'is_manual' => 1 ),
            array( '%d', '%d' )
        );
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
