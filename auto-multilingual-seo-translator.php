<?php
/**
 * Plugin Name: Auto Multilingual SEO Translator
 * Plugin URI: https://github.com/marinerotrade-max/sepcod-ml
 * Description: Automatically translate all website content into multiple languages using Google Cloud Translation API with server-side rendering for SEO.
 * Version: 1.3.4
 * Author: Majed Nefzi
 * Author URI: https://github.com/marinerotrade-max
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: auto-multilingual-seo
 * Domain Path: /languages
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'AMST_VERSION', '1.3.4' );
define( 'AMST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AMST_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require core files.
require_once AMST_PLUGIN_DIR . 'includes/class-amst-database.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-translator.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-language-detector.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-cache.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-rewrite.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-seo.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-sitemap.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-integrations.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-content-processor.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-shortcodes.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-manual-translations.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-comprehensive-filters.php';
require_once AMST_PLUGIN_DIR . 'includes/class-amst-language-switcher.php';
require_once AMST_PLUGIN_DIR . 'admin/class-amst-admin.php';

/**
 * Main plugin class.
 */
class Auto_Multilingual_SEO_Translator {
    
    /**
     * Single instance of the class.
     *
     * @var Auto_Multilingual_SEO_Translator
     */
    private static $instance = null;
    
    /**
     * Database handler.
     *
     * @var AMST_Database
     */
    public $database;
    
    /**
     * Translator handler.
     *
     * @var AMST_Translator
     */
    public $translator;
    
    /**
     * Language detector.
     *
     * @var AMST_Language_Detector
     */
    public $language_detector;
    
    /**
     * Cache handler.
     *
     * @var AMST_Cache
     */
    public $cache;
    
    /**
     * SEO handler.
     *
     * @var AMST_SEO
     */
    public $seo;
    
    /**
     * Sitemap handler.
     *
     * @var AMST_Sitemap
     */
    public $sitemap;
    
    /**
     * Integrations handler.
     *
     * @var AMST_Integrations
     */
    public $integrations;
    
    /**
     * Content processor.
     *
     * @var AMST_Content_Processor
     */
    public $content_processor;
    
    /**
     * Admin handler.
     *
     * @var AMST_Admin
     */
    public $admin;
    
    /**
     * Rewrite handler.
     *
     * @var AMST_Rewrite
     */
    public $rewrite;
    
    /**
     * Get single instance.
     *
     * @return Auto_Multilingual_SEO_Translator
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor.
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }
    
    /**
     * Manual translations handler.
     *
     * @var AMST_Manual_Translations
     */
    public $manual_translations;
    
    /**
     * Comprehensive filters handler.
     *
     * @var AMST_Comprehensive_Filters
     */
    public $comprehensive_filters;
    
    /**
     * Language switcher handler.
     *
     * @var AMST_Language_Switcher
     */
    public $language_switcher;
    
    /**
     * Initialize components.
     */
    private function init_components() {
        $this->database = new AMST_Database();
        $this->cache = new AMST_Cache();
        $this->translator = new AMST_Translator( $this->cache );
        $this->language_detector = new AMST_Language_Detector();
        $this->rewrite = new AMST_Rewrite( $this->language_detector );
        $this->seo = new AMST_SEO( $this->language_detector );
        $this->sitemap = new AMST_Sitemap( $this->language_detector );
        $this->integrations = new AMST_Integrations();
        $this->content_processor = new AMST_Content_Processor( $this->translator, $this->language_detector, $this->cache );
        $this->manual_translations = new AMST_Manual_Translations( $this->cache, $this->database );
        $this->comprehensive_filters = new AMST_Comprehensive_Filters( $this->language_detector, $this->content_processor );
        $this->language_switcher = new AMST_Language_Switcher();
        
        if ( is_admin() ) {
            $this->admin = new AMST_Admin();
        }
    }
    
    /**
     * Plugin activation.
     */
    public function activate() {
        $this->database->create_tables();
        $this->set_default_options();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation.
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Set default options.
     */
    private function set_default_options() {
        $defaults = array(
            'amst_api_key' => '',
            'amst_default_language' => 'en',
            'amst_enabled_languages' => array( 'en' ),
            'amst_enable_cache' => true,
            'amst_cache_expiry' => 2592000, // 30 days
            'amst_translate_pages' => true,
            'amst_translate_posts' => true,
            'amst_translate_custom_post_types' => true,
            'amst_enable_hreflang' => true,
            'amst_enable_canonical' => true,
            'amst_enable_sitemap' => true,
            'amst_switcher_style' => 'modal',
            'amst_switcher_show_names' => 'no',
            'amst_switcher_flag_size' => 'medium',
            'amst_switcher_position' => 'inline',
            'amst_switcher_modal_title' => __( 'Select Language', 'auto-multilingual-seo' ),
        );
        
        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }
    
    /**
     * Load plugin textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'auto-multilingual-seo',
            false,
            dirname( AMST_PLUGIN_BASENAME ) . '/languages'
        );
    }
}

/**
 * Get plugin instance.
 *
 * @return Auto_Multilingual_SEO_Translator
 */
function amst() {
    return Auto_Multilingual_SEO_Translator::instance();
}

// Initialize the plugin.
amst();
