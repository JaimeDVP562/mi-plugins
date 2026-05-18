<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Obtener ajustes del plugin
 */
function ctpdf_purchases_get_settings() {
    $defaults = array(
        'template_id'    => 0,
        'company_name'   => '',
        'company_email'  => '',
        'company_phone'  => '',
        'company_address'=> '',
    );

    $options = get_option( 'gamipress_ct_pdf_purchases_settings', array() );

    return wp_parse_args( $options, $defaults );
}

/**
 * Obtener template global
 */
function ctpdf_purchases_get_template_id() {
    $settings = ctpdf_purchases_get_settings();
    return absint( $settings['template_id'] );
}