<?php
/**
 * Plugin Name: HelloNote
 * Plugin URI: https://gobird.io
 * Description: A simple WordPress plugin for managing admin notes with a custom admin page and database table.
 * Version: 1.0.1
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: trueqap, gobird.io
 * Author URI: https://gobird.io
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hellonote
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HELLONOTE_VERSION', '1.0.1');
define('HELLONOTE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HELLONOTE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HELLONOTE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require Composer autoloader
require_once HELLONOTE_PLUGIN_DIR . 'vendor/autoload.php';

// Initialize the plugin
add_action('plugins_loaded', function() {
    $plugin = new \HelloNote\Plugin();
    $plugin->init();
});

// Activation hook
register_activation_hook(__FILE__, function() {
    $database = new \HelloNote\Database();
    $database->create_table();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Cleanup actions on deactivation (if needed)
    // Note: We don't drop the table on deactivation to preserve data
});
