<?php
/**
 * Ajax Functions
 *
 * @package GamiPress\Credly\Ajax_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function gamipress_credly_ajax_authorize() {
    // Security check
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    $prefix = 'gamipress_credly_';

    $token = sanitize_text_field ( $_POST['token'] );
    $authorization = sanitize_text_field( $_POST['authorization'] );
    $url_api = gamipress_credly_get_api_url();
    
    if( empty( $token ) ){
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with the Credly API', 'gamipress-credly' ) ) );
    }

    $response = wp_remote_get( $url_api . 'organizations', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $authorization,
            'Content-Type'  => 'application/json'
        ),
        'sslverify' => false
    ) );

    if ( is_wp_error( $response ) ) {

        // Bail if there is a WP Error
        wp_send_json_error( array( 'message' => __( 'Error while connecting with the remote server', 'gamipress-credly' ) ) );

    } else {

        $data = json_decode( $response['body'] );
        $status_code = $response['response']['code'];
        $organizations = $data->data;

        if ( ! empty( $status_code ) && absint( $status_code ) === 200 ) {

            $settings = GamiPress()->settings;

            if( $settings === null ) {

                // If GamiPress is installed network wide, get settings from network options
                if( gamipress_is_network_wide_active() ) {
                    $settings = get_site_option( 'gamipress_settings' );
                } else {
                    $settings = get_option( 'gamipress_settings' );
                }

            }

            if( ! is_array( $settings ) ) {
                $settings = array();
            }

            foreach ($organizations as $organization){
                $id_organization = $organization->id;
                $name_organization = $organization->name;
            }


            // Store organization information
            $organization = array(
                'id_organization' => $id_organization,
                'name' => $name_organization,
            );

            // Update settings and auth tokens
            if( gamipress_is_network_wide_active() ) {

                $settings[$prefix . 'token'] = $token;
                $settings[$prefix. 'authorization_code'] = $authorization;
                update_site_option( 'gamipress_settings', $settings );
                update_site_option( $prefix . 'organization', $organization );

            } else {

                $settings[$prefix . 'token'] = $token;
                $settings[$prefix. 'authorization_code'] = $authorization;
                update_option( 'gamipress_settings', $settings );
                update_option( $prefix . 'organization', $organization );
                
            }

            // Return a success response
            wp_send_json_success( array( 'message' => __( 'Authentication success', 'gamipress-credly' ) ) );

        } else {

            // Bail if server responded with some error
            wp_send_json_error( array( 'message' => sprintf( __( 'Authorization token is not correct', 'gamipress-credly' ) ) ) );

        }
    }

}
add_action( 'wp_ajax_gamipress_credly_authorize',  'gamipress_credly_ajax_authorize' );
/**
 * AJAX handler for import achievements
 *
 * @since 1.0.0
 */
