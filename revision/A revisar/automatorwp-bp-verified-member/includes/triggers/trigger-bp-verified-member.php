<?php
/**
 * BP Verified Member Trigger
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_BP_Verified_Member_Trigger extends AutomatorWP_Integration_Trigger {

    public $integration = 'bp-verified-member';
    public $trigger = 'bp_verified_member_status_changed';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A user is verified', 'automatorwp-bp-verified-member' ),
            'select_option' => __( 'A user is <strong>verified</strong>', 'automatorwp-bp-verified-member' ),
            'edit_label'    => sprintf( __( 'A user is verified %1$s time(s)', 'automatorwp-bp-verified-member' ), '{times}' ),
            'log_label'     => __( 'A user is verified', 'automatorwp-bp-verified-member' ),
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_times_tag()
            )
        ) );
    }

    public function hooks() {
        add_filter( 'automatorwp_user_deserves_trigger_' . $this->trigger, array( $this, 'user_deserves_trigger' ), 10, 6 );
        
        add_action( 'added_user_meta', array( $this, 'listener' ), 10, 4 );
        add_action( 'updated_user_meta', array( $this, 'listener' ), 10, 4 );

        parent::hooks();
    }

    public function listener( $meta_id, $user_id, $meta_key, $meta_value ) {
        
        if ( $meta_key !== 'bp_verified_member' ) {
            return;
        }

        if ( empty( $meta_value ) ) {
            return;
        }

        if ( empty( $user_id ) ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => absint( $user_id ),
        ) );
    }

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        return $deserves_trigger;
    }
}

new AutomatorWP_BP_Verified_Member_Trigger();