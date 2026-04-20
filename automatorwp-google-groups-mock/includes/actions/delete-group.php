<?php
/**
 * Action: Delete Google Group
 *
 * @package     AutomatorWP\GoogleGroups\Actions
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Delete_Group extends AutomatorWP_Integration_Action {

    public $integration = 'googlegroups';
    public $action      = 'googlegroups_delete_group';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Delete a Google Group', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Delete a <strong>Google Group</strong>', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Delete group %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'log_label'     => sprintf( __( 'Deleted group %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'options'       => array(
                // use ajax selector so user picks an existing group instead of typing
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
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $email = isset( $action_options['group'] ) ? automatorwp_parse_automation_tags( $action_options['group'], $user_id, $automation->id, $action->id ) : '';
        if ( empty( $email ) ) {
            $this->result = __( 'Error: Invalid group email.', 'automatorwp-googlegroups' );
            return;
        }
        $service = automatorwp_googlegroups_get_service();
        $response = $service ? $service->delete_group( $email ) : 0;
        if ( $response === 200 ) {
            do_action( 'automatorwp_googlegroups_group_deleted', $user_id, array( 'group' => $email ) );
            $this->result = __( 'Group deleted successfully.', 'automatorwp-googlegroups' );
        } else {
            $this->result = __( 'Failed to delete group.', 'automatorwp-googlegroups' );
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

new AutomatorWP_GoogleGroups_Delete_Group();