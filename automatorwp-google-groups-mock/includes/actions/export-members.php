<?php
/**
 * Action: Export or list Google Group members
 *
 * @package     AutomatorWP\GoogleGroups\Actions
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Export_Members extends AutomatorWP_Integration_Action {

    public $integration = 'googlegroups';
    public $action      = 'googlegroups_export_members';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Export/list members of a Google Group', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Export/list <strong>members</strong> of a Google Group', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Export members of %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'log_label'     => sprintf( __( 'Exported members of %1$s', 'automatorwp-googlegroups' ), '{group}' ),
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
                'format' => array(
                    'name'     => __( 'Format', 'automatorwp-googlegroups' ),
                    'type'     => 'select',
                    'options'  => array(
                        'array' => __( 'Return array', 'automatorwp-googlegroups' ),
                        'csv'   => __( 'Generate CSV file (stored as temp option)', 'automatorwp-googlegroups' ),
                    ),
                    'default'  => 'array',
                    'required' => true,
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $group  = isset( $action_options['group'] ) ? $action_options['group'] : '';
        $format = isset( $action_options['format'] ) ? $action_options['format'] : 'array';
        if ( empty( $group ) ) {
            $this->result = __( 'Error: Invalid group.', 'automatorwp-googlegroups' );
            return;
        }
        $members = automatorwp_googlegroups_export_members( $group, $format );
        if ( $format === 'csv' && is_string( $members ) ) {
            $this->result = __( 'CSV generated.', 'automatorwp-googlegroups' );
        } elseif ( is_array( $members ) ) {
            $this->result = __( 'Members retrieved.', 'automatorwp-googlegroups' );
        } else {
            $this->result = __( 'Failed to export members.', 'automatorwp-googlegroups' );
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

new AutomatorWP_GoogleGroups_Export_Members();