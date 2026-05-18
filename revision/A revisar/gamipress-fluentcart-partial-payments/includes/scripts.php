<?php
/**
 * Scripts
 *
 * @package GamiPress\FluentCart\Partial_Payments\Scripts
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -----------------------------------------------------------------------
// Frontend scripts
// -----------------------------------------------------------------------

/**
 * Register frontend scripts (checkout page)
 *
 * @since  1.0.0
 * @return void
 */
function gamipress_fluentcart_partial_payments_register_scripts() {

    // No minified version — always load the full file
    wp_register_script(
        'gamipress-fluentcart-partial-payments-checkout-js',
        GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_URL . 'assets/js/gamipress-fluentcart-partial-payments-checkout.js',
        array( 'jquery' ),
        GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_VER,
        true
    );
}
add_action( 'init', 'gamipress_fluentcart_partial_payments_register_scripts' );

/**
 * Enqueue and localise the checkout script on FluentCart checkout pages
 *
 * @since  1.0.0
 * @return void
 */
function gamipress_fluentcart_partial_payments_enqueue_scripts() {

    if ( ! function_exists( 'FluentCart' ) ) {
        return;
    }

    $is_checkout = apply_filters( 'gamipress_fluentcart_partial_payments_is_checkout', false );

    if ( ! $is_checkout ) {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && (
            has_shortcode( $post->post_content, 'fluent_cart_checkout' )
            || has_block( 'fluent-cart/checkout', $post )
        ) ) {
            $is_checkout = true;
        }
    }

    if ( ! $is_checkout ) {
        return;
    }

    // Build points types data for JS
    $points_types = array();
    $prefix       = '_gamipress_fluentcart_partial_payments_';

    foreach ( gamipress_get_points_types() as $slug => $data ) {

        if ( ! (bool) gamipress_get_post_meta( $data['ID'], $prefix . 'enable' ) ) {
            continue;
        }

        $conversion = gamipress_get_post_meta( $data['ID'], $prefix . 'conversion' );

        if ( empty( $conversion ) ) {
            continue;
        }

        $data['conversion'] = $conversion;
        $points_types[ $slug ] = $data;
    }

    wp_localize_script(
        'gamipress-fluentcart-partial-payments-checkout-js',
        'gamipress_fluentcart_partial_payments',
        array(
            'ajaxurl'            => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
            'nonce'              => wp_create_nonce( 'gamipress_fluentcart_partial_payments' ),
            'points_types'       => $points_types,
            'decimals'           => 2,
            'decimal_separator'  => '.',
            'thousand_separator' => ',',
            'remove_label'       => __( '[Remove]', 'gamipress-fluentcart-partial-payments' ),
        )
    );

    wp_enqueue_script( 'gamipress-fluentcart-partial-payments-checkout-js' );
}
add_action( 'wp_enqueue_scripts', 'gamipress_fluentcart_partial_payments_enqueue_scripts', 100 );

// -----------------------------------------------------------------------
// Admin scripts
// -----------------------------------------------------------------------

/**
 * Register admin scripts (points-type edit screen)
 *
 * @since  1.0.0
 * @return void
 */
function gamipress_fluentcart_partial_payments_admin_register_scripts() {

    // No minified version — always load the full file
    wp_register_script(
        'gamipress-fluentcart-partial-payments-admin-js',
        GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_URL . 'assets/js/gamipress-fluentcart-partial-payments-admin.js',
        array( 'jquery', 'jquery-ui-sortable' ),
        GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_VER,
        true
    );
}
add_action( 'admin_init', 'gamipress_fluentcart_partial_payments_admin_register_scripts' );

/**
 * Enqueue admin scripts on relevant pages
 *
 * @since  1.0.0
 *
 * @param  string $hook  Current admin page hook.
 * @return void
 */
function gamipress_fluentcart_partial_payments_admin_enqueue_scripts( $hook ) {

    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }

    global $post;

    if ( ! is_a( $post, 'WP_Post' ) || $post->post_type !== 'points-type' ) {
        return;
    }

    wp_enqueue_script( 'gamipress-fluentcart-partial-payments-admin-js' );
}
add_action( 'admin_enqueue_scripts', 'gamipress_fluentcart_partial_payments_admin_enqueue_scripts', 100 );