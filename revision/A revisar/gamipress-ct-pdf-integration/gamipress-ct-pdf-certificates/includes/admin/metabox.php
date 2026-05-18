<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', 'ctpdf_certificates_add_metabox' );

function ctpdf_certificates_add_metabox() {
    add_meta_box(
        'ctpdf_certificate_template',
        'Certificate PDF Template',
        'ctpdf_certificates_metabox_callback',
        'certificate',
        'side',
        'default'
    );
}

function ctpdf_certificates_metabox_callback( $post ) {
    $selected = get_post_meta( $post->ID, '_ctpdf_certificate_template_id', true );

    $templates = get_posts( array(
        'post_type'      => 'ct_pdf_template',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    wp_nonce_field( 'ctpdf_certificate_template_save', 'ctpdf_certificate_template_nonce' );

    echo '<p><select name="ctpdf_certificate_template_id" style="width:100%;">';
    echo '<option value="">No expedir certificados</option>';

    foreach ( $templates as $template ) {
        echo '<option value="' . esc_attr( $template->ID ) . '" ' . selected( $selected, $template->ID, false ) . '>';
        echo esc_html( $template->post_title );
        echo '</option>';
    }

    echo '</select></p>';
}

add_action( 'save_post_certificate', 'ctpdf_certificates_save_metabox' );

function ctpdf_certificates_save_metabox( $post_id ) {

    if ( ! isset( $_POST['ctpdf_certificate_template_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['ctpdf_certificate_template_nonce'], 'ctpdf_certificate_template_save' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $template_id = isset( $_POST['ctpdf_certificate_template_id'] ) ? absint( $_POST['ctpdf_certificate_template_id'] ) : 0;

    if ( $template_id ) {
        update_post_meta( $post_id, '_ctpdf_certificate_template_id', $template_id );
    } else {
        delete_post_meta( $post_id, '_ctpdf_certificate_template_id' );
    }
}