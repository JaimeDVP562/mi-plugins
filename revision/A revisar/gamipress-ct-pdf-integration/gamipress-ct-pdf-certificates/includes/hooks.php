<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Endpoint de descarga del certificado PDF
 */
add_action( 'init', 'ctpdf_certificates_download_endpoint' );

function ctpdf_certificates_download_endpoint() {

    if ( ! isset( $_GET['ctpdf_download_certificate'] ) ) {
        return;
    }

    if ( ! is_user_logged_in() ) {
        wp_die( 'You must be logged in.' );
    }

    $achievement_id = absint( $_GET['achievement_id'] ?? 0 );
    $user_id        = get_current_user_id();

    if ( ! $achievement_id ) {
        wp_die( 'Missing achievement.' );
    }

    $pdf = ctpdf_certificates_get_or_generate_pdf( $achievement_id, $user_id, false );

    if ( ! $pdf || empty( $pdf['path'] ) || ! file_exists( $pdf['path'] ) ) {
        wp_die( 'Certificate could not be generated.' );
    }

    header( 'Content-Type: application/pdf' );
    header( 'Content-Disposition: inline; filename="' . basename( $pdf['path'] ) . '"' );
    readfile( $pdf['path'] );
    exit;
}

/**
 * Añadir botón de descarga en la página Mis Logros
 */
add_filter( 'the_content', 'ctpdf_add_download_buttons_to_mis_logros', 99 );

function ctpdf_add_download_buttons_to_mis_logros( $content ) {

    if ( is_admin() || ! is_user_logged_in() ) {
        return $content;
    }

    if ( ! is_page( 'mis-logros' ) ) {
        return $content;
    }

    $user_id = get_current_user_id();

    $certificates = get_posts( array(
        'post_type'      => 'certificate',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    if ( empty( $certificates ) ) {
        return $content;
    }

    foreach ( $certificates as $certificate ) {

        $template_id = ctpdf_certificates_get_template_id( $certificate->ID );
        if ( ! $template_id ) {
            continue;
        }

        if ( ! gamipress_has_user_earned_achievement( $certificate->ID, $user_id ) ) {
            continue;
        }

        $button_html = ctpdf_certificates_get_download_button_html( $certificate->ID );

        if (
            strpos( $content, $certificate->post_title ) !== false &&
            strpos( $content, 'achievement_id=' . $certificate->ID ) === false
        ) {
            $content = str_replace(
                $certificate->post_title,
                $certificate->post_title . $button_html,
                $content
            );
        }
    }

    return $content;
}