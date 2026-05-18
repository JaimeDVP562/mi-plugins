<?php
/**
 * Plugin Name: AutomatorWP - Asana Integration
 * Description: Integrates AutomatorWP with Asana.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load action
add_action('automatorwp_init', function() {
    require_once plugin_dir_path(__FILE__) . 'includes/action-create-task.php';
});