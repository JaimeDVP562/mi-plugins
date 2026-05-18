<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function ctpdf_certificates_get_or_generate_pdf( $achievement_id, $user_id, $override = false ) {

    $achievement_id = absint( $achievement_id );
    $user_id        = absint( $user_id );

    if ( ! $achievement_id || ! $user_id ) {
        return false;
    }

    $template_id = ctpdf_certificates_get_template_id( $achievement_id );

    if ( ! $template_id ) {
        return false;
    }

    $dirs = ctpdf_certificates_get_upload_dir();

    $filename  = 'certificate-' . $user_id . '-' . $achievement_id . '.pdf';
    $file_path = $dirs['dir'] . $filename;
    $file_url  = $dirs['url'] . $filename;

    if ( ! $override && file_exists( $file_path ) ) {
        return array(
            'url'    => $file_url,
            'path'   => $file_path,
            'cached' => true,
        );
    }

    $template_post = get_post( $template_id );
    $achievement   = get_post( $achievement_id );
    $user          = get_user_by( 'id', $user_id );

    if ( ! $template_post || ! $achievement || ! $user ) {
        return false;
    }

    $content = apply_filters( 'the_content', $template_post->post_content );

    $content = str_replace(
        array(
            '{date}',
            '{certificate.issue_date}',
            '{user.display_name}',
            '{achievement.title}',
        ),
        array(
            date_i18n( 'Y-m-d' ),
            date_i18n( 'Y-m-d' ),
            $user->display_name,
            $achievement->post_title,
        ),
        $content
    );

    if ( ! class_exists( '\Mpdf\Mpdf' ) ) {
        $autoload = WP_PLUGIN_DIR . '/ct-pdf-core/vendor/autoload.php';

        if ( ! file_exists( $autoload ) ) {
            return false;
        }

        require_once $autoload;
    }

    $preset = get_post_meta( $template_id, '_ct_pdf_preset', true ) ?: 'modern';
    $size   = get_post_meta( $template_id, '_ct_pdf_page_size', true ) ?: 'A4';
    $orient = get_post_meta( $template_id, '_ct_pdf_orientation', true ) ?: 'P';

    $m_top    = (int) ( get_post_meta( $template_id, '_ct_pdf_margin_top', true ) ?: 20 );
    $m_right  = (int) ( get_post_meta( $template_id, '_ct_pdf_margin_right', true ) ?: 20 );
    $m_bottom = (int) ( get_post_meta( $template_id, '_ct_pdf_margin_bottom', true ) ?: 20 );
    $m_left   = (int) ( get_post_meta( $template_id, '_ct_pdf_margin_left', true ) ?: 20 );

    $font         = get_post_meta( $template_id, '_ct_pdf_font', true ) ?: 'Arial';
    $bg_color     = get_post_meta( $template_id, '_ct_pdf_bg_color', true ) ?: '#ffffff';
    $border_color = get_post_meta( $template_id, '_ct_pdf_border_color', true ) ?: '#6f42c1';

    $bg_image_url     = get_post_meta( $template_id, '_ct_pdf_bg_image', true ) ?: '';
    $border_image_url = get_post_meta( $template_id, '_ct_pdf_border_image', true ) ?: '';

    $bg_image_resolved     = function_exists( 'ct_pdf_url_to_file_uri' ) ? ct_pdf_url_to_file_uri( $bg_image_url ) : $bg_image_url;
    $border_image_resolved = function_exists( 'ct_pdf_url_to_file_uri' ) ? ct_pdf_url_to_file_uri( $border_image_url ) : $border_image_url;

    $css = function_exists( 'ct_pdf_get_preset_css' ) ? ct_pdf_get_preset_css( $preset ) : '';

    $css_dynamic = "
        @page {
            margin-top: {$m_top}mm;
            margin-right: {$m_right}mm;
            margin-bottom: {$m_bottom}mm;
            margin-left: {$m_left}mm;
        }
        body.ctpdf {
            font-family: {$font}, sans-serif;
            background: {$bg_color};
        }
    ";

    if ( ! empty( $bg_image_resolved ) ) {
        $css_dynamic .= "
            @page {
                background-image: url('{$bg_image_resolved}');
                background-image-resize: 6;
            }
        ";
    }

    $frame_html = '';
    if ( ! empty( $border_image_resolved ) ) {
        $frame_html = "
            <div style=\"
                position: fixed;
                left: 0; top: 0; right: 0; bottom: 0;
                background: url('{$border_image_resolved}') no-repeat center center;
                background-size: 100% 100%;
                z-index: 1;
            \"></div>
        ";
    }

    $fallback_border = '';
    if ( empty( $border_image_resolved ) && empty( $bg_image_resolved ) ) {
        $fallback_border = "border: 6px solid {$border_color};";
    }

    $html = "
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                {$css}
                {$css_dynamic}
                body { margin:0; }
                .ctpdf-content { position: relative; z-index: 2; {$fallback_border} }
            </style>
        </head>
        <body class='ctpdf {$preset}'>
            {$frame_html}
            <div class='ctpdf-content ctpdf-cert'>
                {$content}
            </div>
        </body>
        </html>
    ";

    $tempDir = WP_CONTENT_DIR . '/uploads/ct-pdf-temp';
    if ( ! is_dir( $tempDir ) ) {
        wp_mkdir_p( $tempDir );
    }

    try {
        $mpdf = new \Mpdf\Mpdf( array(
            'tempDir'       => $tempDir,
            'format'        => $size,
            'orientation'   => $orient,
            'margin_top'    => $m_top,
            'margin_right'  => $m_right,
            'margin_bottom' => $m_bottom,
            'margin_left'   => $m_left,
        ) );

        $mpdf->showImageErrors = true;
        $mpdf->WriteHTML( $html );
        $mpdf->Output( $file_path, \Mpdf\Output\Destination::FILE );

    } catch ( \Throwable $e ) {
        return false;
    }

    return array(
        'url'    => $file_url,
        'path'   => $file_path,
        'cached' => false,
    );
}