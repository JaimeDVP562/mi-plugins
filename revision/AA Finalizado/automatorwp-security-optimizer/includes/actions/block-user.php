<?php
/**
 * Block User
 *
 * @package     AutomatorWP\Security_Optimizer\Actions\Block_User
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SG_Security_Block_User extends AutomatorWP_Integration_Action {

    public $integration = 'security_optimizer';
    public $action = 'sg_security_block_user';

    /**
     * Registers the user block action with AutomatorWP.
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Block a user', 'automatorwp-security-optimizer' ),
            'select_option' => __( 'Block <strong>user</strong>', 'automatorwp-security-optimizer' ),
            'edit_label'    => sprintf( __( 'Block user %1$s', 'automatorwp-security-optimizer' ), '{user_id}' ),
            'log_label'     => sprintf( __( 'User %1$s blocked', 'automatorwp-security-optimizer' ), '{user_id}' ),
            'options'       => array(
                'user_id' => array(
                    'from'   => 'user_id',
                    'fields' => array(
                        'user_id' => array(
                            'name'       => __( 'User:', 'automatorwp-security-optimizer' ),
                            'type'       => 'select',
                            'options_cb' => 'automatorwp_sg_security_options_cb_users',
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Executes the user block action via SG Security.
     *
     * @since 1.0.0
     *
     * @param object $action
     * @param int    $user_id
     * @param array  $action_options
     * @param object $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $target_user_id = $action_options['user_id'];

        $this->result = '';

        if ( empty( $target_user_id ) ) {
            return;
        }

        if ( class_exists( '\SG_Security\Block_Service\Block_Service' ) ) {
            $visitor_id = automatorwp_sg_security_get_or_create_visitor_id( $target_user_id );
            if ( $visitor_id ) {
                $block_service = new \SG_Security\Block_Service\Block_Service();
                $block_service->change_user_role( $visitor_id );
                update_user_meta( $target_user_id, '_sgs_user_blocked', 1 );
                $this->result = __( 'User blocked successfully.', 'automatorwp-security-optimizer' );
                return;
            }
        }

        update_user_meta( $target_user_id, '_sgs_user_blocked', 1 );
        $this->result = __( 'User blocked successfully.', 'automatorwp-security-optimizer' );

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

new AutomatorWP_SG_Security_Block_User();