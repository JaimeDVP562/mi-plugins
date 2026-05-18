<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_AffiliatePress_User_Earns_Commission extends AutomatorWP_Integration_Trigger {

    public $integration = 'affiliatepress';
    public $trigger     = 'affiliatepress_user_earns_commission';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'An affiliate earns a commission', 'automatorwp-affiliatepress' ),
            'select_option' => __( 'An affiliate earns a <strong>commission</strong>', 'automatorwp-affiliatepress' ),
            'edit_label'    => __( 'An affiliate earns a commission', 'automatorwp-affiliatepress' ),
            'log_label'     => __( 'An affiliate earns a commission', 'automatorwp-affiliatepress' ),
            'action'        => 'affiliatepress_after_commission_created',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(),
        ) );
    }

    public function listener( $commission_id, $commission_data ) {
        
        $commission_id = absint( $commission_id );

        if ( empty( $commission_id ) || empty( $commission_data ) ) {
            return;
        }

        $affiliate_id = 0;
        if ( isset( $commission_data['affiliate_id'] ) ) {
            $affiliate_id = absint( $commission_data['affiliate_id'] );
        } elseif ( isset( $commission_data['ap_affiliates_id'] ) ) {
            $affiliate_id = absint( $commission_data['ap_affiliates_id'] );
        } elseif ( isset( $commission_data['affiliatepress_affiliates_id'] ) ) {
            $affiliate_id = absint( $commission_data['affiliatepress_affiliates_id'] );
        }

        if ( empty( $affiliate_id ) ) {
            return; 
        }

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

new AutomatorWP_AffiliatePress_User_Earns_Commission();