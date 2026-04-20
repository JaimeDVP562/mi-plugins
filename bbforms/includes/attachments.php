<?php
/**
 * Attachments
 *
 * @package     BBForms\Attachments
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Attempt to find the attachment ID from the file URL or, if comes from external URL, attempt to download and insert it as attachment
 *
 * @since 1.0.0
 *
 * @param string $url       Attachment URL
 *
 * @return int|WP_Error     Attachment ID on success, WP_Error otherwise
 */
function bbforms_import_attachment( $url ) {

    if( empty( $url ) ) {
        return new WP_Error( 'bbforms_attachment_import_error', __('Trying to import an empty attachment URL', 'bbforms') );
    }

    $wp_filesystem = bbforms_get_filesystem();
    $exists = $wp_filesystem->exists( $url );

    if( $exists ) {
        // Try to fnid the attachment by file path
        $thumbnail_id = bbforms_get_attachment_id_from_path( $url );
    } else {
        // Try to find the attachment by URL
        $thumbnail_id = bbforms_get_attachment_id_from_url( $url );
    }

    if( $thumbnail_id ) {
        return $thumbnail_id;
    } else {

        if( $exists ) {
            // Is a file stored in our server but have no attachment
            return bbforms_insert_local_attachment( $url );
        } else {
            // Is a external file, so import it
            return bbforms_insert_external_attachment( $url );
        }
    }

}

/**
 * Retrieves the attachment ID from the file URL
 *
 * @since 1.0.0
 *
 * @param string $url   File URL
 *
 * @return int|false    Attachment ID on success, false otherwise
 */
function bbforms_get_attachment_id_from_url( $url ) {

    global $wpdb;

    // Try to get the result from cache to prevent duplicated queries
    $cache_key = sanitize_key( "attachment_id_from_url_{$url}" );
    $cache = bbforms_get_cache( $cache_key );

    if( $cache !== null ) {
        return $cache;
    }

    $attachment_id = absint( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid=%s LIMIT 1", $url ) ) );

    // Cache result
    if( $attachment_id !== 0 ) {
        bbforms_set_cache( $cache_key, $attachment_id );
    }

    return $attachment_id !== 0 ? $attachment_id : false;

}

/**
 * Retrieves the attachment ID from the file path
 *
 * @since 1.0.0
 *
 * @param string $path   File Path
 *
 * @return int|false    Attachment ID on success, false otherwise
 */
function bbforms_get_attachment_id_from_path( $path ) {

    global $wpdb;

    // Try to get the result from cache to prevent duplicated queries
    $cache_key = sanitize_key( "attachment_id_from_path_{$path}" );
    $cache = bbforms_get_cache( $cache_key );

    if( $cache !== null ) {
        return $cache;
    }

    $upload_dir = wp_upload_dir();

    $path = str_replace( $upload_dir['basedir'], '', $path );
    $path = trim( $path, '/' );

    $attachment_id = absint( $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key=%s AND meta_value=%s LIMIT 1",
        '_wp_attached_file',
        $path
    ) ) );

    if( $attachment_id !== 0 ) {
        $attachment = get_post( $attachment_id );

        // Ensure that attachment exists
        if( $attachment ) {
            // Cache result
            bbforms_set_cache( $cache_key, $attachment_id );

            return absint( $attachment_id );
        }
    }

    return false;

}

/**
 * Attempt to create a new attachment from a local URL
 *
 * @since 1.0.0
 *
 * @param string $url URL or path to file
 * @param array $post Attachment post details
 *
 * @return int|WP_Error Post ID on success, WP_Error otherwise
 */
function bbforms_insert_local_attachment( $url, $post = array() ) {

    if( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
    }

    if ( $info = wp_check_filetype( $url ) )
        $post['post_mime_type'] = $info['type'];
    else
        return new WP_Error( 'bbforms_attachment_processing_error', __('Invalid file type', 'bbforms') );

    if( ! isset( $post['post_title'] ) ) {
        $post['post_title'] = preg_replace( '/\.[^.]+$/', '', wp_basename( $url ) );
    }

    $post_id = wp_insert_attachment( $post, $url );

    if( ! is_wp_error( $post_id ) )
        wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $url ) );

    return $post_id;

}

/**
 * Attempt to create a new attachment from an external URL
 *
 * @since 1.0.0
 *
 * @param string $url URL to fetch attachment from
 * @param array $post Attachment post details
 *
 * @return int|WP_Error Post ID on success, WP_Error otherwise
 */
function bbforms_insert_external_attachment( $url, $post = array() ) {

    if( ! function_exists( 'media_sideload_image' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
    }

    return media_sideload_image( $url, 0, null, 'id' );

}