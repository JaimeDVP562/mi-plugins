<?php
/**
 * Action: Change member role in a Google Group
 *
 * @package     AutomatorWP\GoogleGroups\Actions
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Change_Member_Role extends AutomatorWP_Integration_Action {

    public $integration = 'googlegroups';
    public $action      = 'googlegroups_change_member_role';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Change Google Group member role', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Change <strong>member role</strong> in Google Group', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Change role of %1$s in %2$s', 'automatorwp-googlegroups' ), '{member_email}', '{group}' ),
            'log_label'     => sprintf( __( 'Changed role of %1$s in %2$s', 'automatorwp-googlegroups' ), '{member_email}', '{group}' ),
            'options'       => array(
                'member' => array(
                    'fields'  => array(
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
                        'email' => automatorwp_utilities_ajax_selector_option( array(
                            'field'          => 'email',
                            'name'           => __( 'Member email', 'automatorwp-googlegroups' ),
                            'option_none'    => false,
                            'option_default' => __( 'Select a member', 'automatorwp-googlegroups' ),
                            'action_cb'      => 'automatorwp_googlegroups_get_members',
                            'options_cb'     => 'automatorwp_googlegroups_options_members',
                            'placeholder'    => __( 'Select a member', 'automatorwp-googlegroups' ),
                            'default'        => '',
                            'required'       => true,
                        ) ),
                        'role' => array(
                            'name'     => __( 'New role', 'automatorwp-googlegroups' ),
                            'type'     => 'select',
                            'options'  => array(
                                'MEMBER'    => __( 'Member', 'automatorwp-googlegroups' ),
                                'MANAGER'   => __( 'Manager', 'automatorwp-googlegroups' ),
                                'OWNER'     => __( 'Owner', 'automatorwp-googlegroups' ),
                            ),
                            'default'  => 'MEMBER',
                            'required' => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $data = isset( $action_options['member'] ) ? $action_options['member'] : array();
        $email_raw = isset( $data['email'] ) ? trim( $data['email'] ) : '';
        $group     = isset( $data['group'] ) ? trim( $data['group'] ) : '';
        $new_role  = isset( $data['role'] ) ? $data['role'] : '';

        $email_parsed = automatorwp_parse_automation_tags( $email_raw, $user_id, $automation->id, $action->id );
        $email_final  = is_email( $email_parsed ) ? $email_parsed : ( is_email( $email_raw ) ? $email_raw : '' );

        if ( empty( $email_final ) || empty( $group ) || empty( $new_role ) ) {
            $this->result = __( 'Error: Invalid data.', 'automatorwp-googlegroups' );
            return;
        }

        $service = automatorwp_googlegroups_get_service();
        $response = $service ? $service->change_member_role( $group, $email_final, $new_role ) : 0;
        if ( $response === 200 ) {
            do_action( 'automatorwp_googlegroups_member_role_changed', $user_id, array( 'group' => $group, 'member' => $email_final, 'role' => $new_role ) );
            $this->result = __( 'Member role changed successfully.', 'automatorwp-googlegroups' );
        } else {
            $this->result = __( 'Failed to change member role.', 'automatorwp-googlegroups' );
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

new AutomatorWP_GoogleGroups_Change_Member_Role();