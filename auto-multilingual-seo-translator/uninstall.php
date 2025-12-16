<?php
/**
 * Uninstall Script
 * Fired when the plugin is uninstalled
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete plugin options
 */
delete_option('amst_options');

/**
 * Delete transients
 */
global $wpdb;

$wpdb->query(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_amst_%' OR option_name LIKE '_transient_timeout_amst_%'"
);

/**
 * Clear WordPress cache
 */
wp_cache_flush();

/**
 * Clear language cookies
 */
if (isset($_COOKIE['amst_language'])) {
    setcookie('amst_language', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
}
