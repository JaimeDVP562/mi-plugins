<?php
/**
 * Admin
 *
 * @package GamiPress\FluentCart\Partial_Payments\Admin
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -----------------------------------------------------------------------
// Option helper
// -----------------------------------------------------------------------

/**
 * Shortcut to get a plugin global option
 *
 * @since  1.0.0
 *
 * @param  string $option_name  Option key (without prefix).
 * @param  mixed  $default      Fallback value.
 * @return mixed
 */
function gamipress_fluentcart_partial_payments_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_fluentcart_partial_payments_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

// -----------------------------------------------------------------------
// Global settings meta box (GamiPress → Settings → Add-ons tab)
// -----------------------------------------------------------------------

/**
 * Register global settings meta box
 *
 * @since  1.0.0
 *
 * @param  array $meta_boxes
 * @return array
 */
function gamipress_fluentcart_partial_payments_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_fluentcart_partial_payments_';

    $meta_boxes['gamipress-fluentcart-partial-payments-settings'] = array(
        'title'  => gamipress_dashicon( 'chart-pie' ) . __( 'FluentCart Partial Payments', 'gamipress-fluentcart-partial-payments' ),
        'fields' => apply_filters( 'gamipress_fluentcart_partial_payments_settings_fields', array(

            $prefix . 'amount_type' => array(
                'name'    => __( 'Amount Field Type', 'gamipress-fluentcart-partial-payments' ),
                'desc'    => __( 'The points amount field type shown at checkout.', 'gamipress-fluentcart-partial-payments' )
                           . '<br>' . __( '<strong>Input:</strong> A numeric input where users type the amount.', 'gamipress-fluentcart-partial-payments' )
                           . '<br>' . __( '<strong>Slider:</strong> A slider to choose the amount.', 'gamipress-fluentcart-partial-payments' )
                           . '<br>' . __( '<strong>Fixed:</strong> Uses the "Initial Amount" value; user cannot change it.', 'gamipress-fluentcart-partial-payments' ),
                'type'    => 'select',
                'options' => array(
                    'input'  => __( 'Input',  'gamipress-fluentcart-partial-payments' ),
                    'slider' => __( 'Slider', 'gamipress-fluentcart-partial-payments' ),
                    'fixed'  => __( 'Fixed',  'gamipress-fluentcart-partial-payments' ),
                ),
                'default' => 'input',
            ),

            $prefix . 'amount_step' => array(
                'name'       => __( 'Amount Field Step', 'gamipress-fluentcart-partial-payments' ),
                'desc'       => __( 'Granularity of the amount field. A step of 5 allows 5, 10, 15 …', 'gamipress-fluentcart-partial-payments' ),
                'type'       => 'text',
                'attributes' => array(
                    'type'        => 'number',
                    'placeholder' => '0',
                    'min'         => '0',
                    'step'        => '1',
                ),
                'default' => '1',
            ),

            $prefix . 'max_discount' => array(
                'name'       => __( 'Maximum Discount Per Order', 'gamipress-fluentcart-partial-payments' ),
                'desc'       => __( 'The maximum monetary discount allowed in a single purchase. Leave 0 for unlimited.', 'gamipress-fluentcart-partial-payments' ),
                'type'       => 'text',
                'attributes' => array(
                    'type'        => 'number',
                    'placeholder' => '0',
                    'min'         => '0',
                    'step'        => '1',
                ),
            ),

            $prefix . 'max_discount_type' => array(
                'name'    => __( 'Maximum Discount Type', 'gamipress-fluentcart-partial-payments' ),
                'desc'    => __( '<strong>Flat:</strong> Fixed monetary amount. <strong>Percentage:</strong> Percentage of the order subtotal.', 'gamipress-fluentcart-partial-payments' ),
                'type'    => 'select',
                'options' => array(
                    'flat'    => __( 'Flat',       'gamipress-fluentcart-partial-payments' ),
                    'percent' => __( 'Percentage', 'gamipress-fluentcart-partial-payments' ),
                ),
                'default' => 'flat',
            ),

        ) ),
    );

    return $meta_boxes;
}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_fluentcart_partial_payments_settings_meta_boxes' );

