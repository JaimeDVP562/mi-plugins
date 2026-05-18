<?php
/**
 * Template Functions
 *
 * @package     GamiPress\FluentCart\Points_Gateway\Template_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin templates directory on GamiPress template engine
 *
 * @since 1.0.0
 *
 * @param array $file_paths
 *
 * @return array
 */
function gamipress_fluentcart_points_gateway_template_paths( $file_paths ) {

    $file_paths[] = trailingslashit( get_stylesheet_directory() ) . 'gamipress/fluentcart-points-gateway/';
    $file_paths[] = trailingslashit( get_template_directory() ) . 'gamipress/fluentcart-points-gateway/';
    $file_paths[] = GAMIPRESS_FC_POINTS_GATEWAY_DIR . 'templates/';

    return $file_paths;
}
add_filter( 'gamipress_template_paths', 'gamipress_fluentcart_points_gateway_template_paths' );

/**
 * Display points balance information on FluentCart checkout
 *
 * Adds a REST API endpoint that FluentCart's checkout can call
 * to get user points information.
 *
 * @since 1.0.0
 */
function gamipress_fluentcart_points_gateway_register_rest_routes() {

    register_rest_route( 'gamipress-fc-points/v1', '/checkout-info', array(
        'methods'             => 'GET',
        'callback'            => 'gamipress_fluentcart_points_gateway_checkout_info',
        'permission_callback' => function() {
            return is_user_logged_in();
        },
    ) );
}
add_action( 'rest_api_init', 'gamipress_fluentcart_points_gateway_register_rest_routes' );

/**
 * REST API callback for checkout info
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request
 *
 * @return WP_REST_Response
 */
function gamipress_fluentcart_points_gateway_checkout_info( $request ) {

    $user_id      = get_current_user_id();
    $points_types = gamipress_get_points_types();
    $data         = array();

    foreach ( $points_types as $slug => $points_type ) {

        $user_points     = absint( gamipress_get_user_points( $user_id, $slug ) );
        $conversion_rate = gamipress_fluentcart_points_gateway_get_conversion_rate( $slug );

        $data[ $slug ] = array(
            'points_type'     => $slug,
            'singular_name'   => $points_type['singular_name'],
            'plural_name'     => $points_type['plural_name'],
            'user_points'     => $user_points,
            'conversion_rate' => $conversion_rate,
            'gateway_id'      => 'gamipress_' . $slug,
        );
    }

    return new WP_REST_Response( $data, 200 );
}

/**
 * Add points information to FluentCart checkout data
 *
 * Hooks into FluentCart's checkout data to inject points balance information
 *
 * @since 1.0.0
 *
 * @param array $checkout_data
 *
 * @return array
 */
function gamipress_fluentcart_points_gateway_checkout_data( $checkout_data ) {

    if ( ! is_user_logged_in() ) {
        return $checkout_data;
    }

    $user_id      = get_current_user_id();
    $points_types = gamipress_get_points_types();

    $points_info = array();

    foreach ( $points_types as $slug => $points_type ) {

        $user_points     = absint( gamipress_get_user_points( $user_id, $slug ) );
        $conversion_rate = gamipress_fluentcart_points_gateway_get_conversion_rate( $slug );

        $points_info[ 'gamipress_' . $slug ] = array(
            'user_points'     => $user_points,
            'plural_name'     => $points_type['plural_name'],
            'conversion_rate' => $conversion_rate,
        );
    }

    $checkout_data['gamipress_points'] = $points_info;

    return $checkout_data;
}
add_filter( 'fluent_cart/checkout_data', 'gamipress_fluentcart_points_gateway_checkout_data' );

/**
 * Display points info on order confirmation / thank you page
 *
 * @since 1.0.0
 *
 * @param object $order
 */
function gamipress_fluentcart_points_gateway_order_confirmation( $order ) {

    $payment_method = $order->payment_method ?? '';

    if ( empty( $payment_method ) || strpos( $payment_method, 'gamipress_' ) !== 0 ) {
        return;
    }

    $chosen_points_type = str_replace( 'gamipress_', '', $payment_method );
    $points_types       = gamipress_get_points_types();

    if ( ! isset( $points_types[ $chosen_points_type ] ) ) {
        return;
    }

    $points_type     = $points_types[ $chosen_points_type ];
    $points_deducted = $order->getMeta( 'gamipress_points_deducted' );

    if ( $points_deducted ) {
        printf(
            '<div class="gamipress-fluentcart-order-points"><strong>%s:</strong> %d %s</div>',
            __( 'Points Used', 'gamipress-fluentcart-points-gateway' ),
            $points_deducted,
            esc_html( $points_type['plural_name'] )
        );
    }
}
add_action( 'fluent_cart/order_confirmation_after_details', 'gamipress_fluentcart_points_gateway_order_confirmation' );
