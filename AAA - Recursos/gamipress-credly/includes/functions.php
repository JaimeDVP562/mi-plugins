<?php
/**
 * Functions
 *
 * @package GamiPress\Credly\Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get API URL
 *
 * @since 1.0.0
 *
 * @return string
 */
function gamipress_credly_get_api_url() {
    
    $url = 'https://api.credly.com/v1/';
    //$url = 'https://sandbox-api.credly.com/v1/';

    /**
     * Filter to override API URL
     *
     * @since 1.0.0
     *
     * @param string $url The API URL
     *
     * @return string
     */
    return apply_filters( 'gamipress_credly_api_url', $url );

}

/**
 * Get store API authorization
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function gamipress_credly_get_auth() {

    $prefix = 'gamipress_credly_';

    if( gamipress_is_network_wide_active() ) {
        $settings = get_site_option( 'gamipress_settings' );
        return $settings[$prefix . 'token'];
    } else {
        $settings = get_option( 'gamipress_settings' );
        return $settings[$prefix . 'token'];
    }

}

/**
 * Get store authorization code
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function gamipress_credly_get_authorization_code() {

    $prefix = 'gamipress_credly_';

    if( gamipress_is_network_wide_active() ) {
        $settings = get_site_option( 'gamipress_settings' );
        return $settings[$prefix . 'authorization_code'];
    } else {
        $settings = get_option( 'gamipress_settings' );
        return $settings[$prefix . 'authorization_code'];
    }

}

/**
 * Get store Organization ID
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function gamipress_credly_get_organization() {

    $prefix = 'gamipress_credly_';

    if( gamipress_is_network_wide_active() ) {
        return get_site_option( $prefix . 'organization' );
    } else {
        return get_option( $prefix . 'organization' );
    }

}

/**
 * Sync achievement
 *
 * @since 1.0.0
 *
 * @param int $achievement_id
 * @param bool $saving
 */
function gamipress_credly_sync_achievement( $achievement_id, $saving = false ) {

    // TODO: Actually, Credly hasn't an endpoint to sync achievements

}

/**
 * Desync achievement
 *
 * @since 1.0.0
 *
 * @param int $achievement_id
 */
function gamipress_credly_desync_achievement( $achievement_id ) {

    // TODO: Actually, Credly hasn't an endpoint to delete achievements

}

/**
 * Sync user
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @param string $user_email
 *
 * @return int|false
 */
function gamipress_credly_sync_user( $user_id, $user_email ) {

    $prefix = '_gamipress_credly_';

    $remote_id = gamipress_get_user_meta( $user_id, $prefix . 'remote_id', true );

    // Bail since user already synchronized
    if( ! empty( $remote_id ) ) {
        return $remote_id;
    }

    $remote_id = gamipress_credly_get_user_remote_id( $user_id, $user_email );

    if( $remote_id ) {

        $date = current_time( 'Y-m-d H:i:s O' );

        // Update remote ID, email and auto sync status
        gamipress_update_user_meta( $user_id, $prefix . 'remote_id', $remote_id );
        gamipress_update_user_meta( $user_id, $prefix . 'email', $user_email );
        gamipress_update_user_meta( $user_id, $prefix . 'sync_date', $date );
        gamipress_update_user_meta( $user_id, $prefix . 'auto_sync_status', 'completed' );

        return $remote_id;

    }

    return false;

}


/**
 * Get user remote ID
 *
 * @since 1.0.0
 *
 * @param int       $user_id
 * @param string    $user_email Optional, if user already has a remote ID
 *
 * @return int|false
 */
function gamipress_credly_get_user_remote_id( $user_id, $user_email = '' ) {

    $prefix = '_gamipress_credly_';

    $auth = gamipress_credly_get_auth();

    // Bail if authorization hasn't been setup yet
    if( ! $auth ) {
        return false;
    }

    $remote_id = gamipress_get_user_meta( $user_id, $prefix . 'remote_id', true );

    // Return the remote ID if exists
    if( ! empty( $remote_id ) ) {
        return $remote_id;
    }

    // Bail if not email provided
    if( empty( $user_email ) ) {
        return false;
    }
    
    return $user_email;

}

/**
 * User sync earned badges after sync
 *
 * @since 1.0.0
 *
 * @param string  $user_login Username.
 * @param WP_User $user       WP_User object of the logged-in user.
 */
