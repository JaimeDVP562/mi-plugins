<?php
/**
 * Auto-Create Links AJAX Functions
 *
 * Handles the Create, Update and Delete bulk operations from the Settings page.
 * We process everything in batches (50 posts at a time) to ensure things
 * run buttery smooth without timing out in large sites!
 *
 * @package     ShortLinksPro\Auto_Create_Links
 * @author      ShortLinks Pro <contact@shortlinkspro.com>
 * @since       1.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Batch size for bulk operations
 *
 * @since 1.2.0
 */
define( 'SHORTLINKSPRO_AUTO_CREATE_BATCH_SIZE', 50 );

/**
 * AJAX: Create links for posts that don't have them yet (batch processed)
 *
 * @since 1.2.0
 */
function shortlinkspro_ajax_auto_create_links() {

    // Security check
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'shortlinkspro' ) );
    }

    // Let's grab what the user just submitted through the form - safely, of course!
    // Notice we take it straight from $_POST, not the saved options.
    $post_type = sanitize_text_field( $_POST['post_type'] );
    $prefix = sanitize_text_field( $_POST['prefix'] );
    $category = absint( $_POST['category'] );
    $tags = isset( $_POST['tags'] ) ? array_map( 'absint', $_POST['tags'] ) : array();
    $redirect_type = sanitize_text_field( $_POST['redirect_type'] );
    $link_options = isset( $_POST['link_options'] ) ? array_map( 'sanitize_text_field', $_POST['link_options'] ) : array();
    $offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

    if( empty( $post_type ) ) {
        wp_send_json_error( __( 'No post type specified.', 'shortlinkspro' ) );
    }

    // Get slug length from general settings
    $length = absint( shortlinkspro_get_option( 'slug_length', '4' ) );

    // First things first, how many posts actually need a link?
    // We need this total so we can give the user a neat progress update.
    $total_query = new WP_Query( array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_shortlinkspro_link_id',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ) );
    $total_remaining_before = $total_query->found_posts;

    // Okay, grab our current batch of published posts that are missing a link
    $posts = get_posts( array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => SHORTLINKSPRO_AUTO_CREATE_BATCH_SIZE,
        'meta_query'     => array(
            array(
                'key'     => '_shortlinkspro_link_id',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ) );

    $created_count = 0;

    foreach( $posts as $p ) {

        // Generate a unique slug
        $slug = shortlinkspro_generate_link_slug( $prefix, $length );

        // Build link data
        $link_data = array(
            'title'                 => $p->post_title,
            'url'                   => get_permalink( $p->ID ),
            'slug'                  => $slug,
            'redirect_type'         => $redirect_type,
            'nofollow'              => absint( in_array( 'nofollow', $link_options ) ),
            'sponsored'             => absint( in_array( 'sponsored', $link_options ) ),
            'parameter_forwarding'  => absint( in_array( 'parameter_forwarding', $link_options ) ),
            'tracking'              => absint( in_array( 'tracking', $link_options ) ),
            'author_id'             => get_current_user_id(),
            'created_at'            => gmdate( 'Y-m-d H:i:s' ),
            'updated_at'            => gmdate( 'Y-m-d H:i:s' ),
        );

        // Insert the link
        $ct_table = ct_setup_table( 'shortlinkspro_links' );
        $link_id = ct_insert_object( $link_data );
        ct_reset_setup_table();

        if( $link_id ) {
            $link_id = absint( $link_id );

            // Save post meta (Post -> Link relationship)
            update_post_meta( $p->ID, '_shortlinkspro_link_id', $link_id );

            // Save link metas (Link -> Post relationship)
            shortlinkspro_update_link_meta( $link_id, 'auto_create_post_id', $p->ID );
            shortlinkspro_update_link_meta( $link_id, 'auto_create_post_type', $post_type );

            // Handle category relationship
            if( $category > 0 ) {
                ct_setup_table( 'shortlinkspro_link_categories_relationships' );
                ct_set_object_terms( $link_id, $category );
                ct_reset_setup_table();
            }

            // Handle tags relationship
            if( ! empty( $tags ) ) {
                ct_setup_table( 'shortlinkspro_link_tags_relationships' );
                ct_set_object_terms( $link_id, $tags );
                ct_reset_setup_table();
            }

            $created_count++;
        }
    }

    // Calculate remaining posts
    $remaining = $total_remaining_before - $created_count;
    $processed_so_far = $offset + $created_count;

    wp_send_json_success( array(
        /* translators: %d: Number of links created in batch */
        'message'   => sprintf( __( '%d links created in this batch.', 'shortlinkspro' ), $created_count ),
        'count'     => $created_count,
        'remaining' => max( 0, $remaining ),
        'processed' => $processed_so_far,
        'total'     => $offset + $total_remaining_before,
        'done'      => ( $remaining <= 0 ),
    ) );

}
add_action( 'wp_ajax_shortlinkspro_auto_create_links', 'shortlinkspro_ajax_auto_create_links' );

/**
 * AJAX: Update existing auto-created links with new settings (batch processed)
 *
 * @since 1.2.0
 */
function shortlinkspro_ajax_auto_update_links() {

    // Security check
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'shortlinkspro' ) );
    }

    // Sanitize parameters
    $post_type = sanitize_text_field( $_POST['post_type'] );
    $category = absint( $_POST['category'] );
    $tags = isset( $_POST['tags'] ) ? array_map( 'absint', $_POST['tags'] ) : array();
    $redirect_type = sanitize_text_field( $_POST['redirect_type'] );
    $link_options = isset( $_POST['link_options'] ) ? array_map( 'sanitize_text_field', $_POST['link_options'] ) : array();
    $offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

    if( empty( $post_type ) ) {
        wp_send_json_error( __( 'No post type specified.', 'shortlinkspro' ) );
    }

    // Quick check: how many total posts already have a link?
    // This is purely so we can show a nice progress indicator.
    $total_query = new WP_Query( array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_shortlinkspro_link_id',
                'compare' => 'EXISTS',
            ),
        ),
    ) );
    $total = $total_query->found_posts;

    // Get a batch of posts that already have a link
    $posts = get_posts( array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => SHORTLINKSPRO_AUTO_CREATE_BATCH_SIZE,
        'offset'         => $offset,
        'meta_query'     => array(
            array(
                'key'     => '_shortlinkspro_link_id',
                'compare' => 'EXISTS',
            ),
        ),
    ) );

    $updated_count = 0;

    foreach( $posts as $p ) {

        $link_id = absint( get_post_meta( $p->ID, '_shortlinkspro_link_id', true ) );

        if( $link_id === 0 ) {
            continue;
        }

        // Build update data (do not change slug)
        $link_data = array(
            'id'                    => $link_id,
            'title'                 => $p->post_title,
            'url'                   => get_permalink( $p->ID ),
            'redirect_type'         => $redirect_type,
            'nofollow'              => absint( in_array( 'nofollow', $link_options ) ),
            'sponsored'             => absint( in_array( 'sponsored', $link_options ) ),
            'parameter_forwarding'  => absint( in_array( 'parameter_forwarding', $link_options ) ),
            'tracking'              => absint( in_array( 'tracking', $link_options ) ),
            'updated_at'            => gmdate( 'Y-m-d H:i:s' ),
        );

        // Update the link
        $ct_table = ct_setup_table( 'shortlinkspro_links' );
        ct_update_object( $link_data );
        ct_reset_setup_table();

        // Update category relationship
        if( $category > 0 ) {
            ct_setup_table( 'shortlinkspro_link_categories_relationships' );
            ct_set_object_terms( $link_id, $category );
            ct_reset_setup_table();
        }

        // Update tags relationship
        if( ! empty( $tags ) ) {
            ct_setup_table( 'shortlinkspro_link_tags_relationships' );
            ct_set_object_terms( $link_id, $tags );
            ct_reset_setup_table();
        }

        $updated_count++;
    }

    $processed_so_far = $offset + $updated_count;
    $remaining = $total - $processed_so_far;

    wp_send_json_success( array(
        /* translators: %d: Number of links updated in batch */
        'message'   => sprintf( __( '%d links updated in this batch.', 'shortlinkspro' ), $updated_count ),
        'count'     => $updated_count,
        'remaining' => max( 0, $remaining ),
        'processed' => $processed_so_far,
        'total'     => $total,
        'done'      => ( $remaining <= 0 || $updated_count === 0 ),
    ) );

}
add_action( 'wp_ajax_shortlinkspro_auto_update_links', 'shortlinkspro_ajax_auto_update_links' );

