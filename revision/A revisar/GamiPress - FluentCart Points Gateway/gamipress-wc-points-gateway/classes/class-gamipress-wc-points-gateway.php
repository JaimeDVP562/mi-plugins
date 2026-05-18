<?php
/**
 * GamiPress WooCommerce Points Gateway
 *
 * @package GamiPress\WooCommerce\Points_Gateway\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'GamiPress_WC_Points_Gateway' ) ) return;

class GamiPress_WC_Points_Gateway extends WC_Payment_Gateway {

    /**
     * @var string
     */
    public $points_type_slug;

    /**
     * @var array
     */
    public $points_type;

    /**
     * @var float
     */
    public $conversion_rate;

    /**
     * GamiPress_WC_Points_Gateway constructor.
     *
     * @since 1.0.0
     *
     * @param string $points_type_slug
     * @param array $points_type
     */
    public function __construct( $points_type_slug, $points_type ) {

        $this->points_type_slug     = $points_type_slug;
        $this->points_type          = $points_type;
        $this->id                   = 'gamipress_' . $points_type_slug;
        $this->icon                 = '';
        $this->has_fields           = true;
        $this->method_title         = $points_type['plural_name'];
        $this->method_description   = sprintf( __( 'Let users pay using %s.', 'gamipress-wc-points-gateway' ), $points_type['plural_name'] );
        $this->supports             = array(
            'products',
            'refunds'
        );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title                = $this->get_option( 'title' );
        $this->description          = $this->get_option( 'description' );
        $this->conversion_rate      = (float) $this->get_option( 'conversion_rate' );

        // Hooks
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Initialise settings form fields.
     *
     * @since 1.0.0
     */
    public function init_form_fields() {

        $this->form_fields = apply_filters( 'gamipress_wc_points_gateway_form_fields', array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'gamipress-wc-points-gateway' ),
                'type'    => 'checkbox',
                'label'   => sprintf( __( 'Enable GamiPress: %s Gateway', 'gamipress-wc-points-gateway' ), $this->points_type['plural_name'] ),
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => __( 'Title', 'gamipress-wc-points-gateway' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'gamipress-wc-points-gateway' ),
                'default'     => $this->points_type['plural_name'],
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'gamipress-wc-points-gateway' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => __( 'This controls the description which the user sees during checkout.', 'gamipress-wc-points-gateway' ),
                'default'     => sprintf( __( "Pay using %s.", 'gamipress-wc-points-gateway' ), $this->points_type['plural_name'] ),
            ),
            'conversion_rate' => array(
                'title'       => __( 'Exchange Conversion', 'gamipress-wc-points-gateway' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( '%1$s to %2$s conversion rate, used when user exchange %1$s as %2$s. This controls define how much is 1 %2$s worth in %1$s.', 'gamipress-wc-points-gateway' ), $this->points_type['plural_name'], get_woocommerce_currency_symbol() ),
                'default'     => '100',
            ),
        ) );

    }

    /**
     * Process Payment.
     *
     * @since 1.0.0
     *
     * @param int $order_id
     *
     * @return array
     */
    public function process_payment( $order_id ) {

        global $woocommerce;

        // Make sure we are still logged in
        if ( ! is_user_logged_in() ) {
            wc_add_notice( sprintf( __( 'You must be logged in to pay with %s.', 'gamipress-wc-points-gateway' ), $this->points_type['plural_name'] ), 'error' );
            return;
        }

        // Setup vars
        $order = wc_get_order( $order_id );
        $user_id = absint( ( ( version_compare( $woocommerce->version, '3.0', '>=' ) ) ? $order->get_user_id() : $order->customer_id ) );
        $order_total = ( version_compare( $woocommerce->version, '3.0', '>=' ) ) ? $order->get_total() : $order->order_total;

        // Setup points vars
        $user_points = gamipress_get_user_points( $user_id, $this->points_type_slug );
        $required_points = round( $order_total * $this->conversion_rate );

        // Check if user has the required amount of points
        if ( $user_points < $required_points ) {
            $message = sprintf( __( 'Insufficient %s.', 'gamipress-wc-points-gateway' ), $this->points_type['plural_name'] );
			throw new Exception( $message );
        }

        // Check if required points are close to 0
        if ( $required_points < 1 ){
            $required_points= 1;
        }

        // Deduct points to the customer
        gamipress_deduct_points_to_user( $user_id, $required_points, $this->points_type_slug, array(
            'log_type' => 'points_expend',
            'reason' => sprintf( __( '{user} expended {points} {points_type} to complete the order #%s for a new total of {total_points} {points_type}', 'gamipress-wc-points-gateway' ), $order->get_order_number() )
        ) );

        // Insert the user points deduction for buying product
        gamipress_insert_user_earning( $user_id, array(
            'title'	        => sprintf( __( '-%s points used on order #%s', 'gamipress-wc-points-gateway' ), $required_points, $order->get_order_number() ),
            'user_id'	    => $user_id,
            'post_id'	    => gamipress_get_points_type_id( $this->points_type_slug ),
            'post_type' 	=> 'points-type',
            'points'	    => $required_points,
            'points_type'	=> $this->points_type_slug,
            'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
        ) );

        foreach ( $order->get_items() as $item ) {

            $product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;

            if( $product ) {

                $vendor_id = absint( get_post_field( 'post_author', $product->get_id() ) );

                if( $vendor_id !== 0 ) {

                    $item_points = ( $item->get_total() * $this->conversion_rate );

                    $award_points_to_vendor = (bool) ($vendor_id !== $user_id);

                    /**
                     * Filter to decide if should award points to vendor
                     *
                     * @since 1.0.0
                     *
                     * @param bool $award_points_to_vendor
                     * @param int $vendor_id
                     * @param int $user_id
                     * @param int $order_id
                     *
                     * @return bool
                     */
                    $award_points_to_vendor = apply_filters( 'gamipress_wc_points_gateway_award_points_to_vendor', $award_points_to_vendor, $vendor_id, $user_id, $order_id );

                    if( $award_points_to_vendor ) {
                        // Award points to each product vendor
                        gamipress_award_points_to_user( $vendor_id, $item_points, $this->points_type_slug, array(
                            'log_type' => 'points_earn',
                            'reason' => sprintf( __( '{user} earned {points} {points_type} for sell %s through {points_type} for a new total of {total_points} {points_type}', 'gamipress-wc-points-gateway' ), $product->get_name() )
                        ) );

                        // Insert the vendor user earning for selling product
                        gamipress_insert_user_earning( $vendor_id, array(
                            'title'	        => sprintf( __( '+%s points for selling product %s', 'gamipress-wc-points-gateway' ), $item_points, $product->get_name() ),
                            'user_id'	    => $vendor_id,
                            'post_id'	    => gamipress_get_points_type_id( $this->points_type_slug ),
                            'post_type' 	=> 'points-type',
                            'points'	    => $item_points,
                            'points_type'	=> $this->points_type_slug,
                            'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
                        ) );
                    }

                }

            }

        }

        $order->payment_complete();

        $order->add_order_note( sprintf( __( 'Payment completed: %d %s deducted to user.', 'gamipress-wc-points-gateway' ), $required_points, $this->points_type['plural_name'] ) );

        // Return a success
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order )
        );

    }

    /**
     * Process refund.
     *
     * @since 1.0.0
     *
     * @param  int $order_id
     * @param  float $amount
     * @param  string $reason
     *
     * @return boolean True or false based on success, or a WP_Error object.
     */
    public function process_refund( $order_id, $amount = null, $reason = '' ) {

        global $woocommerce;

        // Setup vars
        $order = wc_get_order( $order_id );
        $user_id = ( version_compare( $woocommerce->version, '3.0', '>=' ) ) ? $order->get_user_id() : $order->customer_id;

        // If not is a partial refund, then get the order total
        if( $amount === null ) {

            $amount = ( version_compare( $woocommerce->version, '3.0', '>=' ) ) ? $order->get_total() : $order->order_total;

            $order_note = __( 'Order refunded: %d %s refunded to user.', 'gamipress-wc-points-gateway' );

        } else {

            $order_note = __( 'Order partially refunded: %d %s refunded to user.', 'gamipress-wc-points-gateway' );

        }

        // Return if amount is negative or 0
        if( $amount <= 0 ) {
            return false;
        }

        // Setup points vars
        $required_points = ( $amount * $this->conversion_rate );

        // Refund points to the customer
        gamipress_award_points_to_user( $user_id, $required_points, $this->points_type_slug, array(
            'log_type' => 'points_earn',
            'reason' => sprintf( __( '{user} awarded {points} {points_type} for the order #%s refund for a new total of {total_points} {points_type}', 'gamipress-wc-points-gateway' ), $order->get_order_number() )
        ) );

        foreach ( $order->get_items() as $item ) {

            $product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;

            if( $product ) {

                $vendor_id = absint( get_post_field( 'post_author', $product->get_id() ) );

                if( $vendor_id !== 0 ) {

                    $item_points = ( $item->get_total() * $this->conversion_rate );

                    // Deduct points to each product vendor
                    gamipress_deduct_points_to_user( $vendor_id, $item_points, $this->points_type_slug, array(
                        'log_type' => 'points_deduct',
                        'reason' => sprintf( __( '{user} deducted {points} {points_type} for the order #%s refund that has the product %s for a new total of {total_points} {points_type}', 'gamipress-wc-points-gateway' ), $order->get_order_number(), $product->get_name() )
                    ) );

                }

            }

        }

        $order->add_order_note( sprintf( $order_note, $required_points, $this->points_type['plural_name'] ) );

        return true;

    }

}