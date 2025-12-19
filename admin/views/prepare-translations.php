<?php
/**
 * Pre-translation admin page template.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Prepare Translations', 'auto-multilingual-seo' ); ?></h1>
	
	<div class="notice notice-info">
		<p>
			<strong><?php esc_html_e( 'Performance Optimization', 'auto-multilingual-seo' ); ?></strong><br>
			<?php esc_html_e( 'This tool pre-generates translations for all your content to improve frontend performance. Translations are generated in batches to avoid timeouts.', 'auto-multilingual-seo' ); ?>
		</p>
	</div>
	
	<div id="amst-prepare-status" class="card" style="max-width: 600px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Content Summary', 'auto-multilingual-seo' ); ?></h2>
		<table class="widefat">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Pages:', 'auto-multilingual-seo' ); ?></td>
					<td id="amst-pages-count">-</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Posts:', 'auto-multilingual-seo' ); ?></td>
					<td id="amst-posts-count">-</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Directory Listings:', 'auto-multilingual-seo' ); ?></td>
					<td id="amst-directorist-count">-</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Total Items:', 'auto-multilingual-seo' ); ?></strong></td>
					<td><strong id="amst-total-count">-</strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Enabled Languages:', 'auto-multilingual-seo' ); ?></td>
					<td id="amst-enabled-languages">-</td>
				</tr>
			</tbody>
		</table>
		
		<p style="margin-top: 20px;">
			<button type="button" id="amst-start-preparation" class="button button-primary button-hero">
				<?php esc_html_e( 'Start Translation Preparation', 'auto-multilingual-seo' ); ?>
			</button>
			<button type="button" id="amst-stop-preparation" class="button button-secondary" style="display: none;">
				<?php esc_html_e( 'Stop', 'auto-multilingual-seo' ); ?>
			</button>
		</p>
	</div>
	
	<div id="amst-prepare-progress" class="card" style="max-width: 800px; margin-top: 20px; display: none;">
		<h2><?php esc_html_e( 'Progress', 'auto-multilingual-seo' ); ?></h2>
		
		<div style="margin-bottom: 20px;">
			<h3 id="amst-current-type"><?php esc_html_e( 'Processing...', 'auto-multilingual-seo' ); ?></h3>
			<div style="background: #f0f0f1; height: 30px; border-radius: 4px; overflow: hidden; position: relative;">
				<div id="amst-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
				<span id="amst-progress-text" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-weight: bold;">0%</span>
			</div>
		</div>
		
		<div id="amst-progress-stats">
			<table class="widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Type', 'auto-multilingual-seo' ); ?></th>
						<th><?php esc_html_e( 'Processed', 'auto-multilingual-seo' ); ?></th>
						<th><?php esc_html_e( 'Translations Generated', 'auto-multilingual-seo' ); ?></th>
						<th><?php esc_html_e( 'Status', 'auto-multilingual-seo' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr id="amst-stat-pages">
						<td><?php esc_html_e( 'Pages', 'auto-multilingual-seo' ); ?></td>
						<td class="amst-processed">0</td>
						<td class="amst-translated">0</td>
						<td class="amst-status">-</td>
					</tr>
					<tr id="amst-stat-posts">
						<td><?php esc_html_e( 'Posts', 'auto-multilingual-seo' ); ?></td>
						<td class="amst-processed">0</td>
						<td class="amst-translated">0</td>
						<td class="amst-status">-</td>
					</tr>
					<tr id="amst-stat-directorist">
						<td><?php esc_html_e( 'Directory Listings', 'auto-multilingual-seo' ); ?></td>
						<td class="amst-processed">0</td>
						<td class="amst-translated">0</td>
						<td class="amst-status">-</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div id="amst-progress-log" style="margin-top: 20px; max-height: 300px; overflow-y: auto; background: #f0f0f1; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px;">
		</div>
	</div>
	
	<div id="amst-prepare-complete" class="notice notice-success" style="display: none; margin-top: 20px;">
		<p>
			<strong><?php esc_html_e( 'Translation Preparation Complete!', 'auto-multilingual-seo' ); ?></strong><br>
			<span id="amst-complete-message"></span>
		</p>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	var preparationInProgress = false;
	var preparationStopped = false;
	var totalStats = {
		pages: { processed: 0, translated: 0 },
		posts: { processed: 0, translated: 0 },
		directorist: { processed: 0, translated: 0 }
	};
	
	// Load status on page load.
	loadStatus();
	
	// Start preparation button.
	$('#amst-start-preparation').on('click', function() {
		if (preparationInProgress) {
			return;
		}
		
		preparationInProgress = true;
		preparationStopped = false;
		
		$('#amst-start-preparation').prop('disabled', true).hide();
		$('#amst-stop-preparation').show();
		$('#amst-prepare-progress').show();
		$('#amst-prepare-complete').hide();
		
		// Reset stats.
		totalStats = {
			pages: { processed: 0, translated: 0 },
			posts: { processed: 0, translated: 0 },
			directorist: { processed: 0, translated: 0 }
		};
		
		updateStats();
		logMessage('<?php esc_html_e( 'Starting translation preparation...', 'auto-multilingual-seo' ); ?>');
		
		// Process in order: pages -> posts -> directorist.
		processType('pages', 0);
	});
	
	// Stop preparation button.
	$('#amst-stop-preparation').on('click', function() {
		preparationStopped = true;
		$('#amst-stop-preparation').prop('disabled', true);
		logMessage('<?php esc_html_e( 'Stopping... (current batch will complete)', 'auto-multilingual-seo' ); ?>', 'warning');
	});
	
	// Load status.
	function loadStatus() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'amst_get_preparation_status',
				nonce: '<?php echo esc_js( wp_create_nonce( 'amst-prepare-translations' ) ); ?>'
			},
			success: function(response) {
				if (response.success && response.data) {
					$('#amst-pages-count').text(response.data.pages_count);
					$('#amst-posts-count').text(response.data.posts_count);
					$('#amst-directorist-count').text(response.data.directorist_count);
					$('#amst-total-count').text(response.data.total_count);
					$('#amst-enabled-languages').text(response.data.enabled_languages.join(', '));
				}
			}
		});
	}
	
	// Process content type.
	function processType(type, batch) {
		if (preparationStopped) {
			finishPreparation();
			return;
		}
		
		var typeName = type.charAt(0).toUpperCase() + type.slice(1);
		$('#amst-current-type').text('<?php esc_html_e( 'Processing', 'auto-multilingual-seo' ); ?> ' + typeName + '...');
		$('#amst-stat-' + type + ' .amst-status').text('<?php esc_html_e( 'Processing...', 'auto-multilingual-seo' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'amst_prepare_translations',
				nonce: '<?php echo esc_js( wp_create_nonce( 'amst-prepare-translations' ) ); ?>',
				type: type,
				batch: batch
			},
			success: function(response) {
				if (response.success && response.data) {
					var data = response.data;
					
					// Update stats.
					totalStats[type].processed += data.processed;
					totalStats[type].translated += data.translated;
					updateStats();
					
					// Log message.
					logMessage(data.message);
					
					// Continue or move to next type.
					if (data.complete) {
						$('#amst-stat-' + type + ' .amst-status').html('<span style="color: green;">✓ <?php esc_html_e( 'Complete', 'auto-multilingual-seo' ); ?></span>');
						
						// Move to next type.
						if (type === 'pages') {
							processType('posts', 0);
						} else if (type === 'posts') {
							processType('directorist', 0);
						} else {
							finishPreparation();
						}
					} else {
						// Process next batch.
						processType(type, batch + 1);
					}
				} else {
					logMessage('<?php esc_html_e( 'Error processing batch', 'auto-multilingual-seo' ); ?>', 'error');
					finishPreparation();
				}
			},
			error: function() {
				logMessage('<?php esc_html_e( 'AJAX error', 'auto-multilingual-seo' ); ?>', 'error');
				finishPreparation();
			}
		});
	}
	
	// Update statistics display.
	function updateStats() {
		$('#amst-stat-pages .amst-processed').text(totalStats.pages.processed);
		$('#amst-stat-pages .amst-translated').text(totalStats.pages.translated);
		
		$('#amst-stat-posts .amst-processed').text(totalStats.posts.processed);
		$('#amst-stat-posts .amst-translated').text(totalStats.posts.translated);
		
		$('#amst-stat-directorist .amst-processed').text(totalStats.directorist.processed);
		$('#amst-stat-directorist .amst-translated').text(totalStats.directorist.translated);
		
		// Update progress bar (rough estimate).
		var totalProcessed = totalStats.pages.processed + totalStats.posts.processed + totalStats.directorist.processed;
		var totalCount = parseInt($('#amst-total-count').text()) || 1;
		var percentage = Math.min(100, Math.round((totalProcessed / totalCount) * 100));
		
		$('#amst-progress-bar').css('width', percentage + '%');
		$('#amst-progress-text').text(percentage + '%');
	}
	
	// Log message.
	function logMessage(message, type) {
		type = type || 'info';
		var color = type === 'error' ? '#dc3232' : (type === 'warning' ? '#dba617' : '#46b450');
		var timestamp = new Date().toLocaleTimeString();
		var $log = $('#amst-progress-log');
		
		$log.append('<div style="margin-bottom: 5px; color: ' + color + ';">[' + timestamp + '] ' + message + '</div>');
		$log.scrollTop($log[0].scrollHeight);
	}
	
	// Finish preparation.
	function finishPreparation() {
		preparationInProgress = false;
		
		$('#amst-start-preparation').prop('disabled', false).show();
		$('#amst-stop-preparation').hide().prop('disabled', false);
		$('#amst-progress-bar').css('width', '100%');
		$('#amst-progress-text').text('100%');
		
		var totalTranslated = totalStats.pages.translated + totalStats.posts.translated + totalStats.directorist.translated;
		var message = '<?php esc_html_e( 'Generated', 'auto-multilingual-seo' ); ?> ' + totalTranslated + ' <?php esc_html_e( 'translations.', 'auto-multilingual-seo' ); ?>';
		
		$('#amst-complete-message').text(message);
		$('#amst-prepare-complete').show();
		
		logMessage('<?php esc_html_e( 'Translation preparation finished!', 'auto-multilingual-seo' ); ?>', 'info');
	}
});
</script>

<style>
#amst-progress-log {
	line-height: 1.6;
}
</style>
