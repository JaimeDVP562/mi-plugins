<?php
/**
 * User Login (via LatePoint)
 *
 * @package     AutomatorWP\Integrations\LatePoint\Triggers\User_Login
 * @author      Sergio Garcia
 * @since       1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_LatePoint_User_Login extends AutomatorWP_LatePoint_Trigger_Base {

    public $trigger = 'latepoint_user_login';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User <strong>logs in</strong> via LatePoint', 'automatorwp-latepoint' ),
            'select_option' => __( 'User <strong>logs in</strong> via LatePoint', 'automatorwp-latepoint' ),
            'edit_label'    => sprintf( __( 'User logs in via LatePoint %1$s time(s)', 'automatorwp-latepoint' ), '{times}' ),
            'log_label'     => __( 'User logged in via LatePoint', 'automatorwp-latepoint' ),
            'action'        => 'wp_login', 
        ) );
    }

    public function listener( $user_login, $user ) {
        automatorwp_trigger_event( array(
            'trigger'  => $this->trigger,
            'user_id'  => $user->ID,
        ) );
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        return $log_fields;
    }
}

// Instanciar la clase
new AutomatorWP_LatePoint_User_Login();