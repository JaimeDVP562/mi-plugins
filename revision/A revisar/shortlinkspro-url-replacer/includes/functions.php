<?php
/**
 * Helper functions
 *
 * @package ShortLinksPro\URL_Replacer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IMPORTANT:
 * This is the only assumption-heavy part.
 *
 * Change this post type to the real ShortLinks Pro post type
 * after checking the plugin source.
 *
 * @return string
 */
function slp_url_replacer_get_link_post_type() {
	return apply_filters( 'slp_url_replacer_link_post_type', 'slp_link' );
}

/**
 * Get configured URLs
 *
 * @param int $link_id
 * @return array
 */
function slp_url_replacer_get_urls( $link_id ) {
	$urls = get_post_meta( $link_id, '_slp_replace_urls', true );

	if ( empty( $urls ) || ! is_array( $urls ) ) {
		return array();
	}

	$urls = array_map( 'trim', $urls );
	$urls = array_filter( $urls );
	$urls = array_values( array_unique( $urls ) );

	return $urls;
}

/**
 * Get configured post types
 *
 * @param int $link_id
 * @return array
 */
function slp_url_replacer_get_post_types( $link_id ) {
	$post_types = get_post_meta( $link_id, '_slp_replace_post_types', true );

	if ( empty( $post_types ) || ! is_array( $post_types ) ) {
		return array();
	}

	$post_types = array_map( 'sanitize_key', $post_types );
	$post_types = array_filter( $post_types );
	$post_types = array_values( array_unique( $post_types ) );

	return $post_types;
}

/**
 * Normalize pasted URLs string into tags array
 *
 * Supports:
 * a, b, c
 * a,b,c
 * a\nb\nc
 *
 * @param string $raw
 * @return array
 */
function slp_url_replacer_parse_urls_input( $raw ) {
	if ( is_array( $raw ) ) {
		$items = $raw;
	} else {
		$raw   = str_replace( array( "\r\n", "\r" ), "\n", (string) $raw );
		$raw   = str_replace( "\n", ',', $raw );
		$items = explode( ',', $raw );
	}

	$items = array_map( 'trim', $items );
	$items = array_filter( $items );

	$items = array_map( 'esc_url_raw', $items );
	$items = array_filter( $items );

	return array_values( array_unique( $items ) );
}

/**
 * Build shortcode
 *
 * @param int    $link_id
 * @param string $original_url
 * @return string
 */
function slp_url_replacer_build_shortcode( $link_id, $original_url ) {
	$link_id      = absint( $link_id );
	$original_url = trim( (string) $original_url );

	return '[slp_url_link id="' . $link_id . '"]' . $original_url . '[/slp_url_link]';
}

/**
 * Get available post types for replacement
 *
 * @return array
 */
function slp_url_replacer_get_available_post_types() {
	$post_types = get_post_types(
		array(
			'public' => true,
		),
		'objects'
	);

	unset( $post_types['attachment'] );

	return $post_types;
}