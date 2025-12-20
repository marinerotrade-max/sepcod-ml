<?php
/**
 * Admin languages page template.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$translator = amst()->translator;
$supported_languages = $translator->get_supported_languages();
$enabled_languages = get_option( 'amst_enabled_languages', array( 'en' ) );
$default_language = get_option( 'amst_default_language', 'en' );
?>

<div class="wrap amst-languages-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="amst-languages-container">
        <form method="post" action="">
            <?php wp_nonce_field( 'amst_languages' ); ?>
            
            <div class="amst-card">
                <h2><?php esc_html_e( 'Enabled Languages', 'auto-multilingual-seo' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Select the languages you want to enable for your website. Translations will be available for these languages.', 'auto-multilingual-seo' ); ?>
                </p>
                
                <div class="amst-language-grid">
                    <?php foreach ( $supported_languages as $code => $name ) : ?>
                        <label class="amst-language-item <?php echo in_array( $code, $enabled_languages, true ) ? 'active' : ''; ?>">
                            <input type="checkbox" 
                                   name="amst_enabled_languages[]" 
                                   value="<?php echo esc_attr( $code ); ?>"
                                   <?php checked( in_array( $code, $enabled_languages, true ) ); ?>
                                   <?php disabled( $code === $default_language ); ?> />
                            <span class="language-flag"><?php echo esc_html( strtoupper( $code ) ); ?></span>
                            <span class="language-name"><?php echo esc_html( $name ); ?></span>
                            <?php if ( $code === $default_language ) : ?>
                                <span class="language-badge"><?php esc_html_e( 'Default', 'auto-multilingual-seo' ); ?></span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <p class="description">
                    <?php
                    printf(
                        /* translators: %s: default language name */
                        esc_html__( 'Note: The default language (%s) is always enabled and cannot be disabled.', 'auto-multilingual-seo' ),
                        esc_html( $supported_languages[ $default_language ] )
                    );
                    ?>
                </p>
            </div>
            
            <div class="amst-card">
                <h2><?php esc_html_e( 'Language Switcher', 'auto-multilingual-seo' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Use the following shortcode to display a language switcher on your site:', 'auto-multilingual-seo' ); ?>
                </p>
                
                <code>[amst_language_switcher]</code>
                
                <p class="description">
                    <?php esc_html_e( 'Or use this PHP code in your theme:', 'auto-multilingual-seo' ); ?>
                </p>
                
                <code>&lt;?php echo amst()-&gt;language_detector-&gt;get_language_switcher(); ?&gt;</code>
            </div>
            
            <p class="submit">
                <input type="submit" 
                       name="amst_save_languages" 
                       class="button button-primary" 
                       value="<?php esc_attr_e( 'Save Languages', 'auto-multilingual-seo' ); ?>" />
            </p>
        </form>
    </div>
</div>
