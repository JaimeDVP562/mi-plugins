<?php
/**
 * Action: Create Google Group
 *
 * @package     AutomatorWP\GoogleGroups\Actions
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Create_Group extends AutomatorWP_Integration_Action {

    public $integration = 'googlegroups';
    public $action      = 'googlegroups_create_group';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a Google Group', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Create a <strong>Google Group</strong>', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Create group %1$s', 'automatorwp-googlegroups' ), '{group_email}' ),
            'log_label'     => sprintf( __( 'Created group %1$s', 'automatorwp-googlegroups' ), '{group_email}' ),
            'options'       => array(
                'group_email' => array(
                    'name'          => __( 'Group email', 'automatorwp-googlegroups' ),
                    'type'          => 'text',
                    'required'      => true,
                    'supports_tags' => true,
                ),
                'group_name' => array(
                    'name'     => __( 'Group name', 'automatorwp-googlegroups' ),
                    'type'     => 'text',
                    'required' => false,
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $email = isset( $action_options['group_email'] ) ? automatorwp_parse_automation_tags( $action_options['group_email'], $user_id, $automation->id, $action->id ) : '';
        $name  = isset( $action_options['group_name'] ) ? $action_options['group_name'] : '';

        if ( empty( $email ) ) {
            $this->result = __( 'Error: Invalid group email.', 'automatorwp-googlegroups' );
            return;
        }

        $service = automatorwp_googlegroups_get_service();
        $response = $service ? $service->create_group( $email, $name ) : 0;
        if ( $response === 200 ) {
            do_action( 'automatorwp_googlegroups_group_created', $user_id, array( 'group' => $email ) );
            $this->result = __( 'Group created successfully.', 'automatorwp-googlegroups' );
        } else {
            $this->result = __( 'Failed to create group.', 'automatorwp-googlegroups' );
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

// Initialize the action class
new AutomatorWP_GoogleGroups_Create_Group();