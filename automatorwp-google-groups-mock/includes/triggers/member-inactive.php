<?php
/**
 * Trigger: Member inactive or bounced
 *
 * @package     AutomatorWP\GoogleGroups\Triggers
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Member_Inactive extends AutomatorWP_Integration_Trigger {

    public $integration = 'googlegroups';
    public $trigger     = 'googlegroups_member_inactive';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A member becomes inactive/bounced', 'automatorwp-googlegroups' ),
            'select_option' => __( 'A member becomes <strong>inactive/bounced</strong>', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( '%1$s inactive in %2$s', 'automatorwp-googlegroups' ), '{member}', '{group}' ),
            'log_label'     => sprintf( __( '%1$s inactive in %2$s', 'automatorwp-googlegroups' ), '{member}', '{group}' ),
            'action'        => $this->trigger,
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
        add_action( $this->trigger, array( $this, 'listener' ), 10, 2 );
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
} 