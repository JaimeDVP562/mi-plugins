<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init', 'ctpdf_purchases_auto_generate_pdf' );

function ctpdf_purchases_auto_generate_pdf() {

    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Only on payment edit screen
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'edit_gamipress_payments' ) {
        return;
    }

    if ( ! isset( $_GET['payment_id'] ) ) {
        return;
    }

    $payment_id = absint( $_GET['payment_id'] );

    if ( ! $payment_id ) {
        return;
    }

    ctpdf_purchases_get_or_generate_pdf( $payment_id, false );
}

/**
 * Add Download Invoice action to the Actions box
 */
add_filter( 'gamipress_purchases_payment_actions', 'ctpdf_purchases_add_download_invoice_action', 10, 2 );

function ctpdf_purchases_add_download_invoice_action( $payment_actions, $payment ) {

    if ( empty( $payment ) || empty( $payment->payment_id ) ) {
        return $payment_actions;
    }

    if ( empty( $payment->status ) || $payment->status !== 'complete' ) {
        return $payment_actions;
    }

    $pdf = ctpdf_purchases_get_or_generate_pdf( $payment->payment_id, false );

    if ( ! $pdf || empty( $pdf['url'] ) ) {
        return $payment_actions;
    }

    $payment_actions['download_invoice'] = array(
        'label'  => __( 'Download Invoice', 'gamipress-ct-pdf-purchases' ),
        'icon'   => 'dashicons-media-document',
        'url'    => $pdf['url'],
        'target' => '_blank',
    );

    return $payment_actions;
}