/**
 * AJAX: Delete all auto-created links for a specific post type (batch processed)
 *
 * @since 1.2.0
 */
function shortlinkspro_ajax_auto_delete_links() {

    // Security check
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'shortlinkspro' ) );
    }

    // Sanitize parameters
    $post_type = sanitize_text_field( $_POST['post_type'] );
    $offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

    if( empty( $post_type ) ) {
        wp_send_json_error( __( 'No post type specified.', 'shortlinkspro' ) );
    }

    // Get total count of posts with links (for progress)
    // Note: always query without offset since we're deleting and the list shrinks each batch
    $total_query = new WP_Query( array(
        'post_type'      => $post_type,
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_shortlinkspro_link_id',
                'compare' => 'EXISTS',
            ),
        ),
    ) );
    $total_remaining_before = $total_query->found_posts;

    // Let's pull a chunk of posts that currently have a link.
    // We always query from the top because as we delete them, the list naturally shrinks.
    $posts = get_posts( array(
        'post_type'      => $post_type,
        'post_status'    => 'any',
        'posts_per_page' => SHORTLINKSPRO_AUTO_CREATE_BATCH_SIZE,
        'meta_query'     => array(
            array(
                'key'     => '_shortlinkspro_link_id',
                'compare' => 'EXISTS',
            ),
        ),
    ) );

    $deleted_count = 0;

    foreach( $posts as $p ) {

        $link_id = absint( get_post_meta( $p->ID, '_shortlinkspro_link_id', true ) );

        if( $link_id === 0 ) {
            continue;
        }

        // Delete the link
        $ct_table = ct_setup_table( 'shortlinkspro_links' );
        ct_delete_object( $link_id );
        ct_reset_setup_table();

        // Remove the post meta
        delete_post_meta( $p->ID, '_shortlinkspro_link_id' );

        $deleted_count++;
    }

    $remaining = $total_remaining_before - $deleted_count;
    $processed_so_far = $offset + $deleted_count;

    wp_send_json_success( array(
        /* translators: %d: Number of links deleted in batch */
        'message'   => sprintf( __( '%d links deleted in this batch.', 'shortlinkspro' ), $deleted_count ),
        'count'     => $deleted_count,
        'remaining' => max( 0, $remaining ),
        'processed' => $processed_so_far,
        'total'     => $offset + $total_remaining_before,
        'done'      => ( $remaining <= 0 ),
    ) );

}
add_action( 'wp_ajax_shortlinkspro_auto_delete_links', 'shortlinkspro_ajax_auto_delete_links' );

