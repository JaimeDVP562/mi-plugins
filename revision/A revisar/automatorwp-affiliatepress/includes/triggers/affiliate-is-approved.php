<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_AffiliatePress_Affiliate_Approved extends AutomatorWP_Integration_Trigger {

    public $integration = 'affiliatepress';
    public $trigger     = 'affiliatepress_affiliate_approved';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'An affiliate account is approved', 'automatorwp-affiliatepress' ),
            'select_option' => __( 'An affiliate account is <strong>approved</strong>', 'automatorwp-affiliatepress' ),
            'edit_label'    => __( 'An affiliate account is approved', 'automatorwp-affiliatepress' ),
            'log_label'     => __( 'An affiliate account is approved', 'automatorwp-affiliatepress' ),
            'action'        => 'affiliatepress_after_affiliate_status_change',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 3,
            'options'       => array(),
        ) );
    }

        
    public function listener( $affiliate_id, $new_status, $old_status ) {
        $affiliate_id = absint( $affiliate_id );
        
        if ( empty( $affiliate_id ) || (int) $new_status !== 1 ) { return; }

        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliatepress_affiliates';
        $user_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ap_affiliates_user_id FROM $table_name WHERE ap_affiliates_id = %d", $affiliate_id ) );

        if ( empty( $user_id ) ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'options' => array(),
            'tags'    => array(),
        ) );
    }
}

new AutomatorWP_AffiliatePress_Affiliate_Approved();