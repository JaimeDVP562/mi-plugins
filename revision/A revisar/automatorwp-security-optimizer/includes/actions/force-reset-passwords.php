<?php
/**
 * Action: Force Reset Passwords
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * AutomatorWP action class that forces all non-admin users to reset passwords.
 */
class AutomatorWP_SG_Security_Force_Reset_Passwords extends AutomatorWP_Integration_Action {

    public $integration = 'security_optimizer';
    public $action = 'sg_security_force_reset_passwords';

    /**
     * Registers the password reset action with AutomatorWP.
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Force all users to reset their passwords', 'automatorwp' ),
            'select_option' => __( 'Force <strong>all users</strong> to reset their passwords', 'automatorwp' ),
            'edit_label'    => __( 'Force all users to reset their passwords', 'automatorwp' ),
            'log_label'     => __( 'All user passwords reset', 'automatorwp' ),
        ) );
    }

    /**
     * Executes a forced password reset for all non-administrator users.
     *
     * @param object $action         The current action object.
     * @param int    $user_id        The ID of the user that triggered the action.
     * @param array  $action_options Action options provided by AutomatorWP.
     * @param object $automation     The automation object.
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        if ( class_exists( '\SG_Security\Password_Service\Password_Service' ) ) {
            $users = get_users( array( 'fields' => array( 'ID' ) ) );

            foreach ( $users as $user ) {
                if ( user_can( $user->ID, 'manage_options' ) ) {
                    continue;
                }

                update_user_meta( $user->ID, 'sg_security_force_password_reset', 1 );
            }
        }

        $users = get_users( array( 'fields' => array( 'ID' ) ) );

        foreach ( $users as $user ) {
            if ( user_can( $user->ID, 'manage_options' ) ) {
                continue;
            }

            WP_Session_Tokens::get_instance( $user->ID )->destroy_all();
        }

        set_transient( '_sgs_force_password_reset', 1, MINUTE_IN_SECONDS * 5 );
    }
}
new AutomatorWP_SG_Security_Force_Reset_Passwords();