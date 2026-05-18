<?php
/**
 * Action: Force Logout All
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * AutomatorWP action class that forces all users to log out.
 */
class AutomatorWP_SG_Security_Force_Logout_All extends AutomatorWP_Integration_Action {

    public $integration = 'security_optimizer';
    public $action = 'sg_security_force_logout_all';

    /**
     * Registers the logout all action with AutomatorWP.
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Force all users to log out', 'automatorwp' ),
            'select_option' => __( 'Force <strong>all users</strong> to log out', 'automatorwp' ),
            'edit_label'    => __( 'Force all users to log out', 'automatorwp' ),
            'log_label'     => __( 'All user sessions destroyed', 'automatorwp' ),
        ) );
    }

    /**
     * Executes the force logout action for all users.
     *
     * @param object $action         The current action object.
     * @param int    $user_id        The ID of the user that triggered the action.
     * @param array  $action_options Action options provided by AutomatorWP.
     * @param object $automation     The automation object.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'permission_denied', __( 'No tienes permiso para forzar logout de usuarios.', 'automatorwp' ) );
        }

        $current_user_id = get_current_user_id();

        WP_Session_Tokens::destroy_all_for_all_users();

        if ( $current_user_id ) {
            wp_clear_auth_cookie();
            wp_set_current_user( $current_user_id );
            wp_set_auth_cookie( $current_user_id );
        }

        set_transient( '_sgs_all_sessions_destroyed', 1, 60 );

        return true;
    }


}
new AutomatorWP_SG_Security_Force_Logout_All();

