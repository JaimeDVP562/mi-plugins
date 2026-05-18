<?php
/**
 * General Functions
 *
 * @package     AutomatorWP\Integrations\ElasticEmailSender
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to fetch and cache Elastic Email templates
 * * @since 1.0.0
 * @return array 
 */
function automatorwp_elasticmailsender_get_api_templates() {
    
    $options = array( '' => __( 'Select a template...', 'automatorwp-elasticmailsender' ) );
    $api_key = get_option( 'elastic_email_api_key', '' );
    
    if ( empty( $api_key ) ) {
        $options[''] = __( 'Error: API Key missing in settings', 'automatorwp-elasticmailsender' );
        return $options;
    }

    // Try to get from cache first (5 minutes)
    $cached_templates = get_transient( 'scc_elastic_templates_cache' );
    
    if ( false === $cached_templates ) {
        
        $base_url = 'https://api.elasticemail.com/v4/templates';
        $request_url = add_query_arg( array(
            'scopeType'  => 'Personal',
            'scopeTypes' => 'Personal'
        ), $base_url );

        $response = wp_remote_get( $request_url, array(
            'headers' => array( 'X-ElasticEmail-ApiKey' => $api_key ),
            'timeout' => 15
        ) );

        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );
            
            if ( ! empty( $data ) && is_array( $data ) ) {
                $cached_templates = array();
                foreach ( $data as $template ) {
                    if ( isset( $template['Name'] ) ) {
                        $template_name = sanitize_text_field( $template['Name'] );
                        $cached_templates[ $template_name ] = $template_name;
                    }
                }
                
                // Cache the clean array for 5 minutes
                set_transient( 'scc_elastic_templates_cache', $cached_templates, 300 );
            }
        }
    }

    if ( is_array( $cached_templates ) && ! empty( $cached_templates ) ) {
        $options = $options + $cached_templates;
    } else {
        $options = array( '' => __( 'No templates found', 'automatorwp-elasticmailsender' ) );
    }

    return $options;
}

/**
 * Helper function to fetch and cache Elastic Email Lists
 *
 * @since 1.0.0
 * @return array 
 */
function automatorwp_elasticmailsender_get_api_lists() {
    
    $options = array( '' => __( 'Select a list...', 'automatorwp-elasticmailsender' ) );
    $api_key = get_option( 'elastic_email_api_key', '' );
    
    if ( empty( $api_key ) ) {
        $options[''] = __( 'Error: API Key missing in settings', 'automatorwp-elasticmailsender' );
        return $options;
    }

    // Try to get from cache first (5 minutes)
    $cached_lists = get_transient( 'scc_elastic_lists_cache' );
    
    if ( false === $cached_lists ) {
        
        $request_url = 'https://api.elasticemail.com/v4/lists';

        $response = wp_remote_get( $request_url, array(
            'headers' => array( 'X-ElasticEmail-ApiKey' => $api_key ),
            'timeout' => 15
        ) );

        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );
            
            if ( ! empty( $data ) && is_array( $data ) ) {
                $cached_lists = array();
                foreach ( $data as $list ) {
                    // Elastic Email API returns 'ListName' for lists
                    if ( isset( $list['ListName'] ) ) {
                        $list_name = sanitize_text_field( $list['ListName'] );
                        $cached_lists[ $list_name ] = $list_name;
                    }
                }
                
                // Cache the clean array for 5 minutes
                set_transient( 'scc_elastic_lists_cache', $cached_lists, 300 );
            }
        }
    }

    if ( is_array( $cached_lists ) && ! empty( $cached_lists ) ) {
        $options = $options + $cached_lists;
    } else {
        $options = array( '' => __( 'No lists found', 'automatorwp-elasticmailsender' ) );
    }

    return $options;
}

/**
 * ============================================================================
 * WEBHOOK ROUTER FOR ELASTIC EMAIL EVENTS
 * ============================================================================
 */

add_action( 'rest_api_init', function () {
    register_rest_route( 'elasticmailsender/v1', '/webhook', array(
        'methods'             => 'POST',
        'callback'            => 'automatorwp_elasticmailsender_webhook_handler',
        'permission_callback' => '__return_true'
    ) );
} );

function automatorwp_elasticmailsender_webhook_handler( $request ) {
    
    $payload = $request->get_json_params();
    
    // Seguridad: Si no viene el estado o el correo, rechazamos la petición
    if ( empty( $payload['status'] ) || empty( $payload['to'] ) ) {
        return new WP_REST_Response( array( 'status' => 'error', 'reason' => 'Missing status or email' ), 400 );
    }

    $status = sanitize_text_field( $payload['status'] );
    $email  = sanitize_email( $payload['to'] );

    // Enrutador Maestro: Dispara un gancho (hook) distinto de AutomatorWP según el estado
    switch ( $status ) {
        case 'Opened':
            do_action( 'elasticmailsender_webhook_event_opened', $email );
            break;
        case 'Clicked':
            do_action( 'elasticmailsender_webhook_event_clicked', $email );
            break;
        case 'Unsubscribed':
            do_action( 'elasticmailsender_webhook_event_unsubscribed', $email );
            break;
        case 'Bounced':
            do_action( 'elasticmailsender_webhook_event_bounced', $email );
            break;
        case 'Complained':
        case 'Spam':
            do_action( 'elasticmailsender_webhook_event_spam', $email );
            break;
    }

    // Le decimos a Elastic Email que hemos recibido el paquete correctamente
    return new WP_REST_Response( array( 'status' => 'success' ), 200 );
}