// -----------------------------------------------------------------------
// Per-points-type meta box  (edit points-type post screen)
// -----------------------------------------------------------------------

/**
 * Register the per-points-type meta box
 *
 * @since  1.0.0
 */
function gamipress_fluentcart_partial_payments_meta_boxes() {

    // Underscore prefix hides fields from the custom-fields panel
    $prefix = '_gamipress_fluentcart_partial_payments_';

    gamipress_add_meta_box(
        'gamipress-fluentcart-partial-payments',
        __( 'FluentCart Partial Payments', 'gamipress-fluentcart-partial-payments' ),
        'points-type',
        array(

            $prefix . 'enable' => array(
                'name'    => __( 'Enable Partial Payments', 'gamipress-fluentcart-partial-payments' ),
                'desc'    => __( 'Enable this points type to be used as a partial payment method in FluentCart.', 'gamipress-fluentcart-partial-payments' ),
                'type'    => 'checkbox',
                'classes' => 'gamipress-switch',
            ),

            $prefix . 'conversion' => array(
                'name'            => __( 'Exchange Conversion', 'gamipress-fluentcart-partial-payments' ),
                'desc'            => __( 'Points to money conversion rate.', 'gamipress-fluentcart-partial-payments' ),
                // currency_symbol is resolved at render time; safe fallback if FluentCart helper is unavailable
                'currency_symbol' => function_exists( 'fluentcart_currency_symbol' ) ? fluentcart_currency_symbol() : '$',
                'type'            => 'points_rate',
            ),

            $prefix . 'initial_amount' => array(
                'name'       => __( 'Initial Amount', 'gamipress-fluentcart-partial-payments' ),
                'desc'       => __( 'Default points amount pre-filled in the checkout field.', 'gamipress-fluentcart-partial-payments' ),
                'type'       => 'text',
                'attributes' => array(
                    'type'        => 'number',
                    'placeholder' => '0',
                    'min'         => '0',
                    'step'        => '1',
                ),
            ),

            $prefix . 'max_amount' => array(
                'name'       => __( 'Maximum Amount', 'gamipress-fluentcart-partial-payments' ),
                'desc'       => __( 'Maximum points a user can apply per order. Leave 0 for no maximum.', 'gamipress-fluentcart-partial-payments' ),
                'type'       => 'text',
                'attributes' => array(
                    'type'        => 'number',
                    'placeholder' => '0',
                    'min'         => '0',
                    'step'        => '1',
                ),
                'default' => '0',
            ),

        )
    );
}
add_action( 'cmb2_admin_init', 'gamipress_fluentcart_partial_payments_meta_boxes' );

// -----------------------------------------------------------------------
// License meta box
// -----------------------------------------------------------------------

/**
 * Register license meta box
 *
 * @since  1.0.0
 *
 * @param  array $meta_boxes
 * @return array
 */
function gamipress_fluentcart_partial_payments_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-fluentcart-partial-payments-license'] = array(
        'title'  => __( 'FluentCart Partial Payments', 'gamipress-fluentcart-partial-payments' ),
        'fields' => array(
            'gamipress_fluentcart_partial_payments_license' => array(
                'name'      => __( 'License', 'gamipress-fluentcart-partial-payments' ),
                'type'      => 'edd_license',
                'file'      => GAMIPRESS_FLUENTCART_PARTIAL_PAYMENTS_FILE,
                'item_name' => 'FluentCart Partial Payments',
            ),
        ),
    );

    return $meta_boxes;
}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_fluentcart_partial_payments_licenses_meta_boxes' );

// -----------------------------------------------------------------------
// Automatic updates
// -----------------------------------------------------------------------

/**
 * Register plugin for GamiPress automatic updates
 *
 * @since  1.0.0
 *
 * @param  array $plugins
 * @return array
 */
function gamipress_fluentcart_partial_payments_automatic_updates( $plugins ) {

    $plugins['gamipress-fluentcart-partial-payments'] = __( 'FluentCart Partial Payments', 'gamipress-fluentcart-partial-payments' );

    return $plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_fluentcart_partial_payments_automatic_updates' );
