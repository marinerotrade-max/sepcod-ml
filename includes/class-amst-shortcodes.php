<?php
/**
 * Shortcodes handler.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Shortcodes class.
 */
class AMST_Shortcodes {
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode( 'amst_language_switcher', array( $this, 'language_switcher_shortcode' ) );
        add_shortcode( 'amst_current_language', array( $this, 'current_language_shortcode' ) );
        add_shortcode( 'amst_translate', array( $this, 'translate_shortcode' ) );
    }
    
    /**
     * Language switcher shortcode.
     *
     * Usage: [amst_language_switcher]
     *
     * @param array $atts Shortcode attributes.
     * @return string Language switcher HTML.
     */
    public function language_switcher_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'style' => 'default',
            ),
            $atts,
            'amst_language_switcher'
        );
        
        $language_detector = amst()->language_detector;
        return $language_detector->get_language_switcher();
    }
    
    /**
     * Current language shortcode.
     *
     * Usage: [amst_current_language]
     *
     * @param array $atts Shortcode attributes.
     * @return string Current language code or name.
     */
    public function current_language_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'format' => 'code', // 'code' or 'name'
            ),
            $atts,
            'amst_current_language'
        );
        
        $language_detector = amst()->language_detector;
        $current_lang = $language_detector->get_current_language();
        
        if ( 'name' === $atts['format'] ) {
            $translator = amst()->translator;
            $supported_languages = $translator->get_supported_languages();
            return isset( $supported_languages[ $current_lang ] ) ? $supported_languages[ $current_lang ] : $current_lang;
        }
        
        return $current_lang;
    }
    
    /**
     * Translate text shortcode.
     *
     * Usage: [amst_translate]Text to translate[/amst_translate]
     *
     * @param array  $atts Shortcode attributes.
     * @param string $content Content to translate.
     * @return string Translated content.
     */
    public function translate_shortcode( $atts, $content = '' ) {
        if ( empty( $content ) ) {
            return '';
        }
        
        $atts = shortcode_atts(
            array(
                'target' => '', // Target language (empty = current language)
                'source' => '', // Source language (empty = default language)
            ),
            $atts,
            'amst_translate'
        );
        
        $language_detector = amst()->language_detector;
        $translator = amst()->translator;
        
        $target_lang = ! empty( $atts['target'] ) ? $atts['target'] : $language_detector->get_current_language();
        $source_lang = ! empty( $atts['source'] ) ? $atts['source'] : $language_detector->get_default_language();
        
        $translated = $translator->translate( $content, $target_lang, $source_lang );
        
        if ( is_wp_error( $translated ) ) {
            return $content;
        }
        
        return $translated;
    }
}

// Initialize shortcodes.
new AMST_Shortcodes();
