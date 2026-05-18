<?php
/**
 * Replacement logic
 *
 * @package ShortLinksPro\URL_Replacer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace URLs in content for one link
 *
 * @param int $link_id
 * @return int Number of updated posts
 */
function slp_url_replacer_run_replace( $link_id ) {
	$link_id    = absint( $link_id );
	$urls       = slp_url_replacer_get_urls( $link_id );
	$post_types = slp_url_replacer_get_post_types( $link_id );

	if ( empty( $link_id ) || empty( $urls ) || empty( $post_types ) ) {
		return 0;
	}

	$query = new WP_Query(
		array(
			'post_type'      => $post_types,
			'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			's'              => '',
		)
	);

	if ( empty( $query->posts ) ) {
		return 0;
	}

	$updated = 0;

	foreach ( $query->posts as $post_id ) {
		$content     = get_post_field( 'post_content', $post_id );
		$new_content = slp_url_replacer_replace_urls_in_content( $content, $link_id, $urls );

		if ( $new_content !== $content ) {
			remove_action( 'save_post', 'slp_url_replacer_parse_post_on_save', 20 );

			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $new_content,
				)
			);

			add_action( 'save_post', 'slp_url_replacer_parse_post_on_save', 20, 3 );

			$updated++;
		}
	}

	return $updated;
}

/**
 * Cleanup shortcodes for one link
 *
 * @param int $link_id
 * @return int
 */
function slp_url_replacer_run_cleanup( $link_id ) {
	$link_id = absint( $link_id );

	if ( empty( $link_id ) ) {
		return 0;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'any',
			'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	if ( empty( $query->posts ) ) {
		return 0;
	}

	$updated = 0;

	$pattern = '/\[slp_url_link\s+id=["\']' . preg_quote( (string) $link_id, '/' ) . '["\']\](.*?)\[\/slp_url_link\]/is';

	foreach ( $query->posts as $post_id ) {
		$content = get_post_field( 'post_content', $post_id );

		if ( ! preg_match( $pattern, $content ) ) {
			continue;
		}

		$new_content = preg_replace( $pattern, '$1', $content );

		if ( $new_content !== $content ) {
			remove_action( 'save_post', 'slp_url_replacer_parse_post_on_save', 20 );

			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $new_content,
				)
			);

			add_action( 'save_post', 'slp_url_replacer_parse_post_on_save', 20, 3 );

			$updated++;
		}
	}

	return $updated;
}

/**
 * Replace configured URLs in content
 *
 * Important:
 * - Avoid double wrapping
 * - Preserve params and anchors
 *
 * @param string $content
 * @param int    $link_id
 * @param array  $urls
 * @return string
 */
function slp_url_replacer_replace_urls_in_content( $content, $link_id, $urls ) {
	if ( empty( $content ) || empty( $urls ) ) {
		return $content;
	}

	foreach ( $urls as $base_url ) {
		$base_url = trim( $base_url );

		if ( empty( $base_url ) ) {
			continue;
		}

		$quoted = preg_quote( untrailingslashit( $base_url ), '/' );

		/**
		 * Match:
		 * - exact URL
		 * - URL with anchor
		 * - URL with query params
		 * - stop before spaces, quotes, angle brackets, closing bracket
		 */
		$pattern = '/(?<!\[slp_url_link id="(?:.*?)"\])(' . $quoted . '(?:[^\s"\']*)?)(?!\[\/slp_url_link\])/i';

		$content = preg_replace_callback(
			$pattern,
			function( $matches ) use ( $link_id ) {
				$full_url = isset( $matches[1] ) ? $matches[1] : '';

				if ( empty( $full_url ) ) {
					return $full_url;
				}

				if ( strpos( $full_url, '[/slp_url_link]' ) !== false ) {
					return $full_url;
				}

				return slp_url_replacer_build_shortcode( $link_id, $full_url );
			},
			$content
		);
	}

	return $content;
}

/**
 * Parse saved post automatically
 *
 * @param int     $post_id
 * @param WP_Post $post
 * @param bool    $update
 * @return void
 */
function slp_url_replacer_parse_post_on_save( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( empty( $post ) || ! isset( $post->post_type ) ) {
		return;
	}

	$link_query = new WP_Query(
		array(
			'post_type'      => slp_url_replacer_get_link_post_type(),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_slp_replace_post_types',
					'compare' => 'EXISTS',
				),
			),
		)
	);

	if ( empty( $link_query->posts ) ) {
		return;
	}

	$content     = $post->post_content;
	$new_content = $content;

	foreach ( $link_query->posts as $link_id ) {
		$post_types = slp_url_replacer_get_post_types( $link_id );

		if ( empty( $post_types ) || ! in_array( $post->post_type, $post_types, true ) ) {
			continue;
		}

		$urls = slp_url_replacer_get_urls( $link_id );

		if ( empty( $urls ) ) {
			continue;
		}

		$new_content = slp_url_replacer_replace_urls_in_content( $new_content, $link_id, $urls );
	}

	if ( $new_content !== $content ) {
		remove_action( 'save_post', 'slp_url_replacer_parse_post_on_save', 20 );

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $new_content,
			)
		);

		add_action( 'save_post', 'slp_url_replacer_parse_post_on_save', 20, 3 );
	}
}