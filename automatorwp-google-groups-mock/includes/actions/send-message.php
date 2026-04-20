<?php
/**
 * Action: Send message to Google Group
 *
 * @package     AutomatorWP\GoogleGroups\Actions
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Send_Message extends AutomatorWP_Integration_Action {

    public $integration = 'googlegroups';
    public $action      = 'googlegroups_send_message';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Send message to Google Group', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Send <strong>message</strong> to a Google Group', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Send message to %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'log_label'     => sprintf( __( 'Message sent to %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'options'       => array(
'group' => automatorwp_utilities_ajax_selector_option( array(
                    'field'       => 'group',
                    'name'        => __( 'Select Google Group', 'automatorwp-googlegroups' ),
                    'option_none' => false,
                    'option_default' => __( 'Select a Google Group', 'automatorwp-googlegroups' ),
                    'action_cb'   => 'automatorwp_googlegroups_get_groups',
                    'options_cb'  => 'automatorwp_googlegroups_options_groups',
                    'placeholder' => __( 'Select a Google Group', 'automatorwp-googlegroups' ),
                    'default'     => '',
                    'required'    => true,
                ) ),
                'subject' => array(
                    'name'          => __( 'Subject', 'automatorwp-googlegroups' ),
                    'type'          => 'text',
                    'required'      => true,
                    'supports_tags' => true,
                ),
                'message' => array(
                    'name'          => __( 'Message body', 'automatorwp-googlegroups' ),
                    'type'          => 'textarea',
                    'required'      => true,
                    'supports_tags' => true,
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $group   = isset( $action_options['group'] ) ? $action_options['group'] : '';
        $subject = isset( $action_options['subject'] ) ? automatorwp_parse_automation_tags( $action_options['subject'], $user_id, $automation->id, $action->id ) : '';
        $message = isset( $action_options['message'] ) ? automatorwp_parse_automation_tags( $action_options['message'], $user_id, $automation->id, $action->id ) : '';

        if ( empty( $group ) || empty( $subject ) || empty( $message ) ) {
            $this->result = __( 'Error: Missing fields.', 'automatorwp-googlegroups' );
            return;
        }
        $response = automatorwp_googlegroups_send_message( $group, $subject, $message );
        if ( $response === 200 ) {
            $this->result = __( 'Message queued.', 'automatorwp-googlegroups' );
        } else {
            $this->result = __( 'Failed to send message.', 'automatorwp-googlegroups' );
        }
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if ( $action->type !== $this->action ) {
            return $log_meta;
        }
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( 'action' !== $log->type || $object->type !== $this->action ) {
            return $log_fields;
        }
        $log_fields['result'] = array(
            'name' => __( 'Result', 'automatorwp-googlegroups' ),
            'type' => 'text',
        );
        return $log_fields;
    }
}

new AutomatorWP_GoogleGroups_Send_Message();