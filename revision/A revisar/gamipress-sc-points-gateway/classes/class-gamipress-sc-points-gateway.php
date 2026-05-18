<?php
/**
 * GamiPress SureCart Points Gateway
 *
 * @package GamiPress\SureCart\Points_Gateway
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'GamiPress_SC_Points_Gateway' ) ) return;

class GamiPress_SC_Points_Gateway {

    public $points_type_slug;
    public $points_type;
    public $conversion_rate;

    public function __construct( $points_type_slug, $points_type ) {

        $this->points_type_slug = $points_type_slug;
        $this->points_type      = $points_type;
        $this->id               = 'gamipress_' . $points_type_slug;

        $this->title           = get_option( 'gamipress_sc_gateway_title_' . $points_type_slug, $points_type['plural_name'] );
        $this->description     = get_option( 'gamipress_sc_gateway_description_' . $points_type_slug, sprintf( __( 'Pay using %s.', 'gamipress-sc-points-gateway' ), $points_type['plural_name'] ) );
        $this->conversion_rate = (float) get_option( 'gamipress_sc_gateway_conversion_rate_' . $points_type_slug, 100 );
    }

    public function process_surecart_payment( $order ) {

        $user_id         = absint( $order->customer->wp_user_id );
        $order_total     = $order->total_amount / 100;
        $required_points = max( 1, round( $order_total * $this->conversion_rate ) );
        $user_points     = gamipress_get_user_points( $user_id, $this->points_type_slug );

        if ( $user_points < $required_points ) {
            $message = sprintf( __( 'Insufficient %s.', 'gamipress-sc-points-gateway' ), $this->points_type['plural_name'] );
            throw new Exception( $message );
        }

        gamipress_deduct_points_to_user( $user_id, $required_points, $this->points_type_slug, array(
            'log_type' => 'points_expend',
            'reason'   => sprintf( __( '{user} expended {points} {points_type} to complete the order #%s for a new total of {total_points} {points_type}', 'gamipress-sc-points-gateway' ), $order->number )
        ) );

        gamipress_insert_user_earning( $user_id, array(
            'title'       => sprintf( __( '-%s points used on order #%s', 'gamipress-sc-points-gateway' ), $required_points, $order->number ),
            'user_id'     => $user_id,
            'post_id'     => gamipress_get_points_type_id( $this->points_type_slug ),
            'post_type'   => 'points-type',
            'points'      => $required_points,
            'points_type' => $this->points_type_slug,
            'date'        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
        ) );
    }

    public function process_surecart_refund( $order, $amount = null ) {

        $user_id = absint( $order->customer->wp_user_id );

        if ( $amount === null ) {
            $amount = $order->total_amount / 100;
        }

        if ( $amount <= 0 ) return false;

        $required_points = round( $amount * $this->conversion_rate );

        gamipress_award_points_to_user( $user_id, $required_points, $this->points_type_slug, array(
            'log_type' => 'points_earn',
            'reason'   => sprintf( __( '{user} awarded {points} {points_type} for the order #%s refund for a new total of {total_points} {points_type}', 'gamipress-sc-points-gateway' ), $order->number )
        ) );

        return true;
    }
}

function gamipress_sc_points_gateway_process_order( $order, $data = null ) {

    if ( empty( $order->status ) || $order->status !== 'paid' ) return;

    $payment_method = isset( $order->payment_method_id ) ? $order->payment_method_id : '';

    if ( strpos( $payment_method, 'gamipress_' ) !== 0 ) return;

    $points_type_slug = str_replace( 'gamipress_', '', $payment_method );
    $points_types     = gamipress_get_points_types();

    if ( ! isset( $points_types[ $points_type_slug ] ) ) return;

    $gateway = new GamiPress_SC_Points_Gateway( $points_type_slug, $points_types[ $points_type_slug ] );
    $gateway->process_surecart_payment( $order );
}
add_action( 'surecart/order_updated', 'gamipress_sc_points_gateway_process_order', 10, 2 );

function gamipress_sc_points_gateway_process_refund( $order ) {

    $payment_method = isset( $order->payment_method_id ) ? $order->payment_method_id : '';

    if( strpos( $payment_method, 'gamipress_' ) !== 0 ) return;

    $points_type_slug = str_replace( 'gamipress_', '', $payment_method );
    $points_types     = gamipress_get_points_types();

    if( ! isset( $points_types[ $points_type_slug ] ) ) return;

    $gateway = new GamiPress_SC_Points_Gateway( $points_type_slug, $points_types[ $points_type_slug ] );
    $gateway->process_surecart_refund( $order );
}
add_action( 'surecart/order_refunded', 'gamipress_sc_points_gateway_process_refund' );