<?php
/**
 * Plugin Name:           AutomatorWP - Asgaros Forum
 * Description:           Connect AutomatorWP with Asgaros Forum.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Text Domain:           automatorwp-asgaros-forum
 */

final class AutomatorWP_Asgaros_Forum {

    private static $instance;

    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Asgaros_Forum();
            self::$instance->includes();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    private function includes() {
        if( $this->meets_requirements() ) {
            require_once plugin_dir_path( __FILE__ ) . 'includes/actions/add-user-to-group.php';
        }
    }

    private function hooks() {
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
    }

    public function register_integration() {
        automatorwp_register_integration( 'asgarosforum', array(
            'label' => 'Asgaros Forum',
            'icon'  => plugin_dir_url( __FILE__ ) . 'assets/asgarosforum.svg',
        ) );
    }

    private function meets_requirements() {
        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }
        if ( ! class_exists( 'AsgarosForum' ) ) {
            return false;
        }
        return true;
    }
}

function AutomatorWP_Asgaros_Forum() {
    return AutomatorWP_Asgaros_Forum::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Asgaros_Forum', 11 );