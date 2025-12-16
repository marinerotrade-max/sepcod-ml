<?php
/**
 * Content processor for translating page content.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Content_Processor class.
 */
class AMST_Content_Processor {
    
    /**
     * Translator.
     *
     * @var AMST_Translator
     */
    private $translator;
    
    /**
     * Language detector.
     *
     * @var AMST_Language_Detector
     */
    private $language_detector;
    
    /**
     * Cache handler.
     *
     * @var AMST_Cache
     */
    private $cache;
    
    /**
     * Constructor.
     *
     * @param AMST_Translator        $translator Translator.
     * @param AMST_Language_Detector $language_detector Language detector.
     * @param AMST_Cache             $cache Cache handler.
     */
    public function __construct( $translator, $language_detector, $cache ) {
        $this->translator = $translator;
        $this->language_detector = $language_detector;
        $this->cache = $cache;
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Content filters.
        add_filter( 'the_content', array( $this, 'translate_the_content' ), 999 );
        add_filter( 'the_title', array( $this, 'translate_the_title' ), 10, 2 );
        add_filter( 'the_excerpt', array( $this, 'translate_the_excerpt' ), 10 );
        add_filter( 'widget_text', array( $this, 'translate_widget_text' ), 10 );
        add_filter( 'get_the_archive_title', array( $this, 'translate_archive_title' ), 10 );
        
        // Menu translation.
        add_filter( 'wp_nav_menu_items', array( $this, 'translate_menu_items' ), 10, 2 );
        
        // Metadata translation.
        add_filter( 'get_post_metadata', array( $this, 'translate_post_meta' ), 10, 4 );
    }
    
    /**
     * Translate the content.
     *
     * @param string $content Content.
     * @return string Translated content.
     */
    public function translate_the_content( $content ) {
        if ( ! $this->should_translate() ) {
            return $content;
        }
        
        if ( empty( $content ) ) {
            return $content;
        }
        
        $current_lang = $this->language_detector->get_current_language();
        return $this->translate_content( $content, $current_lang );
    }
    
    /**
     * Translate the title.
     *
     * @param string $title Title.
     * @param int    $post_id Post ID.
     * @return string Translated title.
     */
    public function translate_the_title( $title, $post_id = 0 ) {
        if ( ! $this->should_translate() ) {
            return $title;
        }
        
        if ( empty( $title ) ) {
            return $title;
        }
        
        $current_lang = $this->language_detector->get_current_language();
        $default_lang = $this->language_detector->get_default_language();
        
        if ( $current_lang === $default_lang ) {
            return $title;
        }
        
        $translated = $this->translator->translate( $title, $current_lang, $default_lang );
        
        if ( is_wp_error( $translated ) ) {
            return $title;
        }
        
        return $translated;
    }
    
    /**
     * Translate the excerpt.
     *
     * @param string $excerpt Excerpt.
     * @return string Translated excerpt.
     */
    public function translate_the_excerpt( $excerpt ) {
        if ( ! $this->should_translate() ) {
            return $excerpt;
        }
        
        if ( empty( $excerpt ) ) {
            return $excerpt;
        }
        
        $current_lang = $this->language_detector->get_current_language();
        return $this->translate_content( $excerpt, $current_lang );
    }
    
    /**
     * Translate widget text.
     *
     * @param string $text Widget text.
     * @return string Translated text.
     */
    public function translate_widget_text( $text ) {
        if ( ! $this->should_translate() ) {
            return $text;
        }
        
        if ( empty( $text ) ) {
            return $text;
        }
        
        $current_lang = $this->language_detector->get_current_language();
        return $this->translate_content( $text, $current_lang );
    }
    
    /**
     * Translate archive title.
     *
     * @param string $title Archive title.
     * @return string Translated title.
     */
    public function translate_archive_title( $title ) {
        if ( ! $this->should_translate() ) {
            return $title;
        }
        
        if ( empty( $title ) ) {
            return $title;
        }
        
        $current_lang = $this->language_detector->get_current_language();
        $default_lang = $this->language_detector->get_default_language();
        
        if ( $current_lang === $default_lang ) {
            return $title;
        }
        
        $translated = $this->translator->translate( $title, $current_lang, $default_lang );
        
        if ( is_wp_error( $translated ) ) {
            return $title;
        }
        
        return $translated;
    }
    
    /**
     * Translate menu items.
     *
     * @param string $items Menu items HTML.
     * @param object $args Menu args.
     * @return string Translated menu items.
     */
    public function translate_menu_items( $items, $args ) {
        if ( ! $this->should_translate() ) {
            return $items;
        }
        
        if ( empty( $items ) ) {
            return $items;
        }
        
        $current_lang = $this->language_detector->get_current_language();
        return $this->translate_content( $items, $current_lang );
    }
    
