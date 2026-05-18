<?php
/**
 * BP Unverify User Action
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_BP_Unverify_User_Action extends AutomatorWP_Integration_Action {

    public $integration = 'bp-verified-member';
    public $action = 'bp_verified_member_unverify_user';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Unverify a user', 'automatorwp-bp-verified-member' ),
            'select_option'     => __( '<strong>Unverify</strong> a user', 'automatorwp-bp-verified-member' ),
            'edit_label'        => __( 'Unverify user', 'automatorwp-bp-verified-member' ),
            'log_label'         => __( 'Unverify user', 'automatorwp-bp-verified-member' ),
            'options'           => array(),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        
        $this->result = '';

        if ( empty( $user_id ) ) {
            return;
        }

        $updated = update_user_meta( $user_id, 'bp_verified_member', false );

        if ( $updated !== false ) {
            $this->result = __( 'User unverified successfully.', 'automatorwp-bp-verified-member' );
        } else {
            $this->result = __( 'The user could not be unverified or was not verified.', 'automatorwp-bp-verified-member' );
        }
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-bp-verified-member' ),
            'type' => 'text',
        );
        return $log_fields;
    }
}

new AutomatorWP_BP_Unverify_User_Action();