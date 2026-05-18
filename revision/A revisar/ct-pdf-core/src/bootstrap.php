<?php
if (!defined('ABSPATH')) exit;

/**
 * CT PDF CORE - bootstrap.php
 * MVP + Settings + Background/Border images (localhost-safe) + Preview + Fancy layout
 */

/** ------------------------------------------------------------
 * 1) CPT: PDF Templates
 * ------------------------------------------------------------ */
add_action('init', function () {
    register_post_type('ct_pdf_template', [
        'labels' => [
            'name'          => 'PDF Templates',
            'singular_name' => 'PDF Template',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-media-document',
        'supports'     => ['title', 'editor', 'revisions'],
        'rewrite'      => false,
    ]);
});

/** ------------------------------------------------------------
 * 2) Metabox Settings
 * ------------------------------------------------------------ */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'ct_pdf_template_settings',
        'CT PDF Settings',
        'ct_pdf_render_settings_metabox',
        'ct_pdf_template',
        'side',
        'high'
    );
});

function ct_pdf_render_settings_metabox(\WP_Post $post) {
    $preset = get_post_meta($post->ID, '_ct_pdf_preset', true) ?: 'modern';
    $size   = get_post_meta($post->ID, '_ct_pdf_page_size', true) ?: 'A4';
    $orient = get_post_meta($post->ID, '_ct_pdf_orientation', true) ?: 'P';

    $m_top    = get_post_meta($post->ID, '_ct_pdf_margin_top', true);
    $m_right  = get_post_meta($post->ID, '_ct_pdf_margin_right', true);
    $m_bottom = get_post_meta($post->ID, '_ct_pdf_margin_bottom', true);
    $m_left   = get_post_meta($post->ID, '_ct_pdf_margin_left', true);

    $m_top    = ($m_top === '' ? 20 : (int)$m_top);
    $m_right  = ($m_right === '' ? 20 : (int)$m_right);
    $m_bottom = ($m_bottom === '' ? 20 : (int)$m_bottom);
    $m_left   = ($m_left === '' ? 20 : (int)$m_left);

    $font = get_post_meta($post->ID, '_ct_pdf_font', true) ?: 'Arial';

    $bg_color     = get_post_meta($post->ID, '_ct_pdf_bg_color', true) ?: '#ffffff';
    $border_color = get_post_meta($post->ID, '_ct_pdf_border_color', true) ?: '#6f42c1';

    $bg_image_url     = get_post_meta($post->ID, '_ct_pdf_bg_image', true) ?: '';
    $border_image_url = get_post_meta($post->ID, '_ct_pdf_border_image', true) ?: '';

    wp_nonce_field('ct_pdf_save_template_meta', 'ct_pdf_nonce');

    echo '<p><label><strong>Preset</strong></label><br>';
    echo '<select name="ct_pdf_preset" style="width:100%;">';
    foreach ([
        'modern'            => 'Modern',
        'institution'       => 'Institution',
        'certificate_fancy' => 'Certificate (Fancy)'
    ] as $k => $label) {
        printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($preset, $k, false), esc_html($label));
    }
    echo '</select></p>';

    echo '<p><label><strong>Page size</strong></label><br>';
    echo '<select name="ct_pdf_page_size" style="width:100%;">';
    foreach (['A4','A5','LETTER'] as $s) {
        printf('<option value="%s" %s>%s</option>', esc_attr($s), selected($size, $s, false), esc_html($s));
    }
    echo '</select></p>';

    echo '<p><label><strong>Orientation</strong></label><br>';
    echo '<select name="ct_pdf_orientation" style="width:100%;">';
    foreach (['P' => 'Portrait', 'L' => 'Landscape'] as $k => $label) {
        printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($orient, $k, false), esc_html($label));
    }
    echo '</select></p>';

    echo '<hr>';

    echo '<p><strong>Margins (mm)</strong></p>';
    echo '<p style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">';
    echo '<label style="font-size:12px;">Top<br><input type="number" name="ct_pdf_margin_top" value="'.esc_attr($m_top).'" style="width:70px;"></label>';
    echo '<label style="font-size:12px;">Right<br><input type="number" name="ct_pdf_margin_right" value="'.esc_attr($m_right).'" style="width:70px;"></label>';
    echo '<label style="font-size:12px;">Bottom<br><input type="number" name="ct_pdf_margin_bottom" value="'.esc_attr($m_bottom).'" style="width:70px;"></label>';
    echo '<label style="font-size:12px;">Left<br><input type="number" name="ct_pdf_margin_left" value="'.esc_attr($m_left).'" style="width:70px;"></label>';
    echo '</p>';

    echo '<p><label><strong>Font</strong></label><br>';
    echo '<select name="ct_pdf_font" style="width:100%;">';
    foreach (['Arial','Helvetica','Times','Courier','DejaVuSans'] as $f) {
        printf('<option value="%s" %s>%s</option>', esc_attr($f), selected($font, $f, false), esc_html($f));
    }
    echo '</select></p>';

    echo '<p><label><strong>Background</strong></label><br>';
    echo '<input type="color" name="ct_pdf_bg_color" value="'.esc_attr($bg_color).'" style="width:100%;height:30px;"></p>';

    echo '<p><label><strong>Background image (URL)</strong></label><br>';
    echo '<input type="text" name="ct_pdf_bg_image" value="'.esc_attr($bg_image_url).'" style="width:100%;"></p>';

    echo '<p><label><strong>Border color</strong></label><br>';
    echo '<input type="color" name="ct_pdf_border_color" value="'.esc_attr($border_color).'" style="width:100%;height:30px;"></p>';

    echo '<p><label><strong>Border image (URL)</strong></label><br>';
    echo '<input type="text" name="ct_pdf_border_image" value="'.esc_attr($border_image_url).'" style="width:100%;"></p>';

    $url = add_query_arg(['action' => 'ct_pdf_preview', 'template_id' => $post->ID], admin_url('admin-post.php'));
    echo '<p><a class="button button-primary" href="'.esc_url($url).'" target="_blank">Preview PDF</a></p>';

    echo '<hr>';
    echo '<p style="font-size:12px;color:#666;margin:0;">Tags disponibles:<br><code>{date}</code> <code>{user.display_name}</code></p>';
}

