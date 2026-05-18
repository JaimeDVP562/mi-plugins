<?php
/**
 * Functions
 *
 * @package GamiPress\Points_Based_Ranks\Functions
 *
 * This file contains utility functions for the Points-Based Ranks Tool plugin.
 * Includes translation, letter conversion, image processing, and data sorting functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Convert a number to letters (A, B, C, ... Z, AA, AB, ...).
 *
 * Generates alphabetic sequences similar to Excel column headers.
 * Used for generating letter-based rank identifiers.
 *
 * @since 1.0.0
 *
 * @param int $number The positive integer to convert (1-based).
 * @return string The corresponding uppercase letters (A, B, ..., Z, AA, AB, ...).
 */
function gprbt_number_to_letters( $number ) {
    $letters = '';

    while ( $number > 0 ) {
        $mod = ( $number - 1 ) % 26;
        $letters = chr( 65 + $mod ) . $letters;
        $number = intval( ( $number - 1 ) / 26 );
    }

    return $letters;
}

/**
 * Sanitize a base64 image string.
 *
 * Validates that the provided string is a properly formatted base64-encoded
 * image with supported image types (PNG, JPEG).
 *
 * @since 1.0.0
 *
 * @param string $image Base64 image string to sanitize.
 * @return string Sanitized image string if valid, empty string if invalid.
 */
function gprbt_sanitize_base64_image( $image ) {
    $image = trim( $image );

    if ( preg_match( '#^data:image\/(png|jpeg|jpg);base64,#', $image ) ) {
        return $image;
    }

    return '';
}

/**
 * Save a base64 badge image as a post thumbnail.
 *
 * Decodes a base64-encoded image, uploads it to WordPress media library,
 * and attaches it as the featured image (thumbnail) for a specified post.
 *
 * @since 1.0.0
 *
 * @param int    $post_id      The WordPress post ID to attach the thumbnail to.
 * @param string $base64_image Base64 encoded image data with data URI prefix.
 * @return int|false Attachment ID on success, false on failure.
 */
function gprbt_save_base64_image_as_post_thumbnail( $post_id, $base64_image ) {
    $base64_image = gprbt_sanitize_base64_image( $base64_image );

    if ( '' === $base64_image ) {
        return false;
    }

    list( $type, $data ) = explode( ',', $base64_image, 2 );
    $mime = str_replace( 'data:', '', strstr( $type, ';', true ) );
    $extension = 'png';

    if ( 'image/jpeg' === $mime ) {
        $extension = 'jpg';
    }

    $decoded = base64_decode( $data );

    if ( false === $decoded ) {
        return false;
    }

    $upload = wp_upload_bits( 'gprbt-rank-badge-' . $post_id . '-' . uniqid() . '.' . $extension, null, $decoded );

    if ( ! empty( $upload['error'] ) ) {
        return false;
    }

    if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $filetype = wp_check_filetype( $upload['file'], null );

    $attachment_id = wp_insert_attachment( array(
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name( pathinfo( $upload['file'], PATHINFO_FILENAME ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ), $upload['file'], $post_id );

    if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
        return false;
    }

    $attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    wp_update_attachment_metadata( $attachment_id, $attach_data );

    set_post_thumbnail( $post_id, $attachment_id );

    return $attachment_id;
}

/**
 * Sort rank IDs by menu order.
 *
 * Retrieves the menu_order value for each rank and returns the IDs
 * sorted in ascending order. This ensures ranks are processed in the
 * correct priority sequence.
 *
 * @since 1.0.0
 *
 * @param array $rank_ids Array of WordPress post IDs for ranks.
 * @return array Sorted array of rank IDs by menu order, or empty array on failure.
 */
function gprbt_sort_rank_ids_by_menu_order( $rank_ids ) {
    $rank_ids = array_filter( array_map( 'absint', $rank_ids ) );

    if ( empty( $rank_ids ) ) {
        return array();
    }

    $order = array();

    foreach ( $rank_ids as $rank_id ) {
        $order[ $rank_id ] = (int) gamipress_get_post_field( 'menu_order', $rank_id );
    }

    asort( $order );

    return array_keys( $order );
}
