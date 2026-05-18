<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the address fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_woocommerce_get_address_fields() {

    return array(
        'first_name',
        'last_name',
        'company',
        'address_1',
        'address_2',
        'city',
        'postcode',
        'country',
        'state',
        'phone',
        'email',
    );

}

/**
 * Helper function to get an address field label
 *
 * @since 1.0.0
 *
 * @param string $field
 *
 * @return string
 */
function automatorwp_woocommerce_get_address_field_label( $field ) {

    $labels = array(
        'first_name' => __( 'First name', 'automatorwp-woocommerce' ),
        'last_name' => __( 'Last name', 'automatorwp-woocommerce' ),
        'company' => __( 'Company', 'automatorwp-woocommerce' ),
        'address_1' => __( 'Address line 1', 'automatorwp-woocommerce' ),
        'address_2' => __( 'Address line 2', 'automatorwp-woocommerce' ),
        'city' => __( 'City', 'automatorwp-woocommerce' ),
        'postcode' => __( 'Postcode / ZIP', 'automatorwp-woocommerce' ),
        'country' => __( 'Country / Region', 'automatorwp-woocommerce' ),
        'state' => __( 'State / County', 'automatorwp-woocommerce' ),
        'phone' => __( 'Phone', 'automatorwp-woocommerce' ),
        'email' => __( 'Email', 'automatorwp-woocommerce' ),
    );

    return isset( $labels[$field] ) ? $labels[$field] : '';

}

/**
 * Helper function to get an address field preview
 *
 * @since 1.0.0
 *
 * @param string $field
 *
 * @return string
 */
function automatorwp_woocommerce_get_address_field_preview( $field ) {

    $previews = array(
        'first_name' => __( 'AutomatorWP', 'automatorwp-woocommerce' ),
        'last_name' => __( 'Plugin', 'automatorwp-woocommerce' ),
        'company' => __( 'AutomatorWP Ltd.', 'automatorwp-woocommerce' ),
        'address_1' => __( 'False Street, 123', 'automatorwp-woocommerce' ),
        'address_2' => __( 'First floor, door 2', 'automatorwp-woocommerce' ),
        'city' => __( 'Brooklyn', 'automatorwp-woocommerce' ),
        'postcode' => __( '12345', 'automatorwp-woocommerce' ),
        'country' => __( 'United States', 'automatorwp-woocommerce' ),
        'state' => __( 'New York', 'automatorwp-woocommerce' ),
        'phone' => __( '202-555-1234', 'automatorwp-woocommerce' ),
        'email' => __( 'contact@automatorwp.com', 'automatorwp-woocommerce' ),
    );

    return isset( $previews[$field] ) ? $previews[$field] : '';

}

/**
 * Helper function to compare order statuses
 *
 * @since 1.0.0
 *
 * @param string $status
 * @param string $required_status
 *
 * @return string
 */
function automatorwp_woocommerce_order_status_matches( $status, $required_status ) {

    $status = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
    $required_status = 'wc-' === substr( $required_status, 0, 3 ) ? substr( $required_status, 3 ) : $required_status;

    // Bail if status doesn't match with the trigger option
    if( $required_status !== 'any' && $status !== $required_status ) {
        return false;
    }

    return true;

}

/**
 * Options callback for select2 fields assigned to variations
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_woocommerce_options_cb_variations( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any variation', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $variation_id ) {

            // Skip option none
            if( $variation_id === $none_value ) {
                continue;
            }

            $options[$variation_id] = automatorwp_woocommerce_get_variation_title( $variation_id );
        }
    }

    return $options;

}

/**
 * Get the variation title
 *
 * @since 1.0.0
 *
 * @param int $variation_id
 *
 * @return string|null
 */
function automatorwp_woocommerce_get_variation_title( $variation_id ) {

    // Empty title if no ID provided
    if( absint( $variation_id ) === 0 ) {
        return '';
    }

    $variation = wc_get_product( $variation_id );
    
    $attributes = $variation->get_attributes();

    return implode( ', ', array_values( $attributes ) );

}