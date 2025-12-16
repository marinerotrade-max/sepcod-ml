<?php
/**
 * Elementor Integration Class
 * Handles translation of Elementor content
 */

if (!defined('ABSPATH')) {
    exit;
}

class AMST_Elementor_Integration {
    
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
        if (!defined('ELEMENTOR_VERSION')) {
            return;
        }
        
        // Hook into Elementor rendering
        add_filter('elementor/frontend/the_content', [$this, 'translate_elementor_content'], 999);
        add_filter('elementor/widget/render_content', [$this, 'translate_widget_content'], 999, 2);
        
        // Translate Elementor settings
        add_action('elementor/element/before_section_start', [$this, 'add_translation_controls'], 10, 3);
    }
    
    /**
     * Translate Elementor content
     * 
     * @param string $content Content to translate
     * @return string Translated content
     */
    public function translate_elementor_content($content) {
        if (empty($content)) {
            return $content;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $content;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($content, $default_lang, $current_lang);
    }
    
    /**
     * Translate widget content
     * 
     * @param string $content Widget content
     * @param object $widget Widget instance
     * @return string Translated content
     */
    public function translate_widget_content($content, $widget) {
        if (empty($content)) {
            return $content;
        }
        
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($current_lang === $default_lang) {
            return $content;
        }
        
        // Get widget settings
        $settings = $widget->get_settings_for_display();
        
        // Translate specific fields based on widget type
        $content = $this->translate_widget_fields($content, $settings, $widget->get_name());
        
        return $content;
    }
    
    /**
     * Translate widget fields
     * 
     * @param string $content Original content
     * @param array $settings Widget settings
     * @param string $widget_name Widget name
     * @return string Translated content
     */
    private function translate_widget_fields($content, $settings, $widget_name) {
        $detector = AMST_Language_Detector::get_instance();
        $current_lang = $detector->get_current_language();
        
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        
        // Define translatable fields by widget type
        $translatable_fields = $this->get_translatable_fields($widget_name);
        
        foreach ($translatable_fields as $field) {
            if (isset($settings[$field]) && !empty($settings[$field])) {
                $original = $settings[$field];
                $translated = $translation_engine->translate($original, $default_lang, $current_lang);
                $content = str_replace($original, $translated, $content);
            }
        }
        
        return $content;
    }
    
    /**
     * Get translatable fields for widget
     * 
     * @param string $widget_name Widget name
     * @return array Translatable fields
     */
    private function get_translatable_fields($widget_name) {
        $fields = [
            'heading' => ['title', 'description'],
            'text-editor' => ['editor'],
            'button' => ['text', 'title'],
            'image-box' => ['title_text', 'description_text'],
            'icon-box' => ['title_text', 'description_text'],
            'testimonial' => ['testimonial_content', 'testimonial_name', 'testimonial_job'],
            'tabs' => ['tab_title', 'tab_content'],
            'accordion' => ['tab_title', 'tab_content'],
            'toggle' => ['tab_title', 'tab_content']
        ];
        
        return isset($fields[$widget_name]) ? $fields[$widget_name] : ['title', 'description', 'text', 'content'];
    }
    
    /**
     * Add translation controls to Elementor
     * 
     * @param object $element Element instance
     * @param string $section_id Section ID
     * @param array $args Arguments
     */
    public function add_translation_controls($element, $section_id, $args) {
        if ('section_advanced' !== $section_id) {
            return;
        }
        
        $element->start_controls_section(
            'amst_translation_section',
            [
                'label' => __('Translation Settings', 'auto-multilingual-seo-translator'),
                'tab' => \Elementor\Controls_Manager::TAB_ADVANCED
            ]
        );
        
        $element->add_control(
            'amst_disable_translation',
            [
                'label' => __('Disable Translation', 'auto-multilingual-seo-translator'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'auto-multilingual-seo-translator'),
                'label_off' => __('No', 'auto-multilingual-seo-translator'),
                'return_value' => 'yes',
                'default' => ''
            ]
        );
        
        $element->end_controls_section();
    }
    
    /**
     * Register Elementor widgets
     */
    public function register_elementor_widgets() {
        // Register language switcher widget for Elementor
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new AMST_Elementor_Language_Switcher_Widget()
        );
    }
}

/**
 * Elementor Language Switcher Widget
 */
class AMST_Elementor_Language_Switcher_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'amst_language_switcher';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Language Switcher', 'auto-multilingual-seo-translator');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-globe';
    }
    
    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['general'];
    }
    
    /**
     * Register widget controls
     */
    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Settings', 'auto-multilingual-seo-translator'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT
            ]
        );
        
        $this->add_control(
            'style',
            [
                'label' => __('Style', 'auto-multilingual-seo-translator'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'dropdown',
                'options' => [
                    'dropdown' => __('Dropdown', 'auto-multilingual-seo-translator'),
                    'list' => __('List', 'auto-multilingual-seo-translator')
                ]
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        echo do_shortcode('[language_switcher style="' . esc_attr($settings['style']) . '"]');
    }
}
