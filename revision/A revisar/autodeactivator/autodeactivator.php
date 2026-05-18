<?php

/** 
 * Plugin Name: Autodeactivator for AWP
 * Description: Deactivates AWP automatizations on date.
 * Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DAWP_PATH', plugin_dir_path( __FILE__ ) );

require_once DAWP_PATH . 'admin/class-dawp-admin.php';

function run_dawp_plugin() {

	$admin = new DAWP_Admin();
	$admin->init();

}

run_dawp_plugin();

?>