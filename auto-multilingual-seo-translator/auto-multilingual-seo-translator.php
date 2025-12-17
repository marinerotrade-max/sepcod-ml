<?php
/**
 * Plugin Name: Auto Multilingual SEO Translator
 * Plugin URI: https://github.com/marinerotrade-max/sepcod-ml
 * Description: Automatically translate website content into multiple languages with Google Translate API, featuring language detection, caching, and SEO enhancements. Compatible with Elementor, Directorist, LiteSpeed Cache, Rank Math, and Wordfence.
 * Version: 1.0.0
 * Author: Marine Ro Trade Max
 * Author URI: https://github.com/marinerotrade-max
 * Text Domain: auto-multilingual-seo-translator
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('AMST_VERSION', '1.0.0');
define('AMST_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AMST_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AMST_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Auto_Multilingual_SEO_Translator {
    
    /**
     * Single instance of the class
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
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize hooks
        add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
        add_action('init', [$this, 'init_plugin']);
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_init', [$this, 'register_settings']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('wp_head', [$this, 'add_hreflang_tags']);
        
        // Translation hooks
        add_filter('the_content', [$this, 'translate_content'], 999);
        add_filter('the_title', [$this, 'translate_title'], 999, 2);
        add_filter('widget_text', [$this, 'translate_widget_text'], 999);
        
        // Register shortcodes
        add_shortcode('language_switcher', [$this, 'language_switcher_shortcode']);
        
        // Register widget
        add_action('widgets_init', [$this, 'register_widgets']);
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once AMST_PLUGIN_DIR . 'includes/class-translation-engine.php';
        require_once AMST_PLUGIN_DIR . 'includes/class-cache-manager.php';
        require_once AMST_PLUGIN_DIR . 'includes/class-language-detector.php';
        require_once AMST_PLUGIN_DIR . 'includes/class-seo-manager.php';
        require_once AMST_PLUGIN_DIR . 'includes/class-compatibility-manager.php';
        require_once AMST_PLUGIN_DIR . 'includes/class-elementor-integration.php';
        require_once AMST_PLUGIN_DIR . 'includes/class-directorist-integration.php';
        require_once AMST_PLUGIN_DIR . 'includes/class-language-switcher-widget.php';
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'auto-multilingual-seo-translator',
            false,
            dirname(AMST_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Initialize plugin components
     */
    public function init_plugin() {
        // Initialize translation engine
        AMST_Translation_Engine::get_instance();
        
        // Initialize cache manager
        AMST_Cache_Manager::get_instance();
        
        // Initialize language detector
        AMST_Language_Detector::get_instance();
        
        // Initialize SEO manager
        AMST_SEO_Manager::get_instance();
        
        // Initialize compatibility manager
        AMST_Compatibility_Manager::get_instance();
        
        // Initialize integrations
        if (defined('ELEMENTOR_VERSION')) {
            AMST_Elementor_Integration::get_instance();
        }
        
        if (class_exists('Directorist_Base')) {
            AMST_Directorist_Integration::get_instance();
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Auto Multilingual SEO Translator', 'auto-multilingual-seo-translator'),
            __('Multilingual SEO', 'auto-multilingual-seo-translator'),
            'manage_options',
            'amst-settings',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('amst_settings', 'amst_options');
        
        // General settings
        add_settings_section(
            'amst_general_section',
            __('General Settings', 'auto-multilingual-seo-translator'),
            null,
            'amst-settings'
        );
        
        add_settings_field(
            'amst_google_api_key',
            __('Google Translate API Key', 'auto-multilingual-seo-translator'),
            [$this, 'render_api_key_field'],
            'amst-settings',
            'amst_general_section'
        );
        
        add_settings_field(
            'amst_default_language',
            __('Default Language', 'auto-multilingual-seo-translator'),
            [$this, 'render_default_language_field'],
            'amst-settings',
            'amst_general_section'
        );
        
        add_settings_field(
            'amst_enabled_languages',
            __('Enabled Languages', 'auto-multilingual-seo-translator'),
            [$this, 'render_enabled_languages_field'],
            'amst-settings',
            'amst_general_section'
        );
        
        add_settings_field(
            'amst_enable_cache',
            __('Enable Caching', 'auto-multilingual-seo-translator'),
            [$this, 'render_enable_cache_field'],
            'amst-settings',
            'amst_general_section'
        );
        
        add_settings_field(
            'amst_enable_auto_detect',
            __('Enable Auto Language Detection', 'auto-multilingual-seo-translator'),
            [$this, 'render_enable_auto_detect_field'],
            'amst-settings',
            'amst_general_section'
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('amst_settings');
                do_settings_sections('amst-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $options = get_option('amst_options');
        $value = isset($options['api_key']) ? $options['api_key'] : '';
        ?>
        <input type="text" name="amst_options[api_key]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <p class="description"><?php _e('Enter your Google Translate API key', 'auto-multilingual-seo-translator'); ?></p>
        <?php
    }
    
    /**
     * Render default language field
     */
    public function render_default_language_field() {
        $options = get_option('amst_options');
        $value = isset($options['default_language']) ? $options['default_language'] : 'en';
        $languages = $this->get_supported_languages();
        ?>
        <select name="amst_options[default_language]">
            <?php foreach ($languages as $code => $name): ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($value, $code); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    /**
     * Render enabled languages field
     */
    public function render_enabled_languages_field() {
        $options = get_option('amst_options');
        $enabled = isset($options['enabled_languages']) ? $options['enabled_languages'] : ['en', 'es', 'fr', 'de'];
        $languages = $this->get_supported_languages();
        ?>
        <fieldset>
            <?php foreach ($languages as $code => $name): ?>
                <label>
                    <input type="checkbox" name="amst_options[enabled_languages][]" value="<?php echo esc_attr($code); ?>" 
                        <?php checked(in_array($code, $enabled)); ?> />
                    <?php echo esc_html($name); ?>
                </label><br />
            <?php endforeach; ?>
        </fieldset>
        <?php
    }
    
    /**
     * Render enable cache field
     */
    public function render_enable_cache_field() {
        $options = get_option('amst_options');
        $value = isset($options['enable_cache']) ? $options['enable_cache'] : 1;
        ?>
        <label>
            <input type="checkbox" name="amst_options[enable_cache]" value="1" <?php checked($value, 1); ?> />
            <?php _e('Enable translation caching to improve performance', 'auto-multilingual-seo-translator'); ?>
        </label>
        <?php
    }
    
    /**
     * Render enable auto detect field
     */
    public function render_enable_auto_detect_field() {
        $options = get_option('amst_options');
        $value = isset($options['enable_auto_detect']) ? $options['enable_auto_detect'] : 1;
        ?>
        <label>
            <input type="checkbox" name="amst_options[enable_auto_detect]" value="1" <?php checked($value, 1); ?> />
            <?php _e('Automatically detect user language based on browser settings', 'auto-multilingual-seo-translator'); ?>
        </label>
        <?php
    }
    
    /**
     * Get supported languages
     */
    private function get_supported_languages() {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese (Simplified)',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'ar' => 'Arabic',
            'hi' => 'Hindi',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'tr' => 'Turkish',
            'sv' => 'Swedish',
            'da' => 'Danish',
            'fi' => 'Finnish',
            'no' => 'Norwegian',
            'cs' => 'Czech',
            'ro' => 'Romanian',
            'hu' => 'Hungarian',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'th' => 'Thai',
            'vi' => 'Vietnamese',
            'id' => 'Indonesian',
            'uk' => 'Ukrainian',
            'bg' => 'Bulgarian',
            'hr' => 'Croatian'
        ];
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_amst-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'amst-admin-style',
            AMST_PLUGIN_URL . 'assets/css/admin-style.css',
            [],
            AMST_VERSION
        );
        
        wp_enqueue_script(
            'amst-admin-script',
            AMST_PLUGIN_URL . 'assets/js/admin-script.js',
            ['jquery'],
            AMST_VERSION,
            true
        );
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'amst-frontend-style',
            AMST_PLUGIN_URL . 'assets/css/frontend-style.css',
            [],
            AMST_VERSION
        );
        
        wp_enqueue_script(
            'amst-frontend-script',
            AMST_PLUGIN_URL . 'assets/js/frontend-script.js',
            ['jquery'],
            AMST_VERSION,
            true
        );
        
        wp_localize_script('amst-frontend-script', 'amstData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('amst_nonce'),
            'currentLanguage' => $this->get_current_language()
        ]);
    }
    
    /**
     * Get current language
     */
    private function get_current_language() {
        if (isset($_COOKIE['amst_language'])) {
            return sanitize_text_field($_COOKIE['amst_language']);
        }
        
        $options = get_option('amst_options');
        return isset($options['default_language']) ? $options['default_language'] : 'en';
    }
    
    /**
     * Add hreflang tags for SEO
     */
    public function add_hreflang_tags() {
        $seo_manager = AMST_SEO_Manager::get_instance();
        $seo_manager->add_hreflang_tags();
    }
    
    /**
     * Translate content
     */
    public function translate_content($content) {
        $target_lang = $this->get_current_language();
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($target_lang === $default_lang) {
            return $content;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($content, $default_lang, $target_lang);
    }
    
    /**
     * Translate title
     */
    public function translate_title($title, $post_id = null) {
        if (!$post_id || empty($title)) {
            return $title;
        }
        
        $target_lang = $this->get_current_language();
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($target_lang === $default_lang) {
            return $title;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($title, $default_lang, $target_lang);
    }
    
    /**
     * Translate widget text
     */
    public function translate_widget_text($text) {
        $target_lang = $this->get_current_language();
        $options = get_option('amst_options');
        $default_lang = isset($options['default_language']) ? $options['default_language'] : 'en';
        
        if ($target_lang === $default_lang) {
            return $text;
        }
        
        $translation_engine = AMST_Translation_Engine::get_instance();
        return $translation_engine->translate($text, $default_lang, $target_lang);
    }
    
    /**
     * Language switcher shortcode
     */
    public function language_switcher_shortcode($atts) {
        $atts = shortcode_atts([
            'style' => 'dropdown'
        ], $atts);
        
        ob_start();
        $this->render_language_switcher($atts['style']);
        return ob_get_clean();
    }
    
    /**
     * Render language switcher
     */
    private function render_language_switcher($style = 'dropdown') {
        $options = get_option('amst_options');
        $enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : ['en'];
        $current_language = $this->get_current_language();
        $languages = $this->get_supported_languages();
        
        if ($style === 'dropdown') {
            ?>
            <div class="amst-language-switcher amst-dropdown">
                <select id="amst-language-selector" class="amst-language-select">
                    <?php foreach ($enabled_languages as $code): ?>
                        <?php if (isset($languages[$code])): ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($current_language, $code); ?>>
                                <?php echo esc_html($languages[$code]); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php
        } else {
            ?>
            <div class="amst-language-switcher amst-list">
                <ul class="amst-language-list">
                    <?php foreach ($enabled_languages as $code): ?>
                        <?php if (isset($languages[$code])): ?>
                            <li class="amst-language-item <?php echo ($current_language === $code) ? 'active' : ''; ?>">
                                <a href="#" data-language="<?php echo esc_attr($code); ?>" class="amst-language-link">
                                    <?php echo esc_html($languages[$code]); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }
    }
    
    /**
     * Register widgets
     */
    public function register_widgets() {
        register_widget('AMST_Language_Switcher_Widget');
    }
}

/**
 * Initialize plugin
 */
function amst_init() {
    return Auto_Multilingual_SEO_Translator::get_instance();
}

// Initialize the plugin
add_action('plugins_loaded', 'amst_init');

/**
 * Activation hook
 */
function amst_activate() {
    // Set default options
    $default_options = [
        'api_key' => '',
        'default_language' => 'en',
        'enabled_languages' => ['en', 'es', 'fr', 'de'],
        'enable_cache' => 1,
        'enable_auto_detect' => 1
    ];
    
    add_option('amst_options', $default_options);
    
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'amst_activate');

/**
 * Deactivation hook
 */
function amst_deactivate() {
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'amst_deactivate');
