<?php
/**
 * Multilingual sitemap handler.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Sitemap class.
 */
class AMST_Sitemap {
    
    /**
     * Language detector.
     *
     * @var AMST_Language_Detector
     */
    private $language_detector;
    
    /**
     * Constructor.
     *
     * @param AMST_Language_Detector $language_detector Language detector.
     */
    public function __construct( $language_detector ) {
        $this->language_detector = $language_detector;
        
        if ( get_option( 'amst_enable_sitemap', true ) ) {
            add_filter( 'wp_sitemaps_posts_entry', array( $this, 'add_multilingual_urls' ), 10, 3 );
            add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'sitemap_query_args' ) );
        }
    }
    
    /**
     * Add multilingual URLs to sitemap entries.
     *
     * @param array  $entry Sitemap entry.
     * @param object $post Post object.
     * @param string $post_type Post type.
     * @return array Modified entry.
     */
    public function add_multilingual_urls( $entry, $post, $post_type ) {
        $enabled_languages = $this->language_detector->get_enabled_languages();
        $default_lang = $this->language_detector->get_default_language();
        
        // Get post URL.
        $post_url = get_permalink( $post );
        
        // Create alternate entries for each language.
        $alternates = array();
        foreach ( $enabled_languages as $lang ) {
            if ( $lang === $default_lang ) {
                continue;
            }
            
            $lang_url = $this->language_detector->get_language_url( $post_url, $lang );
            $alternates[] = array(
                'hreflang' => $lang,
                'href' => $lang_url,
            );
        }
        
        if ( ! empty( $alternates ) ) {
            $entry['alternates'] = $alternates;
        }
        
        return $entry;
    }
    
    /**
     * Modify sitemap query arguments.
     *
     * @param array $args Query arguments.
     * @return array Modified arguments.
     */
    public function sitemap_query_args( $args ) {
        // Include all post statuses that should be in sitemap.
        $args['post_status'] = 'publish';
        return $args;
    }
    
    /**
     * Generate custom multilingual sitemap XML.
     *
     * @return string Sitemap XML.
     */
    public function generate_multilingual_sitemap() {
        $enabled_languages = $this->language_detector->get_enabled_languages();
        $default_lang = $this->language_detector->get_default_language();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        
        // Get all published posts and pages.
        $post_types = array( 'post', 'page' );
        $args = array(
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'modified',
            'order' => 'DESC',
        );
        
        $posts = get_posts( $args );
        
        foreach ( $posts as $post ) {
            $post_url = get_permalink( $post );
            $modified_date = get_the_modified_date( 'c', $post );
            
            // Add URL for each language.
            foreach ( $enabled_languages as $lang ) {
                $lang_url = $this->language_detector->get_language_url( $post_url, $lang );
                
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . esc_url( $lang_url ) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . esc_html( $modified_date ) . '</lastmod>' . "\n";
                
                // Add alternate links.
                foreach ( $enabled_languages as $alt_lang ) {
                    $alt_url = $this->language_detector->get_language_url( $post_url, $alt_lang );
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . esc_attr( $alt_lang ) . '" href="' . esc_url( $alt_url ) . '" />' . "\n";
                }
                
                // Add x-default.
                $default_url = $this->language_detector->get_language_url( $post_url, $default_lang );
                $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . esc_url( $default_url ) . '" />' . "\n";
                
                $xml .= '  </url>' . "\n";
            }
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
}
