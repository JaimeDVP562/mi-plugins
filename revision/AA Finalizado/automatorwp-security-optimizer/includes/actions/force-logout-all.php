<?php
/**
 * Force Logout All
 *
 * @package     AutomatorWP\Security_Optimizer\Actions\Force_Logout_All
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SG_Security_Force_Logout_All extends AutomatorWP_Integration_Action {

    public $integration = 'security_optimizer';
    public $action = 'sg_security_force_logout_all';

    /**
     * Registers the logout all action with AutomatorWP.
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Force all users to log out', 'automatorwp-security-optimizer' ),
            'select_option' => __( 'Force <strong>all users</strong> to log out', 'automatorwp-security-optimizer' ),
            'edit_label'    => __( 'Force all users to log out', 'automatorwp-security-optimizer' ),
            'log_label'     => __( 'All user sessions destroyed', 'automatorwp-security-optimizer' ),
            'options'       => array(),
        ) );
    }

    /**
     * Executes the force logout action for all users.
     *
     * @since 1.0.0
     *
     * @param object $action
     * @param int    $user_id
     * @param array  $action_options
     * @param object $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $this->result = '';

        $current_user_id = get_current_user_id();

        WP_Session_Tokens::destroy_all_for_all_users();

        if ( $current_user_id ) {
            wp_clear_auth_cookie();
            wp_set_current_user( $current_user_id );
            wp_set_auth_cookie( $current_user_id );
        }

        set_transient( '_sgs_all_sessions_destroyed', 1, 60 );

        $this->result = __( 'All user sessions destroyed successfully.', 'automatorwp-security-optimizer' );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields
     * @param stdClass  $log
     * @param stdClass  $object
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-security-optimizer' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_SG_Security_Force_Logout_All();