<?php
/**
 * Plugin Name:           AutomatorWP - 7todos
 * Plugin URI:            https://automatorwp.com/add-ons/7todos/
 * Description:           Conecta AutomatorWP con la API de 7todos para crear y actualizar tareas.
 * Version:               1.0.1
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-7todos
 * Domain Path:           /languages/
 * Requires at least:     5.0
 * Requires PHP:          7.4
 * Tested up to:          6.8
 * License:               GNU AGPL v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class AutomatorWP_7todos {

    private static $instance;

    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    private function constants() {
        define( 'AUTOMATORWP_7TODOS_VER',      '1.0.1' );
        define( 'AUTOMATORWP_7TODOS_FILE',     __FILE__ );
        define( 'AUTOMATORWP_7TODOS_DIR',      plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_7TODOS_URL',      plugin_dir_url( __FILE__ ) );
        define( 'AUTOMATORWP_7TODOS_BASENAME', plugin_basename( __FILE__ ) );
    }

    private function includes() {
        if ( ! class_exists( 'AutomatorWP' ) ) {
            return;
        }

        require_once AUTOMATORWP_7TODOS_DIR . 'includes/functions.php';

        if ( is_admin() ) {
            require_once AUTOMATORWP_7TODOS_DIR . 'includes/admin.php';
        }

        require_once AUTOMATORWP_7TODOS_DIR . 'includes/actions/create-task.php';
        require_once AUTOMATORWP_7TODOS_DIR . 'includes/actions/update-task.php';
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    public function register_integration() {
        automatorwp_register_integration( '7todos', array(
            'label' => '7todos',
            'icon'  => AUTOMATORWP_7TODOS_URL . 'assets/7todos_icon.png',
        ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'automatorwp-7todos',
            false,
            dirname( AUTOMATORWP_7TODOS_BASENAME ) . '/languages/'
        );
    }
}

function AutomatorWP_7todos() {
    return AutomatorWP_7todos::instance();
}

add_action( 'plugins_loaded', 'AutomatorWP_7todos', 20 );