<?php
/**
 * Builder
 *
 * @package GamiPress\Credly\Builder
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Content for the builder field
 *
 * @since 1.0.0
 */
function gamipress_credly_builder_content_cb() {

    echo __( 'Credly offers a badge builder where you can create your own images.', 'gamipress-credly' )
        . '<br>' .__( 'You can use this builder to create the achievement image or upload a custom image through the "Featured image" box.', 'gamipress-credly' )
        . '<br><br>' . '<a href="admin-ajax.php?action=gamipress_credly_builder&post_id=' . get_the_ID() . '&TB_iframe=true" class="thickbox button gamipress-credly-builder">' . __( 'Open Credly Badge Builder', 'gamipress-credly' ) . '</a>';

}

/**
 * AJAX handler to render the builder
 *
 * @since 1.0.0
 */
function gamipress_credly_ajax_builder() {

    $link = '';
    $post_id = ( isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0 );
    $attachment_id = get_post_thumbnail_id( $post_id );
    $meta_data = get_post_meta( $attachment_id, '_gamipress_credly_meta_data', true );

    if( empty( $meta_data ) ) {
        $meta_data = null;
    }

    $auth = gamipress_credly_get_auth();

    if( $auth ) {

        $response = wp_remote_post( 'https://credly.com/badge-builder/code/', array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Content-Length' => 141
            ),
            'body' => array(
                'access_token' => $auth['access_token']
            )
        ) );

        if ( ! is_wp_error( $response ) ) {

            $data = json_decode( $response['body'] );

            if ( $data->success === true ) {

                $link = add_query_arg(
                    array( 'continue' => rawurlencode( json_encode( $meta_data ) ) ),
                    esc_url( trailingslashit( 'https://credly.com/badge-builder/embed/' ) . $data->temp_token )
                );

            }
        }

    }

    wp_redirect( $link );
    die();

}
add_action( 'wp_ajax_gamipress_credly_builder',  'gamipress_credly_ajax_builder' );

/**
 * AJAX handler to save the image created from the builder
 *
 * @since 1.0.0
 */
function gamipress_credly_ajax_builder_save() {

    $post_id = absint( $_REQUEST['post_id'] );
    $image = esc_url( $_REQUEST['image'] );
    $meta_data = $_REQUEST['all_data'];

    $attachment_id = gamipress_import_attachment( $image );

    if( $attachment_id ) {
        // Assign the thumbnail to the post
        gamipress_update_post_meta( $post_id, '_thumbnail_id', $attachment_id );

        // Store packaged data
        update_post_meta( $attachment_id, '_gamipress_credly_meta_data', $meta_data );


        // Return our success response
        wp_send_json_success( array(
            'attachment_id' => $attachment_id,
            'attachment_url' => wp_get_attachment_image_url( $attachment_id ),
            'attachment_html' => get_the_post_thumbnail( $post_id ),
        ) );

    }

    wp_send_json_error();

}
add_action( 'wp_ajax_gamipress_credly_builder_save',  'gamipress_credly_ajax_builder_save' );