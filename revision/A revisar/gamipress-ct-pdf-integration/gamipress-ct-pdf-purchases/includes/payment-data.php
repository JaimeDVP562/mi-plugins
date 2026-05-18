<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function ctpdf_purchases_get_payment_data( $payment_id ) {
    global $wpdb;

    $payment_id = absint( $payment_id );

    if ( ! $payment_id ) {
        return false;
    }

    $payments_table = $wpdb->prefix . 'gamipress_payments';
    $items_table    = $wpdb->prefix . 'gamipress_payment_items';

    $payment = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$payments_table} WHERE payment_id = %d",
        $payment_id
    ), ARRAY_A );

    if ( ! $payment ) {
        return false;
    }

    $items = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$items_table} WHERE payment_id = %d",
        $payment_id
    ), ARRAY_A );

    $settings = ctpdf_purchases_get_settings();

    return array(
        'payment' => $payment,
        'items'   => $items,
        'company' => array(
            'name'    => $settings['company_name'],
            'email'   => $settings['company_email'],
            'phone'   => $settings['company_phone'],
            'address' => $settings['company_address'],
        ),
    );
}