<?php
/**
 * Utility to add a custom readme description
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

function edd_custom_readme_description_meta_box() {
    add_meta_box( 'edd-custom-readme-description-meta-box', 'Custom Readme Description', 'edd_custom_readme_description_meta_box_content', 'download' );
}
add_action( 'add_meta_boxes', 'edd_custom_readme_description_meta_box' );

function edd_custom_readme_description_meta_box_content( $post ) {
    wp_nonce_field( 'edd_custom_readme_description_nonce_action', 'edd_custom_readme_description_nonce' );

    $readme_description = get_post_meta( $post->ID, '_edd_custom_readme_description', true );

    $style = '
        font-family: Consolas,Monaco,monospace;
        font-size: 13px;
        padding: 10px;
        line-height: 150%;
        width: 100%;
    ';
    ?>
    You can enter the <code>markdown</code> text, on save, it will be parsed to HTML.
    <textarea name="_edd_custom_readme_description" id="edd-custom-readme-description" rows="20" style="<?php echo $style; ?>"><?php echo esc_textarea( $readme_description ); ?></textarea>
    <?php
}

function edd_custom_readme_description_save_post( $post_id, $post ) {
    $nonce_name   = $_POST['edd_custom_readme_description_nonce'] ?? '';
    $nonce_action = 'edd_custom_readme_description_nonce_action';

    if (
        ! isset( $nonce_name ) ||
        ! wp_verify_nonce( $nonce_name, $nonce_action ) ||
        ! current_user_can( 'edit_post', $post_id ) ||
        wp_is_post_autosave( $post_id ) ||
        wp_is_post_revision( $post_id )
    ) {
        return;
    }

    $raw_md = $_POST['_edd_custom_readme_description'];
    $parsed_html = parse_custom_markdown( $raw_md );
    update_post_meta( $post_id, '_edd_custom_readme_description', $parsed_html );
}
add_action( 'save_post', 'edd_custom_readme_description_save_post', 10, 2 );

function edd_custom_readme_description_edd_sl_license_response( $response, $download, $download_beta ) {
    $readme_description = get_post_meta( $download->ID, '_edd_custom_readme_description', true );

    if( ! empty( $readme_description ) ) {
        $changelog      = get_post_meta( $download->ID, '_edd_sl_changelog', true );

        $allowed_tags   = '<p><li><ul><ol><strong><b><a><em><span><br><code>';
        $allowed_tags  .= '<img><div>';
        $allowed_tags  .= '<table><thead><tbody><tr><td><th>';
        $allowed_tags  .= '<h1><h2><h3><h4><h5>';

        $response['sections'] = serialize(
            array(
                'description' => wpautop( strip_tags( $readme_description, $allowed_tags ) ),
                'changelog'   => wpautop( strip_tags( stripslashes( $changelog ), '<p><li><ul><ol><strong><a><em><span><br>' ) ),
            )
        );
    }

    return $response;
}
add_filter( 'edd_sl_license_response', 'edd_custom_readme_description_edd_sl_license_response', 11, 3 );

function parse_custom_markdown($content) {
    $lines = explode("\n", $content);
    $parsed = '';
    $in_list = false;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        // Títulos tipo Markdown (#, ##, etc.)
        if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $matches)) {
            $level = strlen($matches[1]);
            $parsed .= "<h{$level}>{$matches[2]}</h{$level}>";
            continue;
        }

        // Títulos con = y ==
        if (preg_match('/^==\s*(.*?)\s*=*$/', $line, $matches)) {
            if ($in_list) {
                $parsed .= '</ul>';
                $in_list = false;
            }
            $parsed .= '<h3>' . $matches[1] . '</h3>';
        } elseif (preg_match('/^=\s*(.*?)\s*=*$/', $line, $matches)) {
            if ($in_list) {
                $parsed .= '</ul>';
                $in_list = false;
            }
            $parsed .= '<h4>' . $matches[1] . '</h4>';
        }

        // Listas (*, -, +)
        elseif (preg_match('/^(\*|\-|\+)\s+(.*)$/', $line, $matches)) {
            if (!$in_list) {
                $parsed .= '<ul>';
                $in_list = true;
            }
            $item = parse_inline_markdown($matches[2]);
            $parsed .= '<li>' . $item . '</li>';
        }

        // Texto normal
        else {
            if ($in_list) {
                $parsed .= '</ul>';
                $in_list = false;
            }
            $line = parse_inline_markdown($line);
            $parsed .= '<p>' . $line . '</p>';
        }
    }

    if ($in_list) {
        $parsed .= '</ul>';
    }

    return $parsed;
}

function parse_inline_markdown($text) {
    // Negrita: **texto**
    $text = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $text);

    // Cursiva: *texto* o _texto_
    $text = preg_replace('/(?<!\*)\*(?!\*)(.*?)\*(?!\*)/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);

    // Código en línea: `texto`
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);

    // Enlaces: [texto](url)
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);

    return $text;
}
