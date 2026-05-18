<?php
/**
 * Template Functions
 *
 * @package GamiPress\FluentCart\Partial_Payments\Template_Functions
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -----------------------------------------------------------------------
// Template engine registration
// -----------------------------------------------------------------------

/**
 * Register plugin template directory in GamiPress template engine
 *
 * This allows themes to override templates by placing them in:
 *   {theme}/gamipress/fluentcart-partial-payments/
 *
 * @since  1.0.0
 *
 * @param  array $file_paths  Registered template directories.
 * @return array
 */
function gamipress_fluentcart_partial_payments_template_paths( $file_paths ) {

    $file_paths[] = trailingslashit( get_stylesheet_directory() ) . 'gamipress/fluentcart-partial-payments/';
    $file_paths[] = trailingslashit( get_template_directory() )   . 'gamipress/fluentcart-partial-payments/';
    $file_paths[] = GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'templates/';

    return $file_paths;
}
add_filter( 'gamipress_template_paths', 'gamipress_fluentcart_partial_payments_template_paths' );

// -----------------------------------------------------------------------
// Checkout form rendering
// -----------------------------------------------------------------------

/**
 * Render the partial payments form at the FluentCart checkout
 *
 * @since  1.0.0
 * @return void
 */
function gamipress_fluentcart_partial_payments_form() {

    if ( ! is_user_logged_in() ) {
        return;
    }

    $prefix       = '_gamipress_fluentcart_partial_payments_';
    $points_types = array();

    foreach ( gamipress_get_points_types() as $slug => $data ) {

        if ( ! (bool) gamipress_get_post_meta( $data['ID'], $prefix . 'enable' ) ) {
            continue;
        }

        $conversion = gamipress_get_post_meta( $data['ID'], $prefix . 'conversion' );

        if ( empty( $conversion ) ) {
            continue;
        }

        $data['conversion']     = $conversion;
        $data['initial_amount'] = absint( gamipress_get_post_meta( $data['ID'], $prefix . 'initial_amount' ) );
        $data['max_amount']     = absint( gamipress_get_post_meta( $data['ID'], $prefix . 'max_amount' ) );
        $data['user_points']    = gamipress_get_user_points( get_current_user_id(), $slug );
        $points_types[ $slug ]  = $data;
    }

    // No enabled points types — nothing to render
    if ( empty( $points_types ) ) {
        return;
    }

    $amount_type = gamipress_fluentcart_partial_payments_get_option( 'amount_type', 'input' );
    $amount_step = absint( gamipress_fluentcart_partial_payments_get_option( 'amount_step', 1 ) );
    $amount_step = max( 1, $amount_step );

    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments();

    // Load template manually — gamipress_get_template_part does not extract()
    // variables into the template scope, so we do it ourselves.
    $template_vars = array(
        'points_types'     => $points_types,
        'amount_type'      => $amount_type,
        'amount_step'      => $amount_step,
        'partial_payments' => $partial_payments,
    );

    $template_file = GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_DIR . 'templates/partial-payments-form.php';

    // Allow theme override
    $theme_file = get_stylesheet_directory() . '/gamipress/fluentcart-partial-payments/partial-payments-form.php';
    if ( file_exists( $theme_file ) ) {
        $template_file = $theme_file;
    }

    if ( file_exists( $template_file ) ) {
        extract( $template_vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
        include $template_file;
    }
}

// -----------------------------------------------------------------------
// Display applied discounts in the order summary
// -----------------------------------------------------------------------

/**
 * Show applied partial payment lines in the FluentCart order summary table
 *
 * @since  1.0.0
 * @return void
 */
function gamipress_fluentcart_partial_payments_order_summary() {

    if ( ! is_user_logged_in() ) {
        return;
    }

    $partial_payments = gamipress_fluentcart_partial_payments_get_partial_payments();

    if ( empty( $partial_payments ) ) {
        return;
    }

    gamipress_get_template_part( 'partial-payments-order-summary', array(
        'partial_payments' => $partial_payments,
    ) );
}
add_action( 'fluentcart_checkout_order_summary', 'gamipress_fluentcart_partial_payments_order_summary' );

/**
 * Wrapper: only render the form when scope is 'loading' (checkout page init)
 *
 * Hook: fluent_cart/cart/cart_data_items_updated
 * Confirmed: CheckoutPageHandler.php line 67
 *
 * @since  1.0.0
 * @param  array $context  Contains 'cart', 'scope', 'scope_data'.
 * @return void
 */
function gamipress_fluentcart_partial_payments_form_on_checkout( $context ) {
    $scope = $context['scope'] ?? '';
    if ( $scope === 'loading' ) {
        gamipress_fluentcart_partial_payments_form();
    }
}