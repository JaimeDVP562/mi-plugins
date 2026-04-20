<?php
/**
 * Trigger: Group membership limit reached
 *
 * @package     AutomatorWP\GoogleGroups\Triggers
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Membership_Limit_Reached extends AutomatorWP_Integration_Trigger {

    public $integration = 'googlegroups';
    public $trigger     = 'googlegroups_membership_limit_reached';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'Group membership limit reached', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Group <strong>membership limit</strong> is reached', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Limit reached for %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'log_label'     => sprintf( __( 'Limit reached for %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'action'        => $this->trigger,
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(
                'threshold' => array(
                    'name'     => __( 'Threshold', 'automatorwp-googlegroups' ),
                    'type'     => 'number',
                    'required' => true,
                ),
            ),
            'tags'          => array(
                'group' => array(
                    'label' => __( 'Group Email', 'automatorwp-googlegroups' ),
                    'type'  => 'text',
                ),
                'count' => array(
                    'label' => __( 'Current member count', 'automatorwp-googlegroups' ),
                    'type'  => 'number',
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
        $group = isset( $payload['group'] ) ? sanitize_text_field( $payload['group'] ) : '';
        $count = isset( $payload['count'] ) ? absint( $payload['count'] ) : 0;
        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'fields'  => array(
                'group' => $group,
                'count' => $count,
            ),
        ) );
    }
}