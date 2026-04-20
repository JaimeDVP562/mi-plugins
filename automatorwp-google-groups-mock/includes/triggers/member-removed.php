<?php
/**
 * Trigger: Member Removed from Google Group
 *
 * @package     AutomatorWP\GoogleGroups\Triggers
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Member_Removed extends AutomatorWP_Integration_Trigger {

    public $integration = 'googlegroups';
    public $trigger     = 'googlegroups_member_removed';

    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A member is removed from a Google Group', 'automatorwp-googlegroups' ),
            'select_option' => __( 'A member is <strong>removed</strong> from a Google Group', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Member %1$s removed from group %2$s', 'automatorwp-googlegroups' ), '{member}', '{group}' ),
            'log_label'     => sprintf( __( 'Member %1$s removed from group %2$s', 'automatorwp-googlegroups' ), '{member}', '{group}' ),
            'action'        => 'automatorwp_googlegroups_member_removed',
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(),
            'tags'          => array(
                'member' => array(
                    'label' => __( 'Member Email', 'automatorwp-googlegroups' ),
                    'type'  => 'text',
                ),
                'group' => array(
                    'label' => __( 'Group Email', 'automatorwp-googlegroups' ),
                    'type'  => 'text',
                ),
            ),
        ) );

    }

    public function hooks() {
        add_action( 'automatorwp_googlegroups_member_removed', array( $this, 'listener' ), 10, 2 );
        parent::hooks();
    }

    public function listener( $user_id, $payload ) {

        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return;
        }

        $member = isset( $payload['member'] ) ? sanitize_email( $payload['member'] ) : '';
        $group  = isset( $payload['group'] ) ? sanitize_text_field( $payload['group'] ) : '';

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'fields'  => array(
                'member' => $member,
                'group'  => $group,
            ),
        ) );

    }

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        return $deserves_trigger;
    }

}

// Initialize the trigger class
new AutomatorWP_GoogleGroups_Member_Removed();
