<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function ctpdf_certificates_get_template_id( $achievement_id ) {
    return absint( get_post_meta( $achievement_id, '_ctpdf_certificate_template_id', true ) );
}

function ctpdf_certificates_get_upload_dir() {
    $upload_dir = wp_upload_dir();

    $base_dir = trailingslashit( $upload_dir['basedir'] ) . 'ct-pdf-certificates/';
    $base_url = trailingslashit( $upload_dir['baseurl'] ) . 'ct-pdf-certificates/';

    if ( ! file_exists( $base_dir ) ) {
        wp_mkdir_p( $base_dir );
    }

    return array(
        'dir' => $base_dir,
        'url' => $base_url,
    );
}

function ctpdf_certificates_get_download_url( $achievement_id ) {
    return add_query_arg( array(
        'ctpdf_download_certificate' => 1,
        'achievement_id'             => absint( $achievement_id ),
    ), home_url( '/' ) );
}

function ctpdf_certificates_get_download_button_html( $achievement_id ) {
    $url = ctpdf_certificates_get_download_url( $achievement_id );

    return '<div class="ctpdf-button-wrapper">
        <a href="' . esc_url( $url ) . '" target="_blank" class="ctpdf-download-certificate">
            Descargar certificado
        </a>
    </div>';
}