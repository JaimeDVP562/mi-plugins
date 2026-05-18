<?php
/**
 * Points Checkout Template for FluentCart
 *
 * This template can be overridden by copying it to
 * yourtheme/gamipress/fluentcart-points-gateway/fc-points-checkout.php
 *
 * @package GamiPress\FluentCart\Points_Gateway\Templates
 * @since 1.0.0
 */

global $gamipress_fluentcart_points_gateway_template_args;

// Shorthand
$a = $gamipress_fluentcart_points_gateway_template_args;

$points_types = gamipress_get_points_types();

// Default points type
$points_types[''] = array(
    'singular_name' => __( 'Point', 'gamipress' ),
    'plural_name'   => __( 'Points', 'gamipress' ),
);

$points_type = $points_types[ $a['points_type'] ];

// ----------------------------------
// User points
// ----------------------------------

$user_points_label = sprintf( __( 'Current %s:', 'gamipress-fluentcart-points-gateway' ), $points_type['plural_name'] );

/**
 * Filter the user points label
 *
 * @since 1.0.0
 *
 * @param string $user_points_label
 * @param string $points_type
 * @param array  $template_args
 */
$user_points_label = apply_filters( 'gamipress_fluentcart_points_gateway_checkout_user_points_label', $user_points_label, $a['points_type'], $a );

/**
 * Filter the user points
 *
 * @since 1.0.0
 *
 * @param int    $user_points
 * @param string $points_type
 * @param array  $template_args
 */
$user_points = apply_filters( 'gamipress_fluentcart_points_gateway_checkout_user_points', $a['user_points'], $a['points_type'], $a );

// ----------------------------------
// Required points
// ----------------------------------

$cart_points_label = sprintf( __( 'Required %s:', 'gamipress-fluentcart-points-gateway' ), $points_type['plural_name'] );

/**
 * Filter the required points label
 *
 * @since 1.0.0
 *
 * @param string $required_points_label
 * @param string $points_type
 * @param array  $template_args
 */
$cart_points_label = apply_filters( 'gamipress_fluentcart_points_gateway_checkout_cart_points_label', $cart_points_label, $a['points_type'], $a );

/**
 * Filter the required points
 *
 * @since 1.0.0
 *
 * @param int    $required_points
 * @param string $points_type
 * @param array  $template_args
 */
$cart_points = apply_filters( 'gamipress_fluentcart_points_gateway_checkout_cart_points', $a['cart_points'], $a['points_type'], $a );

// ----------------------------------
// Points after purchase
// ----------------------------------

$new_points_balance_label = sprintf( __( '%s after purchase:', 'gamipress-fluentcart-points-gateway' ), $points_type['plural_name'] );

/**
 * Filter the points after purchase label
 *
 * @since 1.0.0
 *
 * @param string $new_points_balance_label
 * @param string $points_type
 * @param array  $template_args
 */
$new_points_balance_label = apply_filters( 'gamipress_fluentcart_points_gateway_checkout_new_points_balance_label', $new_points_balance_label, $a['points_type'], $a );

/**
 * Filter the points after purchase
 *
 * @since 1.0.0
 *
 * @param int    $new_points_balance
 * @param string $points_type
 * @param array  $template_args
 */
$new_points_balance = apply_filters( 'gamipress_fluentcart_points_gateway_checkout_new_points_balance', $a['user_points'] - $a['cart_points'], $a['points_type'], $a );
?>

<?php do_action( 'gamipress_fluentcart_points_gateway_checkout_before', $a ); ?>

<div class="gamipress-fluentcart-points-gateway-checkout-details" data-points-type="<?php echo esc_attr( $a['points_type'] ); ?>">

    <div class="gamipress-fc-points-row gamipress-fc-user-balance">
        <span class="gamipress-fc-points-label"><?php echo esc_html( $user_points_label ); ?></span>
        <span class="gamipress-fc-points-value"><?php echo $user_points; ?></span>
    </div>

    <div class="gamipress-fc-points-row gamipress-fc-required-balance">
        <span class="gamipress-fc-points-label"><?php echo esc_html( $cart_points_label ); ?></span>
        <span class="gamipress-fc-points-value"><?php echo $cart_points; ?></span>
    </div>

    <div class="gamipress-fc-points-row gamipress-fc-new-balance">
        <span class="gamipress-fc-points-label"><?php echo esc_html( $new_points_balance_label ); ?></span>
        <?php if ( $new_points_balance < 0 ) : ?>
            <span class="gamipress-fc-points-value gamipress-fc-points-negative"><?php echo $new_points_balance; ?></span>
        <?php else : ?>
            <span class="gamipress-fc-points-value"><?php echo $new_points_balance; ?></span>
        <?php endif; ?>
    </div>

</div>

<?php do_action( 'gamipress_fluentcart_points_gateway_checkout_after', $a ); ?>
