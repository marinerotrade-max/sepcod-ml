<?php
/**
 * Language Switcher Settings Page
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Save settings if form submitted.
if ( isset( $_POST['amst_switcher_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['amst_switcher_settings_nonce'] ) ), 'amst_switcher_settings' ) ) {
	update_option( 'amst_switcher_style', sanitize_text_field( wp_unslash( $_POST['amst_switcher_style'] ?? 'modal' ) ) );
	update_option( 'amst_switcher_show_names', sanitize_text_field( wp_unslash( $_POST['amst_switcher_show_names'] ?? 'no' ) ) );
	update_option( 'amst_switcher_flag_size', sanitize_text_field( wp_unslash( $_POST['amst_switcher_flag_size'] ?? 'medium' ) ) );
	update_option( 'amst_switcher_position', sanitize_text_field( wp_unslash( $_POST['amst_switcher_position'] ?? 'inline' ) ) );
	update_option( 'amst_switcher_modal_title', sanitize_text_field( wp_unslash( $_POST['amst_switcher_modal_title'] ?? __( 'Select Language', 'auto-multilingual-seo' ) ) ) );
	
	// v1.5.2: Removed homepage URLs configuration - automatic URL generation only
	
	echo '<div class="notice notice-success"><p>' . esc_html__( 'Language switcher settings saved successfully!', 'auto-multilingual-seo' ) . '</p></div>';
}

// Get current settings.
$switcher_style = get_option( 'amst_switcher_style', 'modal' );
$show_names = get_option( 'amst_switcher_show_names', 'no' );
$flag_size = get_option( 'amst_switcher_flag_size', 'medium' );
$position = get_option( 'amst_switcher_position', 'inline' );
$modal_title = get_option( 'amst_switcher_modal_title', __( 'Select Language', 'auto-multilingual-seo' ) );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Language Switcher Settings', 'auto-multilingual-seo' ); ?></h1>
	
	<div class="amst-settings-container" style="max-width: 900px;">
		<form method="post" action="">
			<?php wp_nonce_field( 'amst_switcher_settings', 'amst_switcher_settings_nonce' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="amst_switcher_style"><?php esc_html_e( 'Switcher Style', 'auto-multilingual-seo' ); ?></label>
					</th>
					<td>
						<select name="amst_switcher_style" id="amst_switcher_style" class="regular-text">
							<option value="dropdown" <?php selected( $switcher_style, 'dropdown' ); ?>><?php esc_html_e( 'Simple Dropdown (Current - v1.3.0)', 'auto-multilingual-seo' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Simple dropdown with native browser select element - reliable and works everywhere.', 'auto-multilingual-seo' ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="amst_switcher_show_names"><?php esc_html_e( 'Show Language Names', 'auto-multilingual-seo' ); ?></label>
					</th>
					<td>
						<select name="amst_switcher_show_names" id="amst_switcher_show_names">
							<option value="no" <?php selected( $show_names, 'no' ); ?>><?php esc_html_e( 'No - Flags Only', 'auto-multilingual-seo' ); ?></option>
							<option value="yes" <?php selected( $show_names, 'yes' ); ?>><?php esc_html_e( 'Yes - Show Flags + Names', 'auto-multilingual-seo' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Display language names alongside flag icons.', 'auto-multilingual-seo' ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="amst_switcher_flag_size"><?php esc_html_e( 'Flag Size', 'auto-multilingual-seo' ); ?></label>
					</th>
					<td>
						<select name="amst_switcher_flag_size" id="amst_switcher_flag_size">
							<option value="small" <?php selected( $flag_size, 'small' ); ?>><?php esc_html_e( 'Small (18px)', 'auto-multilingual-seo' ); ?></option>
							<option value="medium" <?php selected( $flag_size, 'medium' ); ?>><?php esc_html_e( 'Medium (24px)', 'auto-multilingual-seo' ); ?></option>
							<option value="large" <?php selected( $flag_size, 'large' ); ?>><?php esc_html_e( 'Large (32px)', 'auto-multilingual-seo' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Set the size of flag icons.', 'auto-multilingual-seo' ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="amst_switcher_position"><?php esc_html_e( 'Fixed Position', 'auto-multilingual-seo' ); ?></label>
					</th>
					<td>
						<select name="amst_switcher_position" id="amst_switcher_position" class="regular-text">
							<option value="inline" <?php selected( $position, 'inline' ); ?>><?php esc_html_e( 'Inline (No Fixed Position)', 'auto-multilingual-seo' ); ?></option>
							<option value="bottom-right" <?php selected( $position, 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'auto-multilingual-seo' ); ?></option>
							<option value="bottom-left" <?php selected( $position, 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'auto-multilingual-seo' ); ?></option>
							<option value="top-right" <?php selected( $position, 'top-right' ); ?>><?php esc_html_e( 'Top Right', 'auto-multilingual-seo' ); ?></option>
							<option value="top-left" <?php selected( $position, 'top-left' ); ?>><?php esc_html_e( 'Top Left', 'auto-multilingual-seo' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Make switcher fixed on screen (useful for floating language selector).', 'auto-multilingual-seo' ); ?>
						</p>
					</td>
				</tr>
				

			</table>
			
			<!-- v1.5.2: Removed Homepage URL Configuration section - automatic URL generation only -->
			
			<h2><?php esc_html_e( 'How to Use', 'auto-multilingual-seo' ); ?></h2>
			<div class="amst-usage-instructions" style="background: #f5f5f5; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;">
				<h3><?php esc_html_e( 'Shortcode', 'auto-multilingual-seo' ); ?></h3>
				<p><?php esc_html_e( 'Add the language switcher to any page, post, or widget:', 'auto-multilingual-seo' ); ?></p>
				<code style="display: block; background: #fff; padding: 10px; margin: 10px 0;">[amst_language_switcher]</code>
				
				<h3 style="margin-top: 20px;"><?php esc_html_e( 'Theme Template', 'auto-multilingual-seo' ); ?></h3>
				<p><?php esc_html_e( 'Add to your theme files (e.g., header.php, footer.php):', 'auto-multilingual-seo' ); ?></p>
				<code style="display: block; background: #fff; padding: 10px; margin: 10px 0;">&lt;?php if ( function_exists( 'amst' ) ) { echo do_shortcode( '[amst_language_switcher]' ); } ?&gt;</code>
				
				<h3 style="margin-top: 20px;"><?php esc_html_e( 'Custom Position', 'auto-multilingual-seo' ); ?></h3>
				<p><?php esc_html_e( 'Override position with shortcode attribute:', 'auto-multilingual-seo' ); ?></p>
				<code style="display: block; background: #fff; padding: 10px; margin: 10px 0;">[amst_language_switcher position="bottom-right"]</code>
				
				<h3 style="margin-top: 20px;"><?php esc_html_e( 'Show/Hide Names', 'auto-multilingual-seo' ); ?></h3>
				<p><?php esc_html_e( 'Control language name display:', 'auto-multilingual-seo' ); ?></p>
				<code style="display: block; background: #fff; padding: 10px; margin: 10px 0;">[amst_language_switcher show_names="yes"]</code>
			</div>
			
			<h2><?php esc_html_e( 'Preview', 'auto-multilingual-seo' ); ?></h2>
			<div class="amst-preview" style="background: #fff; padding: 30px; border: 1px solid #ddd; margin: 20px 0;">
				<h3><?php esc_html_e( 'Current Settings Preview:', 'auto-multilingual-seo' ); ?></h3>
				<?php
				if ( function_exists( 'amst' ) && isset( amst()->language_switcher ) ) {
					echo do_shortcode( '[amst_language_switcher]' );
				}
				?>
			</div>
			
			<?php submit_button( __( 'Save Switcher Settings', 'auto-multilingual-seo' ) ); ?>
		</form>
	</div>
</div>

<style>
.amst-settings-container .form-table th {
	padding-left: 0;
}

.amst-settings-container .form-table td {
	padding-right: 0;
}

.amst-usage-instructions h3 {
	margin-top: 0;
	margin-bottom: 10px;
	color: #0073aa;
}

.amst-usage-instructions code {
	font-size: 13px;
	font-family: Consolas, Monaco, monospace;
}

.amst-preview {
	text-align: center;
}

.amst-preview h3 {
	margin-bottom: 20px;
	color: #333;
}
</style>