function gamipress_credly_ajax_import_achievements() {

    global $wpdb;

    // Security check
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    $prefix = '_gamipress_credly_';

    $auth = gamipress_credly_get_authorization_code();
    $achievement_type = sanitize_text_field( $_POST['achievement_type'] );
    $post_status = sanitize_text_field( $_POST['post_status'] );
    $loop = absint( $_POST['loop'] );
    $url_api = gamipress_credly_get_api_url();

    // Check parameters given
    if( empty( $achievement_type ) || empty( $post_status ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to import achievements', 'gamipress-credly' ) ) );
    }
    
    if( ! $auth ) {
        wp_send_json_error( array( 'message' => __( 'You need to provide your authorization details first', 'gamipress-credly' ) ) );
    }

    $organization = gamipress_credly_get_organization();

    $response = wp_remote_get( $url_api . 'organizations/'. $organization['id_organization'] .'/badge_templates', array(
        'headers' => array(
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . $auth,
            'Content-Type'  => 'application/json'
        ),
        'body' => array(
            'page'          => $loop,
            'per_page'      => 10
        )
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => __( 'Error while connecting with the remote server', 'gamipress-credly' ) ) );
    }

    $data = json_decode( $response['body'] );

    $status_code = $response['response']['code'];

    if ( absint( $status_code ) === 200 ) {

        $achievements = $data->data;
        $counter_active = 0;

        if( ! is_array( $achievements ) ) {
            wp_send_json_success( array( 'message' => __( 'Achievements imported successfully!', 'gamipress-credly' ) ) );
        }

        if( count( $achievements ) === 0 ) {
            wp_send_json_success( array( 'message' => __( 'Achievements imported successfully!', 'gamipress-credly' ) ) );
        }

        foreach( $achievements as $achievement ) {

            $remote_id = $achievement->id;
            $exists = absint( $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '{$prefix}remote_id' AND meta_value = '{$remote_id}'") );
            $criteria = $achievement->badge_template_activities;

            if ( $achievement->state === 'active' ) {
                $counter_active += 1;
            }

            foreach ( $criteria as $criteria_title ){
                $title = $criteria_title->title;
            }
            
            if( ! $exists && $achievement->state === 'active') {

                // Import the achievement data
                $post_id = wp_insert_post( array(
                    'post_title' => $achievement->name,
                    'post_type' => $achievement_type,
                    'post_status' => $post_status,
                ) );
                
                if( $post_id ) {

                    // Set post meta
                    gamipress_update_post_meta( $post_id, $prefix . 'remote_id', $remote_id );
                    gamipress_update_post_meta( $post_id, $prefix . 'sync', 'on' );
                    gamipress_update_post_meta( $post_id, $prefix . 'short_description', $achievement->description );
                    gamipress_update_post_meta( $post_id, $prefix . 'url', $achievement->url );
                    gamipress_update_post_meta( $post_id, $prefix . 'criteria', $title );
                    gamipress_update_post_meta( $post_id, $prefix . 'state', $achievement->state );
                   
                    if( ! empty( $achievement->image_url ) ) {

                        $thumbnail_id = gamipress_import_attachment( $achievement->image_url );

                        if( $thumbnail_id ) {
                            gamipress_update_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );
                        }

                    }

                }

            } else {
                
                // Update post if check is enabled
                $enable_update_badge = (bool) gamipress_credly_get_option( 'enable_update_badge', false );
                $auto_sync_status = (bool) gamipress_credly_get_option( 'auto_sync_status', false );

                if( $enable_update_badge || $auto_sync_status ) {

                    $post_update = array (
                        'ID'            => $exists,
                    );

                    if ( $enable_update_badge ) {

                        $post_update['post_title'] = $achievement->name;

                        // Update post meta
                        gamipress_update_post_meta( $exists, $prefix . 'short_description', $achievement->description );
                        gamipress_update_post_meta( $exists, $prefix . 'url', $achievement->url );
                        gamipress_update_post_meta( $exists, $prefix . 'state', $achievement->state );

                    }

                    if ( $auto_sync_status ) {
                        $post_update['post_status'] = $post_status;
                    }

                    wp_update_post( $post_update );

                }

            }

        }

        wp_send_json_success( array( 'imported' => $counter_active ) );

    } else {

        // Bail if server responded with some error
        wp_send_json_error( array( 'message' => sprintf( __( 'Error while connecting with the remote server', 'gamipress-credly' ) ) ) );

    }

}
add_action( 'wp_ajax_gamipress_credly_import_achievements',  'gamipress_credly_ajax_import_achievements' );


/**
 * AJAX handler for login
 *
 * @since 1.0.0
 */
function gamipress_credly_ajax_login() {

    $nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

    // Security check
    if ( ! wp_verify_nonce( $nonce, 'gamipress_credly_login_form' ) ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-credly' ) );
    }

    // Check the user ID
    $user_id = get_current_user_id();

    if( $user_id === 0 ) {
        wp_send_json_error( __( 'You need to log in to sync your account.', 'gamipress-credly' ) );
    }

    // Get the received email
    $email = isset( $_POST['email'] ) ? $_POST['email'] : '';

    if( empty( $email ) ) {
        wp_send_json_error( __( 'Please, enter a valid email address.', 'gamipress-credly' ) );
    }

    $remote_id = gamipress_credly_sync_user( $user_id, $email );

    if( $remote_id ) {
        // Sync earned badges in Gamipress
        gamipress_credly_auto_sync_badges( $user_id );
        wp_send_json_success( __( 'Your account has been synchronized with Credly successfully!', 'gamipress-credly' ) );
    } else {
        wp_send_json_error( __( 'Invalid email address, ensure the email address is correct and you have activated your Credly account.', 'gamipress-credly' ) );
    }

}
add_action( 'wp_ajax_gamipress_credly_login',  'gamipress_credly_ajax_login' );