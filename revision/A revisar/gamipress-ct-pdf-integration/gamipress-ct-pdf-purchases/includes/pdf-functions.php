<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Obtener o generar PDF de purchase
 */
function ctpdf_purchases_get_or_generate_pdf( $payment_id, $override = false ) {

    $payment_id = absint( $payment_id );

    if ( ! $payment_id ) {
        return false;
    }

    // 1. Si ya existe y no queremos regenerar, devolverlo
    $existing_pdf_url  = get_post_meta( $payment_id, '_ctpdf_pdf_url', true );
    $existing_pdf_path = get_post_meta( $payment_id, '_ctpdf_pdf_path', true );

    if ( ! $override && ! empty( $existing_pdf_url ) && ! empty( $existing_pdf_path ) && file_exists( $existing_pdf_path ) ) {
        return array(
            'url'    => $existing_pdf_url,
            'path'   => $existing_pdf_path,
            'cached' => true,
        );
    }

    // 2. Obtener template global
    $template_id = ctpdf_purchases_get_template_id();

    if ( ! $template_id ) {
        return false;
    }

    // 3. Subidas
    $upload_dir = wp_upload_dir();
    $base_dir   = trailingslashit( $upload_dir['basedir'] ) . 'ct-pdf-purchases/';
    $base_url   = trailingslashit( $upload_dir['baseurl'] ) . 'ct-pdf-purchases/';

    if ( ! file_exists( $base_dir ) ) {
        wp_mkdir_p( $base_dir );
    }

    // 4. Nombre único por payment
    $filename  = 'purchase-' . $payment_id . '.pdf';
    $file_path = $base_dir . $filename;
    $file_url  = $base_url . $filename;

    // 5. Si el archivo existe y override es false, devolverlo
    if ( ! $override && file_exists( $file_path ) ) {
        update_post_meta( $payment_id, '_ctpdf_pdf_url', $file_url );
        update_post_meta( $payment_id, '_ctpdf_pdf_path', $file_path );

        return array(
            'url'    => $file_url,
            'path'   => $file_path,
            'cached' => true,
        );
    }

    // 6. Obtener datos reales del payment
    $payment_data = ctpdf_purchases_get_payment_data( $payment_id );

    if ( ! $payment_data ) {
        return false;
    }

    // 7. Obtener template real
    $template_post = get_post( $template_id );

    if ( ! $template_post ) {
        return false;
    }

    // 8. Parsear tags
    $template_content = $template_post->post_content;
    $content = ctpdf_purchases_parse_tags( $template_content, $payment_data );

    // 9. Cargar mPDF desde CT PDF Core
    if ( ! class_exists( '\Mpdf\Mpdf' ) ) {
        $autoload = WP_PLUGIN_DIR . '/ct-pdf-core/vendor/autoload.php';

        if ( ! file_exists( $autoload ) ) {
            return false;
        }

        require_once $autoload;
    }

    // 10. Crear HTML mínimo válido
    $html = nl2br( wp_kses_post( $content ) );

    // 11. Generar PDF real
    try {
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML( $html );
        $mpdf->Output( $file_path, \Mpdf\Output\Destination::FILE );
    } catch ( \Throwable $e ) {
        return false;
    }

    // 12. Guardar referencia
    update_post_meta( $payment_id, '_ctpdf_pdf_url', $file_url );
    update_post_meta( $payment_id, '_ctpdf_pdf_path', $file_path );

    return array(
        'url'    => $file_url,
        'path'   => $file_path,
        'cached' => false,
    );
}