<?php
/**
 * Action: Add Member to Google Group
 *
 * @package     AutomatorWP\GoogleGroups\Actions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Add_Member extends AutomatorWP_Integration_Action {

    public $integration = 'googlegroups';
    public $action      = 'googlegroups_add_member';

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add a member to a Google Group', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Add a <strong>member</strong> to a Google Group', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Add member %1$s to group %2$s', 'automatorwp-googlegroups' ), '{member_email}', '{group}' ),
            'log_label'     => sprintf( __( 'Added member %1$s to group %2$s', 'automatorwp-googlegroups' ), '{member_email}', '{group}' ),
            'options'       => array(
                // group selector at top level
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
                // email input for the member to add
                'member_email' => array(
                    'name'          => __( 'Email', 'automatorwp-googlegroups' ),
                    'type'          => 'text',
                    'required'      => true,
                    'supports_tags' => true,
                ),
            ),
        ) );

    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        // Read top-level options: 'group' selector and 'member_email' input
        $group     = isset( $action_options['group'] ) ? trim( $action_options['group'] ) : '';
        $email_raw = isset( $action_options['member_email'] ) ? trim( $action_options['member_email'] ) : '';

        $email_parsed = automatorwp_parse_automation_tags( $email_raw, $user_id, $automation->id, $action->id );
        $email_final  = is_email( $email_parsed ) ? $email_parsed : ( is_email( $email_raw ) ? $email_raw : '' );

        if ( empty( $email_final ) || empty( $group ) ) {
            $this->result = __( 'Error: Invalid data.', 'automatorwp-googlegroups' );
            return;
        }

        // use the service abstraction directly; the implementation (fake or real)
        // is picked by googlegroups_get_service().
        $service = automatorwp_googlegroups_get_service();
        $response = $service ? $service->add_member( $group, $email_final ) : 0;

        if ( in_array( $response, array( 200 ), true ) ) {

            do_action( 'automatorwp_googlegroups_member_added', $user_id, array( 'group' => $group, 'member' => $email_final ) );
            $this->result = __( 'Member added to Google Group successfully.', 'automatorwp-googlegroups' );

        } elseif ( $response === 409 ) {

            // Member already exists - treat as success by default
            do_action( 'automatorwp_googlegroups_member_added', $user_id, array( 'group' => $group, 'member' => $email_final ) );
            $this->result = __( 'Member already exists in the Google Group (treated as success).', 'automatorwp-googlegroups' );

        } else {
            $this->result = __( 'Failed to add member to Google Group.', 'automatorwp-googlegroups' );
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
new AutomatorWP_GoogleGroups_Add_Member();

