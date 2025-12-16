<?php
/**
 * SEO Manager Class
 * Handles SEO enhancements for multilingual content
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_SEO_Manager {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
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
        // Rank Math compatibility
        add_filter('rank_math/frontend/title', [$this, 'translate_seo_title'], 999);
        add_filter('rank_math/frontend/description', [$this, 'translate_seo_description'], 999);
        
        // Yoast SEO compatibility
        add_filter('wpseo_title', [$this, 'translate_seo_title'], 999);
        add_filter('wpseo_metadesc', [$this, 'translate_seo_description'], 999);
        
        // Add Open Graph tags
        add_action('wp_head', [$this, 'add_og_tags'], 5);
    }
    
    /**
     * Add hreflang tags for SEO
     */
    public function add_hreflang_tags() {
        if (!is_singular() && !is_front_page()) {
            return;
        }
        
        $options = get_option('amst_options');
        $enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : ['en'];
        $default_language = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        $current_url = $this->get_current_url();
        
        // Add x-default tag
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($current_url) . '" />' . "\n";
        
        // Add hreflang tags for each enabled language
        foreach ($enabled_languages as $lang) {
            $lang_url = add_query_arg('lang', $lang, $current_url);
            echo '<link rel="alternate" hreflang="' . esc_attr($lang) . '" href="' . esc_url($lang_url) . '" />' . "\n";
        }
    }
    
    /**
     * Get current URL
     * 
     * @return string Current URL
     */
    private function get_current_url() {
        global $wp;
        return home_url(add_query_arg([], $wp->request));
    }
    
    /**
     * Translate SEO title
     * 
     * @param string $title SEO title
     * @return string Translated title
     */
    public function translate_seo_title($title) {
        if (empty($title)) {
            return $title;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $title;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($title, $default_lang, $current_lang);
    }
    
    /**
     * Translate SEO description
     * 
     * @param string $description SEO description
     * @return string Translated description
     */
    public function translate_seo_description($description) {
        if (empty($description)) {
            return $description;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $description;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($description, $default_lang, $current_lang);
    }
    
    /**
     * Add Open Graph tags
     */
    public function add_og_tags() {
        if (!is_singular()) {
            return;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        echo '<meta property="og:locale" content="' . esc_attr($this->get_og_locale($current_lang)) . '" />' . "\n";
        
        // Add alternate locales
        $options = get_option('amst_options');
        $enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : ['en'];
        
        foreach ($enabled_languages as $lang) {
            if ($lang !== $current_lang) {
                echo '<meta property="og:locale:alternate" content="' . esc_attr($this->get_og_locale($lang)) . '" />' . "\n";
            }
        }
    }
    
    /**
     * Get Open Graph locale format
     * 
     * @param string $lang_code Language code
     * @return string OG locale
     */
    private function get_og_locale($lang_code) {
        $locales = [
            'en' => 'en_US',
            'es' => 'es_ES',
            'fr' => 'fr_FR',
            'de' => 'de_DE',
            'it' => 'it_IT',
            'pt' => 'pt_PT',
            'ru' => 'ru_RU',
            'zh' => 'zh_CN',
            'ja' => 'ja_JP',
            'ko' => 'ko_KR',
            'ar' => 'ar_AR',
            'hi' => 'hi_IN',
            'nl' => 'nl_NL',
            'pl' => 'pl_PL',
            'tr' => 'tr_TR',
            'sv' => 'sv_SE',
            'da' => 'da_DK',
            'fi' => 'fi_FI',
            'no' => 'nb_NO',
            'cs' => 'cs_CZ',
            'ro' => 'ro_RO',
            'hu' => 'hu_HU',
            'el' => 'el_GR',
            'he' => 'he_IL',
            'th' => 'th_TH',
            'vi' => 'vi_VN',
            'id' => 'id_ID',
            'uk' => 'uk_UA',
            'bg' => 'bg_BG',
            'hr' => 'hr_HR'
        ];
        
        return isset($locales[$lang_code]) ? $locales[$lang_code] : 'en_US';
    }
    
    /**
     * Generate sitemap for multilingual content
     * 
     * @return string XML sitemap
     */
    public function generate_multilingual_sitemap() {
        $options = get_option('amst_options');
        $enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : ['en'];
        
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        
        // Get all published posts
        $posts = get_posts([
            'post_type' => 'any',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);
        
        foreach ($posts as $post) {
            $permalink = get_permalink($post->ID);
            
            $sitemap .= '  <url>' . "\n";
            $sitemap .= '    <loc>' . esc_url($permalink) . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . get_the_modified_date('c', $post->ID) . '</lastmod>' . "\n";
            
            // Add alternate language URLs
            foreach ($enabled_languages as $lang) {
                $lang_url = add_query_arg('lang', $lang, $permalink);
                $sitemap .= '    <xhtml:link rel="alternate" hreflang="' . esc_attr($lang) . '" href="' . esc_url($lang_url) . '" />' . "\n";
            }
            
            $sitemap .= '  </url>' . "\n";
        }
        
        $sitemap .= '</urlset>';
        
        return $sitemap;
    }
}