/**
 * This adds the "Related Post" field straight into the SLP link edit screen.
 * It's all hooked into 'shortlinkspro_shortlinkspro_link_settings_fields'
 * so it pops up in the CMB2 meta box flawlessly.
 *
 * @since 1.2.0
 *
 * @param array $fields
 *
 * @return array
 */
function shortlinkspro_auto_create_links_edit_link_fields( $fields ) {

    $fields['auto_create_post_info'] = array(
        'name' => __( 'Related Post', 'shortlinkspro' ),
        'type' => 'title',
        'desc' => 'shortlinkspro_auto_create_post_info_desc',
        'show_on_cb' => 'shortlinkspro_auto_create_post_info_show',
    );

    return $fields;

}
add_filter( 'shortlinkspro_shortlinkspro_link_settings_fields', 'shortlinkspro_auto_create_links_edit_link_fields' );

/**
 * Show callback: only show the Related Post field if the link has an associated post
 *
 * @since 1.2.0
 *
 * @return bool
 */
function shortlinkspro_auto_create_post_info_show() {

    if( ! isset( $_GET['id'] ) ) {
        return false;
    }

    $link_id = absint( $_GET['id'] );
    $post_id = shortlinkspro_get_link_meta( $link_id, 'auto_create_post_id', true );

    return ! empty( $post_id );

}

/**
 * Add a custom field on the link edit screen showing the related post
 *
 * Uses admin_notices hook to render a postbox above the link form
 * on the SLP link edit page.
 *
 * @since 1.2.0
 */
function shortlinkspro_auto_create_links_show_related_post() {

    if( ! isset( $_GET['page'] ) || $_GET['page'] !== 'edit_shortlinkspro_links' ) {
        return;
    }

    if( ! isset( $_GET['id'] ) ) {
        return;
    }

    $link_id = absint( $_GET['id'] );
    $post_id = shortlinkspro_get_link_meta( $link_id, 'auto_create_post_id', true );

    if( empty( $post_id ) ) {
        return;
    }

    $post_id = absint( $post_id );
    $post = get_post( $post_id );

    if( ! $post ) {
        return;
    }

    ?>
    <div class="shortlinkspro-auto-create-related-post" style="margin-bottom: 15px;">
        <div class="postbox">
            <h2 class="hndle"><span><?php esc_html_e( 'Related Post', 'shortlinkspro' ); ?></span></h2>
            <div class="inside">
                <p>
                    <strong><?php esc_html_e( 'This link is related to:', 'shortlinkspro' ); ?></strong>
                    <a href="<?php echo esc_attr( get_edit_post_link( $post_id ) ); ?>" target="_blank">
                        <?php echo esc_html( $post->post_title ); ?>
                    </a>
                    (<?php echo esc_html( get_post_type_object( $post->post_type )->labels->singular_name ); ?> #<?php echo esc_html( $post_id ); ?>)
                </p>
            </div>
        </div>
    </div>
    <?php

}
add_action( 'admin_notices', 'shortlinkspro_auto_create_links_show_related_post' );