function gamipress_credly_auto_sync_badges( $user_id ) {

    global $wpdb;
    $credly_badges = array();

    if( (bool) gamipress_credly_get_option( 'auto_sync_users', false ) ) {

        $prefix = '_gamipress_credly_';

        $auth = gamipress_credly_get_auth();

        // Bail if authorization hasn't been setup yet
        if( ! $auth ) {
            return false;
        }

        $remote_id = gamipress_get_user_meta( $user_id, $prefix . 'remote_id', true );

        // Return the remote ID if exists
        if( empty( $remote_id ) ) {
            return;
        }

        // Get badges from Credly
        $response = gamipress_credly_get_user_earning( $user_id );

        foreach ( $response as $credly_badge ){
            $credly_badges[] = $credly_badge->badge_template->id;
        }

        // Earned achievements by user
        $user_earnings = gamipress_get_user_earned_achievement_ids( $user_id );

        // GamiPress Credly achievements
        $results = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_gamipress_credly_remote_id'" );
        
        foreach( $results as $badge ){

            if ( in_array( $badge->post_id, $user_earnings ) ) {

                if ( ! in_array( $badge->meta_value, $credly_badges ) )

                    gamipress_credly_sync_user_earning( $user_id, $badge->post_id );
                    
            }

        } 
    }

}

/**
 * Sync user earning
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @param int $achievement_id
 */
function gamipress_credly_sync_user_earning( $user_id, $achievement_id ) {

    $prefix = '_gamipress_credly_';

    $auth = gamipress_credly_get_auth();
    $authorization = gamipress_credly_get_authorization_code();
    $organization = gamipress_credly_get_organization();

    // Bail if authorization hasn't been setup yet
    if( ! $auth ) {
        return;
    }

    $user_remote_id = gamipress_get_user_meta( $user_id, $prefix . 'remote_id', true );

    // Bail if user is not synchronized
    if( empty( $user_remote_id ) ) {
        return;
    }

    $achievement_remote_id = gamipress_get_post_meta( $achievement_id, $prefix . 'remote_id', true );

    // Bail if achievement is not synchronized
    if( empty( $achievement_remote_id ) ) {
        return;
    }

    $user_data = get_user_by('id', $user_id);

    if (empty( $user_data->first_name)){
        $first_name = '';
    } else{
        $first_name = $user_data->first_name;
    }

    if (empty( $user_data->last_name)){
        $last_name = '';
    } else{
        $last_name = $user_data->last_name;
    }

    $date = current_time( 'Y-m-d H:i:s O' );   

    $response = wp_remote_post( gamipress_credly_get_api_url() . 'organizations/'.$organization['id_organization'].'/badges', array(
        'headers' => array(
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . $authorization,
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode(array(
            'badge_template_id'                 => $achievement_remote_id,
            'issued_at'                         => $date,
            'issued_to_first_name'              => $first_name,
            'issued_to_last_name'               => $last_name,
            'recipient_email'                   => $user_remote_id,
            'suppress_badge_notification_email' => false
        )
    ) ) );   

}

/**
 * Get user earning in Credly
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @return array
 */
function gamipress_credly_get_user_earning( $user_id ) {

    $prefix = '_gamipress_credly_';

    $auth = gamipress_credly_get_auth();
    $authorization = gamipress_credly_get_authorization_code();
    $organization = gamipress_credly_get_organization();

    // Bail if authorization hasn't been setup yet
    if( ! $auth ) {
        return;
    }

    $user_remote_id = gamipress_get_user_meta( $user_id, $prefix . 'remote_id', true );

    // Bail if user is not synchronized
    if( empty( $user_remote_id ) ) {
        return;
    }


    $response = wp_remote_get( gamipress_credly_get_api_url() . 'organizations/'.$organization['id_organization'].'/badges?filter=recipient_email::'.$user_remote_id, array(
        'headers' => array(
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . $authorization,
            'Content-Type'  => 'application/json'
        ) ) );   

    $response = json_decode( wp_remote_retrieve_body( $response ) );

    return $response->data;

}

/**
 * Desync user earning
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @param int $achievement_id
 */
function gamipress_credly_desync_user_earning( $user_id, $achievement_id ) {

    // TODO: Actually, Credly hasn't an endpoint to revoke user earnings

}