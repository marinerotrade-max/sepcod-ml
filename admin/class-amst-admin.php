<?php
/**
 * Admin interface handler.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AMST_Admin class.
 */
class AMST_Admin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_amst_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'wp_ajax_amst_test_api', array( $this, 'ajax_test_api' ) );
    }
    
    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Multilingual SEO', 'auto-multilingual-seo' ),
            __( 'Multilingual SEO', 'auto-multilingual-seo' ),
            'manage_options',
            'amst-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-translation',
            30
        );
        
        add_submenu_page(
            'amst-settings',
            __( 'Settings', 'auto-multilingual-seo' ),
            __( 'Settings', 'auto-multilingual-seo' ),
            'manage_options',
            'amst-settings',
            array( $this, 'render_settings_page' )
        );
        
        add_submenu_page(
            'amst-settings',
            __( 'Languages', 'auto-multilingual-seo' ),
            __( 'Languages', 'auto-multilingual-seo' ),
            'manage_options',
            'amst-languages',
            array( $this, 'render_languages_page' )
        );
        
        add_submenu_page(
            'amst-settings',
            __( 'Language Switcher', 'auto-multilingual-seo' ),
            __( 'Language Switcher', 'auto-multilingual-seo' ),
            'manage_options',
            'amst-switcher-settings',
            array( $this, 'render_switcher_settings_page' )
        );
        
        add_submenu_page(
            'amst-settings',
            __( 'Statistics', 'auto-multilingual-seo' ),
            __( 'Statistics', 'auto-multilingual-seo' ),
            'manage_options',
            'amst-statistics',
            array( $this, 'render_statistics_page' )
        );
    }
    
    /**
     * Register settings.
     */
    public function register_settings() {
        // General settings.
        register_setting( 'amst_general_settings', 'amst_api_key' );
        register_setting( 'amst_general_settings', 'amst_default_language' );
        
        // Content settings.
        register_setting( 'amst_content_settings', 'amst_translate_pages' );
        register_setting( 'amst_content_settings', 'amst_translate_posts' );
        register_setting( 'amst_content_settings', 'amst_translate_custom_post_types' );
        
        // SEO settings.
        register_setting( 'amst_seo_settings', 'amst_enable_hreflang' );
        register_setting( 'amst_seo_settings', 'amst_enable_canonical' );
        register_setting( 'amst_seo_settings', 'amst_enable_sitemap' );
        
        // Cache settings.
        register_setting( 'amst_cache_settings', 'amst_enable_cache' );
        register_setting( 'amst_cache_settings', 'amst_cache_expiry' );
        
        // Language settings.
        register_setting( 'amst_language_settings', 'amst_enabled_languages', array(
            'type' => 'array',
            'sanitize_callback' => array( $this, 'sanitize_languages' ),
        ) );
    }
    
    /**
     * Sanitize languages array.
     *
     * @param array $value Languages array.
     * @return array Sanitized languages.
     */
    public function sanitize_languages( $value ) {
        if ( ! is_array( $value ) ) {
            return array( 'en' );
        }
        
        // Ensure default language is included.
        $default_lang = get_option( 'amst_default_language', 'en' );
        if ( ! in_array( $default_lang, $value, true ) ) {
            $value[] = $default_lang;
        }
        
        return array_map( 'sanitize_text_field', $value );
    }
    
    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 0 !== strpos( $hook, 'toplevel_page_amst' ) && 0 !== strpos( $hook, 'multilingual-seo_page_amst' ) ) {
            return;
        }
        
        wp_enqueue_style(
            'amst-admin',
            AMST_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            AMST_VERSION
        );
        
        wp_enqueue_script(
            'amst-admin',
            AMST_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            AMST_VERSION,
            true
        );
        
        wp_localize_script(
            'amst-admin',
            'amstAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'amst_admin' ),
                'strings' => array(
                    'clearingCache' => __( 'Clearing cache...', 'auto-multilingual-seo' ),
                    'cacheCleared' => __( 'Cache cleared successfully!', 'auto-multilingual-seo' ),
                    'testingApi' => __( 'Testing API...', 'auto-multilingual-seo' ),
                    'apiSuccess' => __( 'API connection successful!', 'auto-multilingual-seo' ),
                    'apiError' => __( 'API connection failed', 'auto-multilingual-seo' ),
                ),
            )
        );
    }
    
    /**
     * Render settings page.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        if ( isset( $_POST['amst_save_settings'] ) && check_admin_referer( 'amst_settings' ) ) {
            $this->save_general_settings();
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully!', 'auto-multilingual-seo' ) . '</p></div>';
        }
        
        include AMST_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Render languages page.
     */
    public function render_languages_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        if ( isset( $_POST['amst_save_languages'] ) && check_admin_referer( 'amst_languages' ) ) {
            $this->save_language_settings();
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Languages saved successfully!', 'auto-multilingual-seo' ) . '</p></div>';
        }
        
        include AMST_PLUGIN_DIR . 'admin/views/languages.php';
    }
    
    /**
     * Render switcher settings page.
     */
    public function render_switcher_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        include AMST_PLUGIN_DIR . 'admin/views/switcher-settings.php';
    }
    
    /**
     * Render statistics page.
     */
    public function render_statistics_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        include AMST_PLUGIN_DIR . 'admin/views/statistics.php';
    }
    
    /**
     * Save general settings.
     */
    private function save_general_settings() {
        if ( isset( $_POST['amst_api_key'] ) ) {
            update_option( 'amst_api_key', sanitize_text_field( wp_unslash( $_POST['amst_api_key'] ) ) );
        }
        
        if ( isset( $_POST['amst_default_language'] ) ) {
            update_option( 'amst_default_language', sanitize_text_field( wp_unslash( $_POST['amst_default_language'] ) ) );
        }
        
        update_option( 'amst_translate_pages', isset( $_POST['amst_translate_pages'] ) );
        update_option( 'amst_translate_posts', isset( $_POST['amst_translate_posts'] ) );
        update_option( 'amst_translate_custom_post_types', isset( $_POST['amst_translate_custom_post_types'] ) );
        
        update_option( 'amst_enable_hreflang', isset( $_POST['amst_enable_hreflang'] ) );
        update_option( 'amst_enable_canonical', isset( $_POST['amst_enable_canonical'] ) );
        update_option( 'amst_enable_sitemap', isset( $_POST['amst_enable_sitemap'] ) );
        
        update_option( 'amst_enable_cache', isset( $_POST['amst_enable_cache'] ) );
        
        if ( isset( $_POST['amst_cache_expiry'] ) ) {
            update_option( 'amst_cache_expiry', absint( $_POST['amst_cache_expiry'] ) );
        }
    }
    
    /**
     * Save language settings.
     */
    private function save_language_settings() {
        if ( isset( $_POST['amst_enabled_languages'] ) && is_array( $_POST['amst_enabled_languages'] ) ) {
            $languages = array_map( 'sanitize_text_field', wp_unslash( $_POST['amst_enabled_languages'] ) );
            update_option( 'amst_enabled_languages', $languages );
        }
    }
    
    /**
     * AJAX: Clear cache.
     */
    public function ajax_clear_cache() {
        check_ajax_referer( 'amst_admin', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'auto-multilingual-seo' ) ) );
        }
        
        $cache = amst()->cache;
        $result = $cache->clear_cache();
        
        if ( $result ) {
            do_action( 'amst_translations_cleared' );
            wp_send_json_success( array( 'message' => __( 'Cache cleared successfully', 'auto-multilingual-seo' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to clear cache', 'auto-multilingual-seo' ) ) );
        }
    }
    
    /**
     * AJAX: Test API connection.
     */
    public function ajax_test_api() {
        check_ajax_referer( 'amst_admin', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'auto-multilingual-seo' ) ) );
        }
        
        $translator = amst()->translator;
        $result = $translator->translate( 'Hello World', 'es', 'en' );
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        } else {
            wp_send_json_success( array(
                'message' => __( 'API connection successful', 'auto-multilingual-seo' ),
                'translation' => $result,
            ) );
        }
    }
}
