<?php

/**
 * Plugin Name: Speed Optimizer Integration
 * Description: Integrates with SiteGround Optimizer to manage caching.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'SOI_PATH', plugin_dir_path( __FILE__ ) );

require_once SOI_PATH . 'admin/class-soi-admin.php';

function run_soi_integration() {

	$admin = new SOI_Admin();
	$admin->init();

}

run_soi_integration();

?>