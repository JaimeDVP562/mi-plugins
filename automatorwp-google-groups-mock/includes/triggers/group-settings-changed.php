<?php
/**
 * Trigger: Group settings changed
 *
 * @package     AutomatorWP\GoogleGroups\Triggers
 * @since       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_GoogleGroups_Group_Settings_Changed extends AutomatorWP_Integration_Trigger {

    public $integration = 'googlegroups';
    public $trigger     = 'googlegroups_group_settings_changed';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'Google Group settings changed', 'automatorwp-googlegroups' ),
            'select_option' => __( 'Google Group <strong>settings</strong> changed', 'automatorwp-googlegroups' ),
            'edit_label'    => sprintf( __( 'Settings changed for %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'log_label'     => sprintf( __( 'Settings changed for %1$s', 'automatorwp-googlegroups' ), '{group}' ),
            'action'        => $this->trigger,
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(),
            'tags'          => array(
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
        $group = isset( $payload['group'] ) ? sanitize_text_field( $payload['group'] ) : '';
        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'fields'  => array(
                'group' => $group,
            ),
        ) );
    }
} 