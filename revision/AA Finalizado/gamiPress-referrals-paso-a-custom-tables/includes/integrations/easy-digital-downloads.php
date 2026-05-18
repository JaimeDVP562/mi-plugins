<?php
/**
 * Easy Digital Downloads
 *
 * @package     GamiPress\Referrals\Integrations\Easy_Digital_Downloads
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Requirements on multisite install
if( is_multisite() && function_exists( 'gamipress_is_network_wide_active' ) && gamipress_is_network_wide_active() && is_main_site() ) {
    // On main site, need to check if integrated plugin is installed on any sub site to load all configuration files
    if( ! gamipress_is_plugin_active_on_network( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
        return;
    }
} else if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
    return;
}

class GamiPress_Referrals_Easy_Digital_Downloads_Integration {

    private $name = 'Easy Digital Downloads';
    private $integration = 'easy_digital_downloads';

    public function __construct() {

        // Enable referral sales
        add_filter( 'gamipress_referrals_enable_sales', '__return_true', 10 );

        // Listen for order status changes
        add_action( 'edd_update_payment_status', array( $this, 'status_listener' ), 10, 3 );

        // Listen for new purchases
        add_action( 'edd_after_payment_actions', array( $this, 'sale' ) );

        // Listen for refunds
        add_action( 'edd_post_refund_payment', array( $this, 'sale_refund' ) );

        // Sale commission box
        add_action( 'cmb2_admin_init', array( $this, 'sale_commission_box' ) );

        // Sale total
        add_action( "gamipress_referrals_{$this->integration}_sale_total", array( $this, 'sale_total' ), 10, 4 );
        add_action( "gamipress_referrals_{$this->integration}_sale_total_formatted", array( $this, 'sale_total_formatted' ), 10, 5 );
    }

    // Order status listener
    public function status_listener( $payment_id , $to, $from ) {

        if( $from !== 'completed' && $to === 'completed' )
            $this->sale( $payment_id );

        if( $from !== 'refunded' && $to === 'refunded' )
            $this->sale_refund( $payment_id );

    }

    // Sale referral
    public function sale( $payment_id ) {

        $payment = edd_get_payment( $payment_id );

        if( ! $payment ) return;

        $user_id = $payment->user_id;

        $cart_details = $payment->cart_details;

        // Bail if cart is not well setup
        if ( is_array( $cart_details ) ) {

            // On purchase, trigger events on each download purchased
            foreach ( $cart_details as $index => $item ) {

                // Setup vars
                $download_id = $item['id'];
                $quantity = isset( $item['quantity'] ) ? absint( $item['quantity'] ) : 1;

                // Trigger events same times as item quantity
                for ( $i = 0; $i < $quantity; $i++ ) {
                    gamipress_referrals_product_sale_listener( $payment_id, $download_id, $user_id, $this->integration );
                }

            }
        }

        gamipress_referrals_sale_listener( $payment->ID, $user_id, $this->integration );

    }

    // Sale refund referral
    public function sale_refund( $payment_id ) {

        $payment = edd_get_payment( $payment_id );

        if( ! $payment ) return;

        $user_id = $payment->user_id;

        gamipress_referrals_sale_refund_listener( $payment->ID, $user_id, $this->integration );

    }

    // Sale commission box
    public function sale_commission_box() {
        gamipress_referrals_sale_commission_box( $this->name, $this->integration );
    }

    // Sale total
    public function sale_total( $sale_total, $affiliate_id, $referral_id, $sale_id ) {

        $payment = edd_get_payment( $sale_id );

        if( ! $payment ) return $sale_total;

        return $payment->total;

    }

    // Sale total formatted
    public function sale_total_formatted( $sale_total_formatted, $sale_total, $affiliate_id, $referral_id, $sale_id ) {

        return strip_tags( edd_currency_filter( edd_format_amount( $sale_total ), edd_get_currency() ) );

    }

}

new GamiPress_Referrals_Easy_Digital_Downloads_Integration();