<?php
/**
 * User Registers as Affiliate Trigger
 *
 * @package     AutomatorWP\Integrations\AffiliatePress\Triggers\User_Registers_Affiliate
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_AffiliatePress_User_Registers_Affiliate extends AutomatorWP_Integration_Trigger {

    public $integration = 'affiliatepress';
    public $trigger     = 'affiliatepress_user_registers_affiliate';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     * @return void
     */
    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A user registers as an affiliate', 'automatorwp-affiliatepress' ),
            'select_option' => __( 'A user registers as an <strong>affiliate</strong>', 'automatorwp-affiliatepress' ),
            'edit_label'    => __( 'A user registers as an affiliate', 'automatorwp-affiliatepress' ),
            'log_label'     => __( 'A user registers as an affiliate', 'automatorwp-affiliatepress' ),
            'action'        => 'affiliatepress_after_signup_affiliate',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(),
        ));
    }

    /**
     * Trigger listener function
     *
     * @since 1.0.0
     * @param int $affiliate_id 
     * @return void
     */
    public function listener( $affiliate_id ) {
        $affiliate_id = absint( $affiliate_id );
        if ( empty( $affiliate_id ) ) { return; }

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

new AutomatorWP_AffiliatePress_User_Registers_Affiliate();