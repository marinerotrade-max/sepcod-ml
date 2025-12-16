<?php
/**
 * SEO features handler (hreflang and canonical tags).
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_SEO class.
 */
class AMST_SEO {
    
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
        
        add_action( 'wp_head', array( $this, 'add_hreflang_tags' ), 1 );
        add_action( 'wp_head', array( $this, 'add_canonical_tag' ), 2 );
    }
    
    /**
     * Add hreflang tags to head.
     */
    public function add_hreflang_tags() {
        if ( ! get_option( 'amst_enable_hreflang', true ) ) {
            return;
        }
        
        $current_url = $this->language_detector->get_current_url();
        $enabled_languages = $this->language_detector->get_enabled_languages();
        $default_lang = $this->language_detector->get_default_language();
        
        // Add hreflang for each enabled language.
        foreach ( $enabled_languages as $lang ) {
            $lang_url = $this->language_detector->get_language_url( $current_url, $lang );
            printf(
                '<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
                esc_attr( $lang ),
                esc_url( $lang_url )
            );
        }
        
        // Add x-default hreflang.
        $default_url = $this->language_detector->get_language_url( $current_url, $default_lang );
        printf(
            '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n",
            esc_url( $default_url )
        );
    }
    
    /**
     * Add canonical tag to head.
     */
    public function add_canonical_tag() {
        if ( ! get_option( 'amst_enable_canonical', true ) ) {
            return;
        }
        
        $current_url = $this->language_detector->get_current_url();
        $current_lang = $this->language_detector->get_current_language();
        $canonical_url = $this->language_detector->get_language_url( $current_url, $current_lang );
        
        printf(
            '<link rel="canonical" href="%s" />' . "\n",
            esc_url( $canonical_url )
        );
    }
    
    /**
     * Get meta description translated.
     *
     * @param string $description Meta description.
     * @param string $target_lang Target language.
     * @return string Translated description.
     */
    public function translate_meta_description( $description, $target_lang ) {
        if ( empty( $description ) ) {
            return $description;
        }
        
        $translator = amst()->translator;
        $source_lang = $this->language_detector->get_default_language();
        
        if ( $source_lang === $target_lang ) {
            return $description;
        }
        
        $translated = $translator->translate( $description, $target_lang, $source_lang );
        
        if ( is_wp_error( $translated ) ) {
            return $description;
        }
        
        return $translated;
    }
    
    /**
     * Get meta title translated.
     *
     * @param string $title Meta title.
     * @param string $target_lang Target language.
     * @return string Translated title.
     */
    public function translate_meta_title( $title, $target_lang ) {
        if ( empty( $title ) ) {
            return $title;
        }
        
        $translator = amst()->translator;
        $source_lang = $this->language_detector->get_default_language();
        
        if ( $source_lang === $target_lang ) {
            return $title;
        }
        
        $translated = $translator->translate( $title, $target_lang, $source_lang );
        
        if ( is_wp_error( $translated ) ) {
            return $title;
        }
        
        return $translated;
    }
}
