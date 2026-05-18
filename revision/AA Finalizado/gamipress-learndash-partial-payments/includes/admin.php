<?php
/**
 * Admin
 *
 * @package GamiPress\LearnDash\Partial_Payments\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function gamipress_ld_partial_payments_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_ld_partial_payments_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * Plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_ld_partial_payments_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_ld_partial_payments_';

    $meta_boxes['gamipress-wc-partial-payments-settings'] = array(
        'title' => gamipress_dashicon( 'chart-pie' ) . __( 'LearnDash Partial Payments', 'gamipress-wc-partial-payments' ),
        'fields' => apply_filters( 'gamipress_ld_partial_payments_settings_fields', array(
            $prefix . 'amount_type' => array(
                'name' => __( 'Amount Field Type', 'gamipress-wc-partial-payments' ),
                'desc' => __( 'The points amount field type. Available options:', 'gamipress-wc-partial-payments' )
                    . '<br>' . __( '<strong>Input:</strong> A numeric input field where users can type the desired amount.', 'gamipress-wc-partial-payments' )
                    . '<br>' . __( '<strong>Slider:</strong> A slider field where users can choose the desired amount.', 'gamipress-wc-partial-payments' )
                    . '<br>' . __( '<strong>Fixed:</strong> A fixed amount that users won\'t be able to change. The points amount will be the points type\'s "Initial Amount" field value.', 'gamipress-wc-partial-payments' ),
                'type' => 'select',
                'options' => array(
                    'input'     => __( 'Input', 'gamipress-wc-partial-payments' ),
                    'slider'    => __( 'Slider', 'gamipress-wc-partial-payments' ),
                    'fixed'     => __( 'Fixed', 'gamipress-wc-partial-payments' ),
                ),
                'default' => 'input',
            ),
            $prefix . 'amount_step' => array(
                'name' => __( 'Amount Field Step', 'gamipress-wc-partial-payments' ),
                'desc' => __( 'The points amount field step. It\'s used to adjust the granularity of the amount field. Examples:', 'gamipress-wc-partial-payments' )
                    . '<br>' . __( 'A step of <strong>5</strong> will allow values multiple of 5: 5, 10, 15, etc.', 'gamipress-wc-partial-payments' )
                    . '<br>' . __( 'A step of <strong>10</strong> will allow values multiple of 10: 10, 20, 30, etc.', 'gamipress-wc-partial-payments' )
                    . '<br>' . __( 'A step of <strong>1</strong> will allow any value.', 'gamipress-wc-partial-payments' ),
                'type' => 'text',
                'attributes' => array(
                    'type' => 'number',
                    'placeholder' => '0',
                    'min' => '0',
                    'step' => '1',
                ),
                'default' => '1'
            ),
            $prefix . 'max_discount' => array(
                'name' => __( 'Maximum discount per cart', 'gamipress-wc-partial-payments' ),
                'desc' => __( 'The maximum discount allowed in a single purchase.', 'gamipress-wc-partial-payments' ),
                'type' => 'text',
                'attributes' => array(
                    'type' => 'number',
                    'placeholder' => '0',
                    'min' => '0',
                    'step' => '1',
                ),
            ),
            $prefix . 'max_discount_type' => array(
                'name' => __( 'Type of maximum discount per cart', 'gamipress-wc-partial-payments' ),
                'desc' => __( 'The type of maximum discount allowed in a single purchase. Available options:', 'gamipress-wc-partial-payments' )
                    . '<br>' . sprintf( __( '<strong>Flat:</strong> The maximum discount allowed will be limited by a flat amount. If you enter 10 then the maximum discount will be %s.', 'gamipress-wc-partial-payments' ), learndash_get_currency_symbol() . '10' )
                    . '<br>' . __( '<strong>Percentage:</strong> The maximum discount allowed will be limited by a percentage of the purchase subtotal. If you enter 10 then the maximum discount will be 10% of the purchase subtotal.', 'gamipress-wc-partial-payments' ),
                'type' => 'select',
                'options' => array(
                    'flat'     => __( 'Flat', 'gamipress-wc-partial-payments' ),
                    'percent'    => __( 'Percentage', 'gamipress-wc-partial-payments' ),
                ),
                'default' => 'flat'
            ),
            $prefix . 'disable_fee_taxes' => array(
                'name' => __( 'Apply taxes', 'gamipress-wc-partial-payments' ),
                'desc' => __( 'Check this option to apply taxes to partial payment discount.', 'gamipress-wc-partial-payments' ),
                'type' 	=> 'checkbox',
                'classes' => 'gamipress-switch'
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_ld_partial_payments_settings_meta_boxes' );

/**
 * Plugin meta boxes
 *
 * @since  1.0.0
 */
function gamipress_ld_partial_payments_meta_boxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_ld_partial_payments_';

    // Points Type Payment Gateway
    gamipress_add_meta_box(
        'gamipress-wc-partial-payments',
        __( 'LearnDash Partial Payments', 'gamipress-wc-partial-payments' ),
        'points-type',
        array(
            $prefix . 'enable' => array(
                'name' 	    => __( 'Enable Partial Payments', 'gamipress-wc-partial-payments' ),
                'desc' 	    => __( 'Check this option to enable this points type to be used as partial payment.', 'gamipress-wc-partial-payments' ),
                'type' 	    => 'checkbox',
                'classes' 	=> 'gamipress-switch',
            ),
            $prefix . 'conversion' => array(
                'name' 	=> __( 'Exchange Conversion', 'gamipress-wc-partial-payments' ),
                'desc' 	=> __( 'Points to money conversion rate.', 'gamipress-wc-partial-payments' ),
                'currency_symbol' => learndash_get_currency_symbol(),
                'type' 	=> 'points_rate',
            ),
            $prefix . 'initial_amount' => array(
                'name' 	    => __( 'Initial Amount', 'gamipress-wc-partial-payments' ),
                'desc' 	    => __( 'Set the initial amount for the points amount input.', 'gamipress-wc-partial-payments' ),
                'type' 	    => 'text',
                'attributes' => array(
                    'type' => 'number',
                    'placeholder' => '0',
                    'min' => '0',
                    'step' => '1',
                )
            ),
            $prefix . 'max_amount' => array(
                'name' 	    => __( 'Maximum Amount', 'gamipress-wc-partial-payments' ),
                'desc' 	    => __( 'Set the maximum amount allowed for the points amount input. Leave it to 0 for no maximum.', 'gamipress-wc-partial-payments' ),
                'type' 	    => 'text',
                'attributes' => array(
                    'type' => 'number',
                    'placeholder' => '0',
                    'min' => '0',
                    'step' => '1',
                ),
                'default' => '0'
            ),
        )
    );

}
add_action( 'cmb2_admin_init', 'gamipress_ld_partial_payments_meta_boxes' );

/**
 * Plugin Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_ld_partial_payments_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-wc-partial-payments-license'] = array(
        'title' => __( 'LearnDash Partial Payments', 'gamipress-wc-partial-payments' ),
        'fields' => array(
            'gamipress_ld_partial_payments_license' => array(
                'name' => __( 'License', 'gamipress-wc-partial-payments' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_LD_PARTIAL_PAYMENTS_FILE,
                'item_name' => 'LearnDash Partial Payments',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_ld_partial_payments_licenses_meta_boxes' );

/**
 * Plugin automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_ld_partial_payments_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-wc-partial-payments'] = __( 'LearnDash Partial Payments', 'gamipress-wc-partial-payments' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_ld_partial_payments_automatic_updates' );