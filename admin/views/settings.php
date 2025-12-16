<?php
/**
 * Admin settings page template.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$api_key = get_option( 'amst_api_key', '' );
$default_language = get_option( 'amst_default_language', 'en' );
$translate_pages = get_option( 'amst_translate_pages', true );
$translate_posts = get_option( 'amst_translate_posts', true );
$translate_custom_post_types = get_option( 'amst_translate_custom_post_types', true );
$enable_hreflang = get_option( 'amst_enable_hreflang', true );
$enable_canonical = get_option( 'amst_enable_canonical', true );
$enable_sitemap = get_option( 'amst_enable_sitemap', true );
$enable_cache = get_option( 'amst_enable_cache', true );
$cache_expiry = get_option( 'amst_cache_expiry', 2592000 );

$translator = amst()->translator;
$supported_languages = $translator->get_supported_languages();
?>

<div class="wrap amst-settings-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="amst-settings-container">
        <form method="post" action="">
            <?php wp_nonce_field( 'amst_settings' ); ?>
            
            <div class="amst-card">
                <h2><?php esc_html_e( 'API Configuration', 'auto-multilingual-seo' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Configure your Google Cloud Translation API credentials.', 'auto-multilingual-seo' ); ?>
                    <a href="https://console.cloud.google.com/apis/library/translate.googleapis.com" target="_blank">
                        <?php esc_html_e( 'Get API Key', 'auto-multilingual-seo' ); ?>
                    </a>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="amst_api_key"><?php esc_html_e( 'API Key', 'auto-multilingual-seo' ); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="amst_api_key" 
                                   name="amst_api_key" 
                                   value="<?php echo esc_attr( $api_key ); ?>" 
                                   class="regular-text" 
                                   placeholder="AIzaSy..." />
                            <p class="description">
                                <?php esc_html_e( 'Enter your Google Cloud Translation API key.', 'auto-multilingual-seo' ); ?>
                            </p>
                            <button type="button" class="button" id="amst-test-api">
                                <?php esc_html_e( 'Test API Connection', 'auto-multilingual-seo' ); ?>
                            </button>
                            <span class="amst-api-status"></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="amst_default_language"><?php esc_html_e( 'Default Language', 'auto-multilingual-seo' ); ?></label>
                        </th>
                        <td>
                            <select id="amst_default_language" name="amst_default_language">
                                <?php foreach ( $supported_languages as $code => $name ) : ?>
                                    <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $default_language, $code ); ?>>
                                        <?php echo esc_html( $name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e( 'The default language of your website content.', 'auto-multilingual-seo' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="amst-card">
                <h2><?php esc_html_e( 'Content Translation', 'auto-multilingual-seo' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Configure which content types should be translated.', 'auto-multilingual-seo' ); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Translation For', 'auto-multilingual-seo' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           name="amst_translate_pages" 
                                           value="1" 
                                           <?php checked( $translate_pages ); ?> />
                                    <?php esc_html_e( 'Pages', 'auto-multilingual-seo' ); ?>
                                </label>
                                <br />
                                <label>
                                    <input type="checkbox" 
                                           name="amst_translate_posts" 
                                           value="1" 
                                           <?php checked( $translate_posts ); ?> />
                                    <?php esc_html_e( 'Posts', 'auto-multilingual-seo' ); ?>
                                </label>
                                <br />
                                <label>
                                    <input type="checkbox" 
                                           name="amst_translate_custom_post_types" 
                                           value="1" 
                                           <?php checked( $translate_custom_post_types ); ?> />
                                    <?php esc_html_e( 'Custom Post Types', 'auto-multilingual-seo' ); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="amst-card">
                <h2><?php esc_html_e( 'SEO Settings', 'auto-multilingual-seo' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Configure SEO features for multilingual content.', 'auto-multilingual-seo' ); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'SEO Features', 'auto-multilingual-seo' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" 
                                           name="amst_enable_hreflang" 
                                           value="1" 
                                           <?php checked( $enable_hreflang ); ?> />
                                    <?php esc_html_e( 'Enable Hreflang Tags', 'auto-multilingual-seo' ); ?>
                                </label>
                                <br />
                                <label>
                                    <input type="checkbox" 
                                           name="amst_enable_canonical" 
                                           value="1" 
                                           <?php checked( $enable_canonical ); ?> />
                                    <?php esc_html_e( 'Enable Canonical Tags', 'auto-multilingual-seo' ); ?>
                                </label>
                                <br />
                                <label>
                                    <input type="checkbox" 
                                           name="amst_enable_sitemap" 
                                           value="1" 
                                           <?php checked( $enable_sitemap ); ?> />
                                    <?php esc_html_e( 'Enable Multilingual Sitemap', 'auto-multilingual-seo' ); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="amst-card">
                <h2><?php esc_html_e( 'Cache Settings', 'auto-multilingual-seo' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Configure translation caching to improve performance.', 'auto-multilingual-seo' ); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="amst_enable_cache"><?php esc_html_e( 'Enable Cache', 'auto-multilingual-seo' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="amst_enable_cache" 
                                   name="amst_enable_cache" 
                                   value="1" 
                                   <?php checked( $enable_cache ); ?> />
                            <p class="description">
                                <?php esc_html_e( 'Cache translations to reduce API calls and improve performance.', 'auto-multilingual-seo' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="amst_cache_expiry"><?php esc_html_e( 'Cache Expiry', 'auto-multilingual-seo' ); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="amst_cache_expiry" 
                                   name="amst_cache_expiry" 
                                   value="<?php echo esc_attr( $cache_expiry ); ?>" 
                                   min="3600" 
                                   step="1" 
                                   class="small-text" />
                            <span><?php esc_html_e( 'seconds', 'auto-multilingual-seo' ); ?></span>
                            <p class="description">
                                <?php esc_html_e( 'How long to keep translations in cache (default: 2592000 = 30 days).', 'auto-multilingual-seo' ); ?>
                            </p>
                            <button type="button" class="button" id="amst-clear-cache">
                                <?php esc_html_e( 'Clear Cache Now', 'auto-multilingual-seo' ); ?>
                            </button>
                            <span class="amst-cache-status"></span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <p class="submit">
                <input type="submit" 
                       name="amst_save_settings" 
                       class="button button-primary" 
                       value="<?php esc_attr_e( 'Save Settings', 'auto-multilingual-seo' ); ?>" />
            </p>
        </form>
    </div>
</div>
