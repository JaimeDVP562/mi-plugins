<?php
/**
 * Action: Block User
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * AutomatorWP action class for blocking a user in SG Security.
 */
class AutomatorWP_SG_Security_Block_User extends AutomatorWP_Integration_Action {

    public $integration = 'security_optimizer';
    public $action = 'sg_security_block_user';

    /**
     * Registers the user block action with AutomatorWP.
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Block a user', 'automatorwp' ),
            'select_option' => __( 'Block <strong>user</strong>', 'automatorwp' ),
            'edit_label'    => sprintf( __( 'Block user %1$s', 'automatorwp' ), '{user_id}' ),
            'log_label'     => sprintf( __( 'User %1$s blocked', 'automatorwp' ), '{user_id}' ),
            'options'       => array(
                'user_id' => array(
                    'from' => 'user_id',
                    'fields' => array(
                        'user_id' => array(
                            'name'       => __( 'User:', 'automatorwp' ),
                            'type'       => 'select',
                            'options_cb' => 'automatorwp_sg_security_options_cb_users', 
        )
    )
),
            ),
        ) );
    }

    /**
     * Executes the user block action via SG Security.
     *
     * @param object $action         The current action object.
     * @param int    $user_id        The ID of the user that triggered the action.
     * @param array  $action_options Action options provided by AutomatorWP.
     * @param object $automation     The automation object.
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        $target_user_id = $action_options['user_id'];
        if ( empty( $target_user_id ) ) {
            return;
        }

        if ( class_exists( '\SG_Security\Block_Service\Block_Service' ) ) {
            $visitor_id = automatorwp_sg_security_get_or_create_visitor_id( $target_user_id );
            if ( $visitor_id ) {
                $block_service = new \SG_Security\Block_Service\Block_Service();
                $block_service->change_user_role( $visitor_id );
                update_user_meta( $target_user_id, '_sgs_user_blocked', 1 );
                return;
            }
        }

        update_user_meta( $target_user_id, '_sgs_user_blocked', 1 );
    }
}
new AutomatorWP_SG_Security_Block_User();