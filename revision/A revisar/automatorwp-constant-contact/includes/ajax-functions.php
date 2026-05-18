<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\Constant_Contact\Ajax_Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Define the function before adding actions

// Function for handling AJAX authorization
function automatorwp_constant_contact_ajax_authorize() {
    // Seguridad del nonce
    if ( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'automatorwp_admin') ) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', 'automatorwp-constant-contact')));
    }

    $prefix = 'automatorwp_constant_contact_';

    $client_id = isset($_POST['client_id']) ? sanitize_text_field($_POST['client_id']) : '';
    $client_secret = isset($_POST['client_secret']) ? sanitize_text_field($_POST['client_secret']) : '';

    // Verificar si los parámetros están vacíos
    if (empty($client_id) || empty($client_secret)) {
        wp_send_json_error(array('message' => __('All fields are required to connect with Constant Contact', 'automatorwp-constant-contact')));
    }

    // Guardar los datos en la base de datos
    $settings = get_option('automatorwp_settings', array());
    $settings[$prefix . 'client_id'] = $client_id;
    $settings[$prefix . 'client_secret'] = $client_secret;
    update_option('automatorwp_settings', $settings);

    // Crear la URL de autorización
    $scope = 'contact_data campaign_data offline_access';
    $state = wp_create_nonce('automatorwp_constant_contact_state');
    $redirect_uri = admin_url('admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact');

    $auth_url = 'https://authz.constantcontact.com/oauth2/default/v1/authorize?' .
                'response_type=code' .
                '&client_id=' . urlencode($client_id) .
                '&redirect_uri=' . urlencode($redirect_uri) .
                '&scope=' . urlencode($scope) .
                '&state=' . urlencode($state);

    wp_send_json_success(array('message' => __('Authorization successful. Please wait...', 'automatorwp-constant-contact'), 'url' => $auth_url));
}


add_action( 'wp_ajax_automatorwp_constant_contact_authorize', 'automatorwp_constant_contact_ajax_authorize' );

// Function for handling the redirect after authorization
function automatorwp_handle_code_param() {
    if ( isset( $_GET['code'] ) ) {
        $settings = get_option( 'automatorwp_settings', array() );
        $client_id = isset($settings['automatorwp_constant_contact_client_id']) ? $settings['automatorwp_constant_contact_client_id'] : '';
        $client_secret = isset($settings['automatorwp_constant_contact_client_secret']) ? $settings['automatorwp_constant_contact_client_secret'] : '';

        if ( empty($client_id) || empty($client_secret) ) {
            wp_die('Error: client_id or client_secret are not set correctly.');
        }

        $code = sanitize_text_field( $_GET['code'] );
        $redirect_uri = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact' );
        
        $token_url = 'https://authz.constantcontact.com/oauth2/default/v1/token';
        $token_data = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri
        );

        $response = wp_remote_post( $token_url, array(
            'method'    => 'POST',
            'body'      => http_build_query( $token_data ),
            'headers'   => array( 'Content-Type' => 'application/x-www-form-urlencoded' )
        ) );

        if ( is_wp_error( $response ) ) {
            wp_die('Error: ' . $response->get_error_message());
        }

        $response_body = wp_remote_retrieve_body( $response );
        $token_info = json_decode( $response_body );

        if ( isset($token_info->access_token) && isset($token_info->refresh_token) ) {
            update_option( 'constant_contact_access_token', $token_info->access_token );
            update_option( 'constant_contact_refresh_token', $token_info->refresh_token );
        }
    }
}

// Add actions for AJAX functions
add_action( 'template_redirect', 'automatorwp_handle_code_param' );
?>