<?php
/**
 * AJAX handlers
 *
 * @package ShortLinksPro\URL_Replacer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax replace
 *
 * @return void
 */
function slp_url_replacer_ajax_run_replace() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'slp_url_replacer_save' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Nonce inválido.', 'shortlinkspro-url-replacer' ),
			)
		);
	}

	$link_id = isset( $_POST['link_id'] ) ? absint( $_POST['link_id'] ) : 0;

	if ( empty( $link_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Link inválido.', 'shortlinkspro-url-replacer' ),
			)
		);
	}

	$count = slp_url_replacer_run_replace( $link_id );

	wp_send_json_success(
		array(
			'message' => sprintf(
				__( 'Reemplazo completado. Posts actualizados: %d', 'shortlinkspro-url-replacer' ),
				$count
			),
		)
	);
}

/**
 * Ajax cleanup
 *
 * @return void
 */
function slp_url_replacer_ajax_run_cleanup() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'slp_url_replacer_save' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Nonce inválido.', 'shortlinkspro-url-replacer' ),
			)
		);
	}

	$link_id = isset( $_POST['link_id'] ) ? absint( $_POST['link_id'] ) : 0;

	if ( empty( $link_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Link inválido.', 'shortlinkspro-url-replacer' ),
			)
		);
	}

	$count = slp_url_replacer_run_cleanup( $link_id );

	wp_send_json_success(
		array(
			'message' => sprintf(
				__( 'Limpieza completada. Posts actualizados: %d', 'shortlinkspro-url-replacer' ),
				$count
			),
		)
	);
}