/** ------------------------------------------------------------
 * 3) Save metabox
 * ------------------------------------------------------------ */
add_action('save_post_ct_pdf_template', function ($post_id) {
    if (!isset($_POST['ct_pdf_nonce']) || !wp_verify_nonce($_POST['ct_pdf_nonce'], 'ct_pdf_save_template_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, '_ct_pdf_preset', sanitize_text_field($_POST['ct_pdf_preset'] ?? 'modern'));
    update_post_meta($post_id, '_ct_pdf_page_size', sanitize_text_field($_POST['ct_pdf_page_size'] ?? 'A4'));
    update_post_meta($post_id, '_ct_pdf_orientation', sanitize_text_field($_POST['ct_pdf_orientation'] ?? 'P'));

    update_post_meta($post_id, '_ct_pdf_margin_top', (int)($_POST['ct_pdf_margin_top'] ?? 20));
    update_post_meta($post_id, '_ct_pdf_margin_right', (int)($_POST['ct_pdf_margin_right'] ?? 20));
    update_post_meta($post_id, '_ct_pdf_margin_bottom', (int)($_POST['ct_pdf_margin_bottom'] ?? 20));
    update_post_meta($post_id, '_ct_pdf_margin_left', (int)($_POST['ct_pdf_margin_left'] ?? 20));

    update_post_meta($post_id, '_ct_pdf_font', sanitize_text_field($_POST['ct_pdf_font'] ?? 'Arial'));
    update_post_meta($post_id, '_ct_pdf_bg_color', sanitize_hex_color($_POST['ct_pdf_bg_color'] ?? '#ffffff'));
    update_post_meta($post_id, '_ct_pdf_border_color', sanitize_hex_color($_POST['ct_pdf_border_color'] ?? '#6f42c1'));

    update_post_meta($post_id, '_ct_pdf_bg_image', esc_url_raw($_POST['ct_pdf_bg_image'] ?? ''));
    update_post_meta($post_id, '_ct_pdf_border_image', esc_url_raw($_POST['ct_pdf_border_image'] ?? ''));

    $v = (int) get_post_meta($post_id, '_ct_pdf_template_version', true);
    update_post_meta($post_id, '_ct_pdf_template_version', $v + 1);
});

/** ------------------------------------------------------------
 * Helpers: URL -> file:/// for uploads (localhost-safe)
 * ------------------------------------------------------------ */
function ct_pdf_url_to_file_uri(string $url): string {
    $url = trim($url);
    if ($url === '') return '';
    if (stripos($url, 'file://') === 0) return $url;

    $uploads = wp_get_upload_dir();
    if (!empty($uploads['baseurl']) && strpos($url, $uploads['baseurl']) === 0) {
        $rel  = ltrim(substr($url, strlen($uploads['baseurl'])), '/');
        $path = trailingslashit($uploads['basedir']) . $rel;
        $path = wp_normalize_path($path);

        if (file_exists($path)) {
            return 'file:///' . ltrim($path, '/');
        }
    }
    return $url;
}

/** ------------------------------------------------------------
 * 4) Preview endpoint
 * ------------------------------------------------------------ */
add_action('admin_post_ct_pdf_preview', function () {
    if (!current_user_can('edit_posts')) wp_die('No permission');

    $template_id = (int) ($_GET['template_id'] ?? 0);
    if (!$template_id) wp_die('Missing template_id');

    $post = get_post($template_id);
    if (!$post || $post->post_type !== 'ct_pdf_template') wp_die('Invalid template');

    $preset = get_post_meta($template_id, '_ct_pdf_preset', true) ?: 'modern';
    $size   = get_post_meta($template_id, '_ct_pdf_page_size', true) ?: 'A4';
    $orient = get_post_meta($template_id, '_ct_pdf_orientation', true) ?: 'P';

    $m_top    = (int)(get_post_meta($template_id, '_ct_pdf_margin_top', true) ?: 20);
    $m_right  = (int)(get_post_meta($template_id, '_ct_pdf_margin_right', true) ?: 20);
    $m_bottom = (int)(get_post_meta($template_id, '_ct_pdf_margin_bottom', true) ?: 20);
    $m_left   = (int)(get_post_meta($template_id, '_ct_pdf_margin_left', true) ?: 20);

    $font         = get_post_meta($template_id, '_ct_pdf_font', true) ?: 'Arial';
    $bg_color     = get_post_meta($template_id, '_ct_pdf_bg_color', true) ?: '#ffffff';
    $border_color = get_post_meta($template_id, '_ct_pdf_border_color', true) ?: '#6f42c1';

    $bg_image_url     = get_post_meta($template_id, '_ct_pdf_bg_image', true) ?: '';
    $border_image_url = get_post_meta($template_id, '_ct_pdf_border_image', true) ?: '';

    $bg_image_resolved     = ct_pdf_url_to_file_uri($bg_image_url);
    $border_image_resolved = ct_pdf_url_to_file_uri($border_image_url);

    $content = apply_filters('the_content', $post->post_content);

    $user = wp_get_current_user();
    $content = str_replace(
        ['{date}', '{user.display_name}'],
        [date_i18n('Y-m-d'), ($user && $user->display_name ? $user->display_name : 'User')],
        $content
    );

    $css = ct_pdf_get_preset_css($preset);

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

    if (!empty($bg_image_resolved)) {
        $css_dynamic .= "
            @page {
                background-image: url('{$bg_image_resolved}');
                background-image-resize: 6;
            }
        ";
    }

    // Frame overlay (only if border image exists)
    $frame_html = '';
    if (!empty($border_image_resolved)) {
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

    // IMPORTANT: no fallback border box when we are using images
    $fallback_border = '';
    if (empty($border_image_resolved) && empty($bg_image_resolved)) {
        $fallback_border = "border: 6px solid {$border_color};";
    }

    $html = "
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                {$css}
                {$css_dynamic}
                body{ margin:0; }
                .ctpdf-content{ position: relative; z-index: 2; {$fallback_border} }
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
    if (!is_dir($tempDir)) wp_mkdir_p($tempDir);

    if (!class_exists('\Mpdf\Mpdf')) {
        wp_die('mPDF not found. Did you run composer install?');
    }

    $mpdf = new \Mpdf\Mpdf([
        'tempDir'     => $tempDir,
        'format'      => $size,
        'orientation' => $orient,
        'margin_top'    => $m_top,
        'margin_right'  => $m_right,
        'margin_bottom' => $m_bottom,
        'margin_left'   => $m_left,
    ]);

    $mpdf->showImageErrors = true;

    $mpdf->WriteHTML($html);
    $mpdf->Output('preview.pdf', 'I');
    exit;
});

/** ------------------------------------------------------------
 * Preset CSS
 * ------------------------------------------------------------ */
function ct_pdf_get_preset_css(string $preset): string {
    $base = "
        p { margin: 0 0 10px; line-height: 1.4; }
        strong { font-weight: 700; }
    ";

    if ($preset === 'institution') {
        return $base . "
            .ctpdf-content { padding: 50px; }
            h1 { font-size: 32px; text-transform: uppercase; letter-spacing: 1px; margin:0 0 12px; }
        ";
    }

    if ($preset === 'certificate_fancy') {
        return $base . "
            .ctpdf-content{
                padding: 70px 90px;
                text-align: center;
            }

            /* Make the first lines behave like a certificate */
            .ctpdf-content p { font-size: 14px; }
            .ctpdf-content p:first-child { 
                font-size: 46px; 
                font-weight: 800; 
                letter-spacing: 2px;
                text-transform: uppercase;
                margin-bottom: 6px;
            }

            /* If user writes 'OF ACHIEVEMENT' as second line */
            .ctpdf-content p:nth-child(2){
                font-size: 16px;
                letter-spacing: 5px;
                text-transform: uppercase;
                margin-bottom: 28px;
                opacity: 0.9;
            }

            /* Big name line if they put it alone */
            .ctpdf-content p:nth-child(4){
                font-size: 34px;
                font-weight: 700;
                margin: 8px 0 18px;
            }

            /* Center block spacing */
            .ctpdf-content p { margin-bottom: 12px; }
        ";
    }

    // modern default
    return $base . "
        .ctpdf-content { padding: 50px 40px; }
        p:first-child { font-size: 40px; font-weight: 800; margin-bottom: 10px; }
    ";
}

/** ------------------------------------------------------------
 * Hidden CPT placeholder
 * ------------------------------------------------------------ */
add_action('init', function () {
    register_post_type('ct_pdf_generated', [
        'labels' => ['name' => 'Generated PDFs'],
        'public'   => false,
        'show_ui'  => false,
        'supports' => ['title'],
        'rewrite'  => false,
    ]);
});