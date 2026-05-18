<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Shortio\Ajax_Functions
 * @since       1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action.
 */
function automatorwp_shortio_ajax_authorize() {
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
   
    if( empty( $api_key )) {
        wp_send_json_error( array( 'message' => __( 'API Key is required.', 'automatorwp-shortio' ) ) );
        return;
    }

    if ( ! automatorwp_shortio_check_api_key( $api_key ) ) {
        return; 
    }

    $settings = get_option( 'automatorwp_settings', array() );
    $settings['automatorwp_shortio_api_key'] = $api_key;
    update_option( 'automatorwp_settings', $settings );
   
    wp_send_json_success( array(
        'message'      => __( 'Successfully connected to Short.io!', 'automatorwp-shortio' ),
        'redirect_url' => admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-shortio' )
    ) );
}
add_action( 'wp_ajax_automatorwp_shortio_authorize', 'automatorwp_shortio_ajax_authorize' );

/**
 * Ajax function for selecting domains.
 */
function automatorwp_shortio_ajax_get_domains() {
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';
    $domains = automatorwp_shortio_get_domains();
    $results = array();

    foreach ( $domains as $domain ) {
        if( ! empty( $search ) && strpos( strtolower( $domain['hostname'] ), strtolower( $search ) ) === false ) {
            continue;
        }
        $results[] = array(
            'id'   => $domain['id'],
            'text' => $domain['hostname']
        );
    }

    wp_send_json_success( automatorwp_ajax_parse_extra_options( $results ) );
}
add_action( 'wp_ajax_automatorwp_shortio_get_domains', 'automatorwp_shortio_ajax_get_domains' );