<?php
/**
 * BP Unverified Member Trigger
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;
 
class AutomatorWP_BP_Unverified_Member_Trigger extends AutomatorWP_Integration_Trigger {
 
    public $integration = 'bp-verified-member';
    public $trigger = 'bp_verified_member_status_unverified';
 
    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A user is unverified', 'automatorwp-bp-verified-member' ),
            'select_option' => __( 'A user is <strong>unverified</strong>', 'automatorwp-bp-verified-member' ),
            'edit_label'    => sprintf( __( 'A user is unverified %1$s time(s)', 'automatorwp-bp-verified-member' ), '{times}' ),
            'log_label'     => __( 'A user is unverified', 'automatorwp-bp-verified-member' ),
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
 
        add_action( 'updated_user_meta', array( $this, 'listener' ), 10, 4 );
 
        parent::hooks();
    }
 
    public function listener( $meta_id, $user_id, $meta_key, $meta_value ) {
 
        if ( $meta_key !== 'bp_verified_member' ) {
            return;
        }
 
        if ( ! empty( $meta_value ) ) {
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
 
new AutomatorWP_BP_Unverified_Member_Trigger();