<?php
/**
 * Plugin Name: AutomatorWP - Asana Integration
 * Description: Connect AutomatorWP with Asana to automate task management.
 * Version: 1.0.0
 * Author: AutomatorWP
 * Text Domain: automatorwp-asana
 *
 * @package AutomatorWP\Asana
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Initialize the Asana integration once AutomatorWP has loaded.
 *
 * This hook ensures that all necessary dependencies from the core plugin
 * are available before registering any custom actions or triggers.
 *
 * @since 1.0.0
 */
add_action('automatorwp_init', function() {
    // Load the Asana create task action include file.
    require_once plugin_dir_path(__FILE__) . 'includes/action-create-task.php';
});