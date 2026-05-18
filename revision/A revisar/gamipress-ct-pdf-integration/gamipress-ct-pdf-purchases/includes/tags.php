<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function ctpdf_purchases_parse_tags( $content, $payment_data ) {
    $payment = isset( $payment_data['payment'] ) ? $payment_data['payment'] : array();
    $company = isset( $payment_data['company'] ) ? $payment_data['company'] : array();
    $items   = isset( $payment_data['items'] ) ? $payment_data['items'] : array();

    $items_text = '';

    if ( ! empty( $items ) ) {
        foreach ( $items as $item ) {
            $description = isset( $item['description'] ) ? $item['description'] : '';
            $quantity    = isset( $item['quantity'] ) ? $item['quantity'] : '';
            $total       = isset( $item['total'] ) ? $item['total'] : '';

            $items_text .= $description . ' x ' . $quantity . ' = ' . $total . "\n";
        }
    }

    $replacements = array(
        '{purchase_number}' => isset( $payment['number'] ) ? $payment['number'] : '',
        '{purchase_date}'   => isset( $payment['date'] ) ? $payment['date'] : '',
        '{purchase_total}'  => isset( $payment['total'] ) ? $payment['total'] : '',
        '{user_email}'      => isset( $payment['email'] ) ? $payment['email'] : '',
        '{company_name}'    => isset( $company['name'] ) ? $company['name'] : '',
        '{company_email}'   => isset( $company['email'] ) ? $company['email'] : '',
        '{company_phone}'   => isset( $company['phone'] ) ? $company['phone'] : '',
        '{company_address}' => isset( $company['address'] ) ? $company['address'] : '',
        '{purchase_items}'  => trim( $items_text ),
    );

    return strtr( $content, $replacements );
}