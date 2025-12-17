<?php
/**
 * Manual Translations Admin Interface
 *
 * @package Auto_Multilingual_SEO_Translator
 * @since   1.3.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle form submissions
if ( isset( $_POST['amst_add_manual_translation'] ) && check_admin_referer( 'amst_manual_translation_nonce' ) ) {
	$original_text = sanitize_textarea_field( $_POST['original_text'] );
	$target_language = sanitize_text_field( $_POST['target_language'] );
	$manual_translation = sanitize_textarea_field( $_POST['manual_translation'] );
	
	if ( ! empty( $original_text ) && ! empty( $target_language ) && ! empty( $manual_translation ) ) {
		$result = amst()->manual_translations->add_manual_translation( 
			$original_text, 
			$manual_translation, 
			$target_language, 
			'en',
			'manual_override'
		);
		if ( $result ) {
			echo '<div class="notice notice-success is-dismissible"><p>Manual translation added successfully!</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>Failed to add manual translation.</p></div>';
		}
	}
}

// Handle delete
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) && check_admin_referer( 'amst_delete_override_' . $_GET['id'] ) ) {
	$id = intval( $_GET['id'] );
	if ( amst()->manual_translations->delete_manual_translation( $id ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Manual translation deleted successfully!</p></div>';
	}
}

// Get supported languages
$languages = array(
	'bg' => 'Bulgarian 🇧🇬',
	'hr' => 'Croatian 🇭🇷',
	'cs' => 'Czech 🇨🇿',
	'da' => 'Danish 🇩🇰',
	'nl' => 'Dutch 🇳🇱',
	'et' => 'Estonian 🇪🇪',
	'fi' => 'Finnish 🇫🇮',
	'fr' => 'French 🇫🇷',
	'de' => 'German 🇩🇪',
	'el' => 'Greek 🇬🇷',
	'hu' => 'Hungarian 🇭🇺',
	'ga' => 'Irish 🇮🇪',
	'it' => 'Italian 🇮🇹',
	'lv' => 'Latvian 🇱🇻',
	'lt' => 'Lithuanian 🇱🇹',
	'mt' => 'Maltese 🇲🇹',
	'pl' => 'Polish 🇵🇱',
	'pt' => 'Portuguese 🇵🇹',
	'ro' => 'Romanian 🇷🇴',
	'sk' => 'Slovak 🇸🇰',
	'sl' => 'Slovenian 🇸🇮',
	'es' => 'Spanish 🇪🇸',
	'sv' => 'Swedish 🇸🇪',
);

// Get pagination
$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page = 20;
$offset = ( $paged - 1 ) * $per_page;

// Get filter
$filter_lang = isset( $_GET['filter_lang'] ) ? sanitize_text_field( $_GET['filter_lang'] ) : '';
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

// Get overrides using the get_manual_translations method
$args = array(
	'per_page' => $per_page,
	'offset' => $offset,
);

if ( ! empty( $search ) ) {
	// For search, we'll do a simple LIKE query through the args
	$args['search'] = $search;
}

if ( ! empty( $filter_lang ) ) {
	$args['target_language'] = $filter_lang;
}

$overrides = amst()->manual_translations->get_manual_translations( $args );
$total = count( $overrides ); // Simplified - in production you'd want a separate count query

$total_pages = ceil( $total / $per_page );

?>

<div class="wrap">
	<h1>Manual Translations</h1>
	<p>Add manual translation corrections that take priority over automatic translations. Supports all EU language special characters.</p>
	
	<div class="amst-manual-translations-container">
		
		<!-- Add New Translation Form -->
		<div class="card" style="max-width: 800px; margin: 20px 0;">
			<h2>Add Manual Translation</h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'amst_manual_translation_nonce' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="target_language">Target Language</label>
						</th>
						<td>
							<select name="target_language" id="target_language" required style="width: 300px;">
								<option value="">Select Language</option>
								<?php foreach ( $languages as $code => $name ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">Select the language for this translation</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="original_text">Original Text (English)</label>
						</th>
						<td>
							<textarea name="original_text" id="original_text" rows="3" cols="50" required style="width: 100%; max-width: 500px;"></textarea>
							<p class="description">Enter the original English text exactly as it appears on your site</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="manual_translation">Manual Correction</label>
						</th>
						<td>
							<textarea name="manual_translation" id="manual_translation" rows="3" cols="50" required style="width: 100%; max-width: 500px;"></textarea>
							<p class="description">Enter your manual translation with correct special characters (č, š, ž, đ, ć, etc.)</p>
						</td>
					</tr>
				</table>
				
				<p class="submit">
					<input type="submit" name="amst_add_manual_translation" id="submit" class="button button-primary" value="Add Manual Translation">
				</p>
			</form>
		</div>
		
		<!-- Search and Filter -->
		<div class="tablenav top" style="margin: 20px 0;">
			<div class="alignleft actions">
				<form method="get" action="">
					<input type="hidden" name="page" value="amst-manual-translations">
					<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search translations...">
					<select name="filter_lang">
						<option value="">All Languages</option>
						<?php foreach ( $languages as $code => $name ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $filter_lang, $code ); ?>>
								<?php echo esc_html( $name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="submit" class="button" value="Filter">
					<?php if ( $filter_lang || $search ) : ?>
						<a href="<?php echo admin_url( 'admin.php?page=amst-manual-translations' ); ?>" class="button">Clear</a>
					<?php endif; ?>
				</form>
			</div>
			<div class="alignright">
				<span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s item', '%s items', $total ), number_format_i18n( $total ) ) ); ?></span>
			</div>
		</div>
		
		<!-- Manual Translations List -->
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 15%;">Language</th>
					<th style="width: 35%;">Original Text</th>
					<th style="width: 35%;">Manual Translation</th>
					<th style="width: 10%;">Updated</th>
					<th style="width: 5%;">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $overrides ) ) : ?>
					<tr>
						<td colspan="5" style="text-align: center; padding: 30px;">
							<em>No manual translations found. Add your first one above!</em>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $overrides as $override ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( isset( $languages[$override->target_language] ) ? $languages[$override->target_language] : $override->target_language ); ?></strong>
							</td>
							<td>
								<div style="max-height: 100px; overflow-y: auto;">
									<?php echo esc_html( $override->original_text ); ?>
								</div>
							</td>
							<td>
								<div style="max-height: 100px; overflow-y: auto;">
									<?php echo esc_html( $override->translated_text ); ?>
								</div>
							</td>
							<td>
								<?php echo esc_html( date( 'Y-m-d', strtotime( $override->updated_at ) ) ); ?>
							</td>
							<td>
								<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=amst-manual-translations&action=delete&id=' . $override->id ), 'amst_delete_override_' . $override->id ); ?>" 
								   class="button button-small" 
								   onclick="return confirm('Are you sure you want to delete this manual translation?');">Delete</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		
		<!-- Pagination -->
		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					$base_url = add_query_arg( array(
						'page' => 'amst-manual-translations',
						'filter_lang' => $filter_lang,
						's' => $search,
					), admin_url( 'admin.php' ) );
					
					echo paginate_links( array(
						'base' => add_query_arg( 'paged', '%#%', $base_url ),
						'format' => '',
						'prev_text' => __( '&laquo;' ),
						'next_text' => __( '&raquo;' ),
						'total' => $total_pages,
						'current' => $paged,
					) );
					?>
				</div>
			</div>
		<?php endif; ?>
		
	</div>
	
	<!-- Help Section -->
	<div class="card" style="max-width: 800px; margin: 30px 0;">
		<h2>How to Use Manual Translations</h2>
		<p><strong>Priority System:</strong></p>
		<ol>
			<li><strong>Manual Override</strong> - Takes highest priority (added here)</li>
			<li><strong>Automatic Translation</strong> - Google Cloud Translation API</li>
			<li><strong>Original Text</strong> - Fallback if no translation available</li>
		</ol>
		
		<p><strong>What You Can Translate:</strong></p>
		<ul>
			<li>Page and post titles</li>
			<li>Page and post content</li>
			<li>Menu item labels</li>
			<li>Form labels and buttons</li>
			<li>Plugin and theme strings</li>
		</ul>
		
		<p><strong>Special Characters Support:</strong></p>
		<p>The system fully supports UTF-8 encoding for all EU language special characters including Croatian (č, š, ž, đ, ć), French (é, è, ê, ç), German (ä, ö, ü, ß), Spanish (ñ, á, é), and all others.</p>
		
		<p><strong>Example:</strong></p>
		<ol>
			<li>Select Language: <em>German (de)</em></li>
			<li>Original Text: <em>"Welcome to our website"</em></li>
			<li>Manual Correction: <em>"Herzlich Willkommen auf unserer Website"</em></li>
			<li>Save - Now all instances of "Welcome to our website" will show your manual translation instead of Google's automatic one</li>
		</ol>
	</div>
</div>

<style>
.amst-manual-translations-container {
	margin-top: 20px;
}
.amst-manual-translations-container .card {
	padding: 20px;
}
.amst-manual-translations-container textarea {
	font-family: monospace;
}
</style>
