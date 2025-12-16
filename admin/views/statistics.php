<?php
/**
 * Admin statistics page template.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cache = amst()->cache;
$stats = $cache->get_statistics();
$translator = amst()->translator;
$supported_languages = $translator->get_supported_languages();
?>

<div class="wrap amst-statistics-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="amst-statistics-container">
        <div class="amst-card">
            <h2><?php esc_html_e( 'Translation Statistics', 'auto-multilingual-seo' ); ?></h2>
            
            <div class="amst-stats-grid">
                <div class="amst-stat-box">
                    <div class="stat-value"><?php echo esc_html( number_format_i18n( $stats['total'] ) ); ?></div>
                    <div class="stat-label"><?php esc_html_e( 'Total Translations', 'auto-multilingual-seo' ); ?></div>
                </div>
                
                <div class="amst-stat-box">
                    <div class="stat-value"><?php echo esc_html( number_format_i18n( $stats['total_hits'] ) ); ?></div>
                    <div class="stat-label"><?php esc_html_e( 'Cache Hits', 'auto-multilingual-seo' ); ?></div>
                </div>
                
                <div class="amst-stat-box">
                    <?php
                    $avg_hits = $stats['total'] > 0 ? round( $stats['total_hits'] / $stats['total'], 2 ) : 0;
                    ?>
                    <div class="stat-value"><?php echo esc_html( number_format_i18n( $avg_hits, 2 ) ); ?></div>
                    <div class="stat-label"><?php esc_html_e( 'Average Hits per Translation', 'auto-multilingual-seo' ); ?></div>
                </div>
                
                <div class="amst-stat-box">
                    <div class="stat-value"><?php echo esc_html( count( $stats['by_language'] ) ); ?></div>
                    <div class="stat-label"><?php esc_html_e( 'Active Languages', 'auto-multilingual-seo' ); ?></div>
                </div>
            </div>
        </div>
        
        <div class="amst-card">
            <h2><?php esc_html_e( 'Translations by Language', 'auto-multilingual-seo' ); ?></h2>
            
            <?php if ( ! empty( $stats['by_language'] ) ) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Language', 'auto-multilingual-seo' ); ?></th>
                            <th><?php esc_html_e( 'Language Code', 'auto-multilingual-seo' ); ?></th>
                            <th><?php esc_html_e( 'Translations', 'auto-multilingual-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $stats['by_language'] as $lang_stat ) : ?>
                            <tr>
                                <td>
                                    <?php
                                    $lang_name = isset( $supported_languages[ $lang_stat->target_language ] )
                                        ? $supported_languages[ $lang_stat->target_language ]
                                        : $lang_stat->target_language;
                                    echo esc_html( $lang_name );
                                    ?>
                                </td>
                                <td><?php echo esc_html( $lang_stat->target_language ); ?></td>
                                <td><?php echo esc_html( number_format_i18n( $lang_stat->count ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e( 'No translations yet.', 'auto-multilingual-seo' ); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="amst-card">
            <h2><?php esc_html_e( 'Translations by Content Type', 'auto-multilingual-seo' ); ?></h2>
            
            <?php if ( ! empty( $stats['by_type'] ) ) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Content Type', 'auto-multilingual-seo' ); ?></th>
                            <th><?php esc_html_e( 'Translations', 'auto-multilingual-seo' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $stats['by_type'] as $type_stat ) : ?>
                            <tr>
                                <td><?php echo esc_html( $type_stat->content_type ); ?></td>
                                <td><?php echo esc_html( number_format_i18n( $type_stat->count ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e( 'No translations yet.', 'auto-multilingual-seo' ); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="amst-card">
            <h2><?php esc_html_e( 'Cache Management', 'auto-multilingual-seo' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'Clear the translation cache to force re-translation of content.', 'auto-multilingual-seo' ); ?>
            </p>
            
            <button type="button" class="button button-primary" id="amst-clear-cache">
                <?php esc_html_e( 'Clear All Cache', 'auto-multilingual-seo' ); ?>
            </button>
            <span class="amst-cache-status"></span>
        </div>
    </div>
</div>
