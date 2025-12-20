<?php
/**
 * Translation Status Admin Page
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get database handler
global $wpdb;
$table_name = $wpdb->prefix . 'amst_translations';

// Get enabled languages
$enabled_languages = get_option( 'amst_enabled_languages', array( 'en' ) );
$default_language = get_option( 'amst_default_language', 'en' );

// Get language names
$language_names = array(
	'bg' => 'Bulgarian',
	'hr' => 'Croatian',
	'cs' => 'Czech',
	'da' => 'Danish',
	'nl' => 'Dutch',
	'en' => 'English',
	'et' => 'Estonian',
	'fi' => 'Finnish',
	'fr' => 'French',
	'de' => 'German',
	'el' => 'Greek',
	'hu' => 'Hungarian',
	'ga' => 'Irish',
	'it' => 'Italian',
	'lv' => 'Latvian',
	'lt' => 'Lithuanian',
	'mt' => 'Maltese',
	'pl' => 'Polish',
	'pt' => 'Portuguese',
	'ro' => 'Romanian',
	'sk' => 'Slovak',
	'sl' => 'Slovenian',
	'es' => 'Spanish',
	'sv' => 'Swedish',
);

// Count manual translations by content type
$manual_by_type = $wpdb->get_results(
	"SELECT content_type, COUNT(*) as count 
	FROM {$table_name} 
	WHERE translation_type = 'manual' OR is_manual = 1
	GROUP BY content_type
	ORDER BY count DESC"
);

// Count manual translations by target language
$manual_by_language = $wpdb->get_results(
	"SELECT target_language, COUNT(*) as count 
	FROM {$table_name} 
	WHERE translation_type = 'manual' OR is_manual = 1
	GROUP BY target_language
	ORDER BY count DESC"
);

// Count automatic translations (transients)
$automatic_count = 0;
$automatic_by_language = array();

// Count transients by checking the options table
// Transient key pattern: _transient_amst_auto_*
$transients = $wpdb->get_results(
	"SELECT option_name 
	FROM {$wpdb->prefix}options 
	WHERE option_name LIKE '_transient_amst_auto_%' 
	LIMIT 10000"
);

$automatic_count = count( $transients );

// Try to parse language from transient keys (approximate)
foreach ( $transients as $transient ) {
	// Extract language codes from key if possible
	// Pattern: _transient_amst_auto_{hash}_{source}_{target}
	$key_parts = explode( '_', $transient->option_name );
	if ( count( $key_parts ) >= 3 ) {
		$target_lang = end( $key_parts );
		if ( strlen( $target_lang ) === 2 && array_key_exists( $target_lang, $language_names ) ) {
			if ( ! isset( $automatic_by_language[ $target_lang ] ) ) {
				$automatic_by_language[ $target_lang ] = 0;
			}
			$automatic_by_language[ $target_lang ]++;
		}
	}
}

// Total manual translations
$total_manual = $wpdb->get_var(
	"SELECT COUNT(*) 
	FROM {$table_name} 
	WHERE translation_type = 'manual' OR is_manual = 1"
);

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Translation Status', 'auto-multilingual-seo' ); ?></h1>
	
	<p class="description">
		<?php esc_html_e( 'Overview of stored translations in the system. This page shows translations stored in the database (manual translations) and WordPress transients (automatic translations from pre-translation tool).', 'auto-multilingual-seo' ); ?>
	</p>
	
	<div class="amst-status-container" style="margin-top: 20px;">
		
		<!-- Summary Cards -->
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
			
			<!-- Manual Translations Card -->
			<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin-top: 0; color: #1d2327;">
					<span class="dashicons dashicons-edit" style="color: #2271b1;"></span>
					<?php esc_html_e( 'Manual Translations', 'auto-multilingual-seo' ); ?>
				</h3>
				<p style="font-size: 32px; font-weight: 600; margin: 10px 0; color: #2271b1;">
					<?php echo esc_html( number_format_i18n( $total_manual ) ); ?>
				</p>
				<p style="margin: 0; color: #646970; font-size: 13px;">
					<?php esc_html_e( 'Stored in database (permanent)', 'auto-multilingual-seo' ); ?>
				</p>
			</div>
			
			<!-- Automatic Translations Card -->
			<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin-top: 0; color: #1d2327;">
					<span class="dashicons dashicons-update" style="color: #00a32a;"></span>
					<?php esc_html_e( 'Automatic Translations', 'auto-multilingual-seo' ); ?>
				</h3>
				<p style="font-size: 32px; font-weight: 600; margin: 10px 0; color: #00a32a;">
					<?php echo esc_html( number_format_i18n( $automatic_count ) ); ?>
				</p>
				<p style="margin: 0; color: #646970; font-size: 13px;">
					<?php esc_html_e( 'Stored in transients (30-day expiration)', 'auto-multilingual-seo' ); ?>
				</p>
			</div>
			
			<!-- Total Card -->
			<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3 style="margin-top: 0; color: #1d2327;">
					<span class="dashicons dashicons-translation" style="color: #9b51e0;"></span>
					<?php esc_html_e( 'Total Translations', 'auto-multilingual-seo' ); ?>
				</h3>
				<p style="font-size: 32px; font-weight: 600; margin: 10px 0; color: #9b51e0;">
					<?php echo esc_html( number_format_i18n( $total_manual + $automatic_count ) ); ?>
				</p>
				<p style="margin: 0; color: #646970; font-size: 13px;">
					<?php esc_html_e( 'Combined manual + automatic', 'auto-multilingual-seo' ); ?>
				</p>
			</div>
			
		</div>
		
		<!-- Manual Translations by Content Type -->
		<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-bottom: 20px;">
			<h2 style="margin-top: 0;">
				<?php esc_html_e( 'Manual Translations by Content Type', 'auto-multilingual-seo' ); ?>
			</h2>
			
			<?php if ( ! empty( $manual_by_type ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 40%;"><?php esc_html_e( 'Content Type', 'auto-multilingual-seo' ); ?></th>
							<th style="width: 30%;"><?php esc_html_e( 'Count', 'auto-multilingual-seo' ); ?></th>
							<th style="width: 30%;"><?php esc_html_e( 'Percentage', 'auto-multilingual-seo' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $manual_by_type as $row ) : ?>
							<tr>
								<td><strong><?php echo esc_html( ucfirst( $row->content_type ) ); ?></strong></td>
								<td><?php echo esc_html( number_format_i18n( $row->count ) ); ?></td>
								<td>
									<?php 
									$percentage = $total_manual > 0 ? ( $row->count / $total_manual ) * 100 : 0;
									echo esc_html( number_format( $percentage, 1 ) ); 
									?>%
									<div style="background: #dcdcde; height: 8px; border-radius: 4px; margin-top: 5px;">
										<div style="background: #2271b1; height: 8px; border-radius: 4px; width: <?php echo esc_attr( $percentage ); ?>%;"></div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p style="color: #646970;">
					<?php esc_html_e( 'No manual translations found.', 'auto-multilingual-seo' ); ?>
				</p>
			<?php endif; ?>
		</div>
		
		<!-- Manual Translations by Target Language -->
		<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-bottom: 20px;">
			<h2 style="margin-top: 0;">
				<?php esc_html_e( 'Manual Translations by Target Language', 'auto-multilingual-seo' ); ?>
			</h2>
			
			<?php if ( ! empty( $manual_by_language ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 40%;"><?php esc_html_e( 'Language', 'auto-multilingual-seo' ); ?></th>
							<th style="width: 30%;"><?php esc_html_e( 'Count', 'auto-multilingual-seo' ); ?></th>
							<th style="width: 30%;"><?php esc_html_e( 'Percentage', 'auto-multilingual-seo' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $manual_by_language as $row ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $language_names[ $row->target_language ] ?? $row->target_language ); ?></strong>
									<span style="color: #646970; font-size: 12px;">(<?php echo esc_html( strtoupper( $row->target_language ) ); ?>)</span>
								</td>
								<td><?php echo esc_html( number_format_i18n( $row->count ) ); ?></td>
								<td>
									<?php 
									$percentage = $total_manual > 0 ? ( $row->count / $total_manual ) * 100 : 0;
									echo esc_html( number_format( $percentage, 1 ) ); 
									?>%
									<div style="background: #dcdcde; height: 8px; border-radius: 4px; margin-top: 5px;">
										<div style="background: #2271b1; height: 8px; border-radius: 4px; width: <?php echo esc_attr( $percentage ); ?>%;"></div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p style="color: #646970;">
					<?php esc_html_e( 'No manual translations found.', 'auto-multilingual-seo' ); ?>
				</p>
			<?php endif; ?>
		</div>
		
		<!-- Automatic Translations by Target Language -->
		<?php if ( ! empty( $automatic_by_language ) ) : ?>
		<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-bottom: 20px;">
			<h2 style="margin-top: 0;">
				<?php esc_html_e( 'Automatic Translations by Target Language (Estimated)', 'auto-multilingual-seo' ); ?>
			</h2>
			<p class="description" style="margin-top: 0;">
				<?php esc_html_e( 'Note: Counts are estimated based on transient key patterns. Actual counts may vary.', 'auto-multilingual-seo' ); ?>
			</p>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 40%;"><?php esc_html_e( 'Language', 'auto-multilingual-seo' ); ?></th>
						<th style="width: 30%;"><?php esc_html_e( 'Count (Estimated)', 'auto-multilingual-seo' ); ?></th>
						<th style="width: 30%;"><?php esc_html_e( 'Percentage', 'auto-multilingual-seo' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					arsort( $automatic_by_language );
					foreach ( $automatic_by_language as $lang => $count ) : 
					?>
						<tr>
							<td>
								<strong><?php echo esc_html( $language_names[ $lang ] ?? $lang ); ?></strong>
								<span style="color: #646970; font-size: 12px;">(<?php echo esc_html( strtoupper( $lang ) ); ?>)</span>
							</td>
							<td><?php echo esc_html( number_format_i18n( $count ) ); ?></td>
							<td>
								<?php 
								$percentage = $automatic_count > 0 ? ( $count / $automatic_count ) * 100 : 0;
								echo esc_html( number_format( $percentage, 1 ) ); 
								?>%
								<div style="background: #dcdcde; height: 8px; border-radius: 4px; margin-top: 5px;">
									<div style="background: #00a32a; height: 8px; border-radius: 4px; width: <?php echo esc_attr( $percentage ); ?>%;"></div>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>
		
		<!-- Notes -->
		<div style="background: #f0f6fc; border: 1px solid #c3d7ef; border-radius: 4px; padding: 15px; margin-top: 20px;">
			<h3 style="margin-top: 0;">
				<span class="dashicons dashicons-info" style="color: #0073aa;"></span>
				<?php esc_html_e( 'About Translation Storage', 'auto-multilingual-seo' ); ?>
			</h3>
			<ul style="margin: 10px 0; padding-left: 20px;">
				<li><strong><?php esc_html_e( 'Manual Translations:', 'auto-multilingual-seo' ); ?></strong> <?php esc_html_e( 'Stored permanently in the database. These translations are created via the Manual Translations interface.', 'auto-multilingual-seo' ); ?></li>
				<li><strong><?php esc_html_e( 'Automatic Translations:', 'auto-multilingual-seo' ); ?></strong> <?php esc_html_e( 'Stored in WordPress transients with 30-day expiration. These are generated by the Pre-Translation tool for pages, posts, menus, widgets, and UI strings.', 'auto-multilingual-seo' ); ?></li>
				<li><strong><?php esc_html_e( 'Performance:', 'auto-multilingual-seo' ); ?></strong> <?php esc_html_e( 'Both storage types use hard early exit optimization, ensuring instant retrieval with zero overhead when translations exist.', 'auto-multilingual-seo' ); ?></li>
			</ul>
		</div>
		
	</div>
</div>

<style>
	.amst-status-container .dashicons {
		font-size: 24px;
		width: 24px;
		height: 24px;
		vertical-align: middle;
		margin-right: 5px;
	}
</style>