    /**
     * Translate post metadata.
     *
     * @param mixed  $value Meta value.
     * @param int    $object_id Object ID.
     * @param string $meta_key Meta key.
     * @param bool   $single Single value.
     * @return mixed Translated value.
     */
    public function translate_post_meta( $value, $object_id, $meta_key, $single ) {
        // Only translate specific meta keys.
        $translatable_keys = array(
            '_yoast_wpseo_metadesc',
            '_yoast_wpseo_title',
            'description',
        );
        
        if ( ! in_array( $meta_key, $translatable_keys, true ) ) {
            return $value;
        }
        
        if ( ! $this->should_translate() ) {
            return $value;
        }
        
        // Get the actual meta value.
        remove_filter( 'get_post_metadata', array( $this, 'translate_post_meta' ), 10 );
        $meta_value = get_post_meta( $object_id, $meta_key, $single );
        add_filter( 'get_post_metadata', array( $this, 'translate_post_meta' ), 10, 4 );
        
        if ( empty( $meta_value ) || ! is_string( $meta_value ) ) {
            return $value;
        }
        
        $current_lang = $this->language_detector->get_current_language();
        $default_lang = $this->language_detector->get_default_language();
        
        if ( $current_lang === $default_lang ) {
            return $value;
        }
        
        $translated = $this->translator->translate( $meta_value, $current_lang, $default_lang );
        
        if ( is_wp_error( $translated ) ) {
            return $value;
        }
        
        return $single ? $translated : array( $translated );
    }
    
    /**
     * Translate content.
     *
     * @param string $content Content.
     * @param string $target_lang Target language.
     * @return string Translated content.
     */
    public function translate_content( $content, $target_lang ) {
        $default_lang = $this->language_detector->get_default_language();
        
        if ( $target_lang === $default_lang ) {
            return $content;
        }
        
        // Parse HTML and extract text nodes.
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        
        // Add HTML wrapper to handle fragments.
        $html = '<!DOCTYPE html><html><body>' . $content . '</body></html>';
        $dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        libxml_clear_errors();
        
        $xpath = new DOMXPath( $dom );
        $text_nodes = $xpath->query( '//text()[normalize-space()]' );
        
        $texts_to_translate = array();
        $text_node_map = array();
        
        foreach ( $text_nodes as $index => $node ) {
            $text = trim( $node->nodeValue );
            if ( ! empty( $text ) ) {
                $texts_to_translate[] = $text;
                $text_node_map[ $index ] = $node;
            }
        }
        
        if ( empty( $texts_to_translate ) ) {
            return $content;
        }
        
        // Translate in batch.
        $translations = $this->translator->translate_batch( $texts_to_translate, $target_lang, $default_lang );
        
        if ( is_wp_error( $translations ) ) {
            return $content;
        }
        
        // Replace text nodes with translations.
        foreach ( $text_node_map as $index => $node ) {
            if ( isset( $translations[ $index ] ) ) {
                $node->nodeValue = html_entity_decode( $translations[ $index ], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
            }
        }
        
        // Extract body content.
        $body = $dom->getElementsByTagName( 'body' )->item( 0 );
        if ( ! $body ) {
            return $content;
        }
        
        $translated_content = '';
        foreach ( $body->childNodes as $child ) {
            $translated_content .= $dom->saveHTML( $child );
        }
        
        return $translated_content;
    }
    
    /**
     * Check if content should be translated.
     *
     * @return bool True if should translate.
     */
    private function should_translate() {
        // Don't translate in admin.
        if ( is_admin() && ! wp_doing_ajax() ) {
            return false;
        }
        
        // Check if on default language.
        $current_lang = $this->language_detector->get_current_language();
        $default_lang = $this->language_detector->get_default_language();
        
        if ( $current_lang === $default_lang ) {
            return false;
        }
        
        // Check if translation is enabled for current post type.
        if ( is_singular() ) {
            $post_type = get_post_type();
            
            if ( 'page' === $post_type && ! get_option( 'amst_translate_pages', true ) ) {
                return false;
            }
            
            if ( 'post' === $post_type && ! get_option( 'amst_translate_posts', true ) ) {
                return false;
            }
            
            if ( ! in_array( $post_type, array( 'post', 'page' ), true ) && ! get_option( 'amst_translate_custom_post_types', true ) ) {
                return false;
            }
        }
        
        return true;
    }
}
