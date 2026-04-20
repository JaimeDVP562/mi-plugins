<?php
/**
 * Tracking
 *
 * @package     ShortLinksPro\Tracking
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check for track click
 *
 * @param stdClass $link Link object
 * * @param string $parameters The query parameters
 *
 * @since 1.0.0
 */
function shortlinkspro_maybe_track_click( $link, $parameters, $url ) {

    global $shortlinkspro_detector;

    // Bail if link tracking is disabled
    if( ! $link->tracking ) {
        return;
    }

    // Check excluded IPs
    $excluded_ips = shortlinkspro_get_option( 'excluded_ips', '' );

    if( $excluded_ips !== '' ) {
        $client_ip = shortlinkspro_get_client_ip();
        $ips_to_check = shortlinkspro_get_ip_ranges( $client_ip );

        foreach( $ips_to_check as $ip ) {
            // Bail if user IP is excluded
            if ( strpos( $excluded_ips, $ip ) !== false ) {
                return;
            }
        }
    }

    require_once SHORTLINKSPRO_DIR . 'vendor/autoload.php';

    // Get the user agent
    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Setup a global device detector
    $shortlinkspro_detector = new DeviceDetector\DeviceDetector( $user_agent );
    $shortlinkspro_detector->parse();

    // Check exclude robots settings
    $exclude_robots = shortlinkspro_get_option( 'exclude_robots', '' );

    // Bail if is a robot
    if( $exclude_robots && $shortlinkspro_detector->isBot() ) {
        return;
    }

    shortlinkspro_track_click( $link, $parameters, $url );

}
add_action( 'shortlinkspro_before_redirect', 'shortlinkspro_maybe_track_click', 10, 3 );

/**
 * Track click
 *
 * @param stdClass  $link       Link object
 * @param string    $parameters The query parameters
 *
 * @since 1.0.0
 */
function shortlinkspro_track_click( $link, $parameters, $url ) {

    global $shortlinkspro_detector;

    $ip = shortlinkspro_get_client_ip();
    $geolocation = shortlinkspro_geolocate_ip( $ip );

    $click_data = array(
        'link_id'           => absint( $link->id ),
        'ip'                => $ip,
        'browser'           => DeviceDetector\Parser\Client\Browser::getBrowserFamily( $shortlinkspro_detector->getClient( 'name' ) ),
        'browser_version'   => $shortlinkspro_detector->getClient( 'version' ),
        'browser_type'      => $shortlinkspro_detector->getClient( 'type' ),
        'os'                => DeviceDetector\Parser\OperatingSystem::getOsFamily( $shortlinkspro_detector->getOs( 'name' ) ),
        'os_version'        => $shortlinkspro_detector->getOs( 'version' ),
        'device'            => $shortlinkspro_detector->getDeviceName(),
        'country'           => ( is_array( $geolocation ) && isset( $geolocation['country'] ) ? $geolocation['country'] : '' ),
        'bot'               => ( $shortlinkspro_detector->isBot() ? $shortlinkspro_detector->getBot() : '' ),
        'user_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
        'referrer'          => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url( sanitize_text_field( $_SERVER['HTTP_REFERER'] ) ) : '',
        'uri'               => isset( $_SERVER['REQUEST_URI'] ) ? esc_url( sanitize_text_field( $_SERVER['REQUEST_URI'] ) ) : '',
        'url'               => sanitize_text_field( $url ),
        'redirect_type'     => sanitize_text_field( $link->redirect_type ),
        'parameters'        => $parameters,
        'visitor_id'        => shortlinkspro_get_visitor_id(),
        'first_click'       => ( shortlinkspro_is_first_click( $link ) ? 1 : 0 ),
        'created_at'        => current_time( 'mysql' ),
    );

    // Prevent null values
    foreach( $click_data as $key => $value ) {
        if( $click_data[$key] === null ) {
            $click_data[$key] = '';
        }
    }

    if( empty( $click_data['browser'] ) || $click_data['browser'] === 'UNK' ) {
        $click_data['browser'] = 'unknown';
    }

    if( empty( $click_data['browser_version'] ) || $click_data['browser_version'] === 'UNK' ) {
        $click_data['browser_version'] = 'unknown';
    }

    if( empty( $click_data['os'] ) || $click_data['os'] === 'UNK' ) {
        $click_data['os'] = 'unknown';
    }

    if( empty( $click_data['os_version'] ) || $click_data['os_version'] === 'UNK' ) {
        $click_data['os_version'] = 'unknown';
    }

    if( empty( $click_data['device'] ) || $click_data['device'] === 'UNK' ) {
        $click_data['device'] = 'unknown';
    }

    /**
     * Filter available to extend tracking data
     *
     * @since 1.0.0
     *
     * @param array     $click_data Click data
     * @param stdClass  $link       Link Object
     * @param string    $parameters The query parameters
     *
     * @return array
     */
    $click_data = apply_filters( 'shortlinkspro_track_click_data', $click_data, $link, $parameters );

    ct_setup_table( 'shortlinkspro_clicks' );

    // Insert the click data
    $click_id = ct_insert_object( $click_data );

    /**
     * Action available when a click was tracked
     *
     * @since 1.0.0
     *
     * @param int       $click_id   Click ID
     * @param array     $click_data Click data
     * @param stdClass  $link       Link object
     * @param string    $parameters The query parameters
     */
    do_action( 'shortlinkspro_click_tracked', $click_id, $click_data, $link, $parameters );

    ct_reset_setup_table();

}

/**
 * Gets the client IP
 *
 * @since 1.0.0
 *
 * @return string
 */
function shortlinkspro_get_client_ip() {

    $ip = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '' ;
    $localhost = '127.0.0.1';

    if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && $_SERVER['HTTP_CLIENT_IP'] !== $localhost ) {
        $ip = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
    } else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== $localhost ) {
        $ip = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
    } else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) && $_SERVER['HTTP_X_FORWARDED'] !== $localhost ) {
        $ip = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED'] );
    } else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) && $_SERVER['HTTP_FORWARDED_FOR'] !== $localhost ) {
        $ip = sanitize_text_field( $_SERVER['HTTP_FORWARDED_FOR'] );
    } else if ( isset( $_SERVER['HTTP_FORWARDED'] ) && $_SERVER['HTTP_FORWARDED'] !== $localhost ) {
        $ip = sanitize_text_field( $_SERVER['HTTP_FORWARDED'] );
    }

    // Check if multiples IPs found
    $ips = explode(',', $ip);

    if( isset( $ips[1] ) ) {
        $ip = $ips[0];
    }

    return apply_filters( 'shortlinkspro_get_client_ip', $ip );

}

/**
 * Generate a set of IP ranges from the given IP like 1.1.1.1 -> 1.1.1.* -> 1.1.*.* -> 1.*.*.* -> *.*.*.*
 *
 * @since 1.0.0
 *
 * @param string $ip The IP
 *
 * @return array
 */
function shortlinkspro_get_ip_ranges( $ip ) {

    $ips = array();

    $parts = explode( '.', $ip );

    $ips[] = $ip; // 1.1.1.1

    if( count( $parts ) !== 4 ) {
        return $ips;
    }

    $ips[] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.*'; // 1.1.1.*
    $ips[] = $parts[0] . '.' . $parts[1] . '.*.*'; // 1.1.*.*
    $ips[] = $parts[0] . '.*.*.*'; // 1.*.*.*
    $ips[] = '*.*.*.*'; // *.*.*.*

    return $ips;

}

/**
 * Checks if first click on a link
 *
 * @since 1.0.0
 *
 * @param stdClass $link      The link object
 *
 * @return bool
 */
function shortlinkspro_is_first_click( $link ) {

    // Cookie name
    $cookie_name = 'shortlinkspro_click_' . $link->id;

    // Expiration for 30 days
    $expiration_time = time() + ( DAY_IN_SECONDS * 30 );

    if( ! isset( $_COOKIE[$cookie_name] ) ) {
        setcookie( $cookie_name, $link->slug, $expiration_time, '/', '', is_ssl() );

        return true;
    }

    return false;

}

/**
 * Geolocate IP
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_geolocate_ip( $ip ) {

    $masked_ip = md5( $ip );

    // Check for execution cache
    $cache_key = sanitize_key( "geolocate_ip_{$masked_ip}" );
    $cache = shortlinkspro_get_cache( $cache_key, false, false );

    if( is_array( $cache ) ) {
        return $cache;
    }

    // Check for transient cache
    $transient_key = sanitize_key( "shortlinkspro_geolocate_ip_{$masked_ip}" );
    $transient = get_transient( $transient_key );

    if( $transient !== false ) {
        return $transient;
    }

    // Get the geolocation provider
    $provider = shortlinkspro_get_geolocation_provider( $ip );

    // Geolocate IP
    $data = shortlinkspro_geolocation_request( $ip, $provider );

    // Update caches
    shortlinkspro_set_cache( $cache_key, $data, false );
    set_transient( $transient_key, $data, DAY_IN_SECONDS );

    return $data;

}

/**
 * Get the geolocation provider
 *
 * @since 1.0.0
 *
 * @param string $ip
 *
 * @return string
 */
function shortlinkspro_get_geolocation_provider( $ip ) {

    /**
     * Filter available to override geolocation provider
     *
     * @since 1.0.0
     *
     * @param string $provider
     * @param string $ip
     *
     * @return string
     */
    return apply_filters( 'shortlinkspro_geolocation_provider', 'ip-api', $ip );

}

/**
 * Geolocate IP request to the service
 *
 * @since 1.0.0
 *
 * @param string $ip
 * @param string $provider
 *
 * @return array
 */
function shortlinkspro_geolocation_request( $ip, $provider ) {

    /**
     * Filter available to override the geolocation request
     *
     * @since 1.0.0
     *
     * @param mixed $data
     * @param string $ip
     * @param string $provider
     *
     * @return mixed
     */
    $data = apply_filters( 'shortlinkspro_geolocation_request', 'no_override', $ip, $provider );

    if( $data !== 'no_override' ) {
        return $data;
    }

    $data = array();

    switch ( $provider ) {
        case 'ip-api':
            $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode";

            $response = wp_remote_get( $url );

            if( is_wp_error( $response ) ) {
                return array();
            }

            $content = json_decode( $response['body'], true );

            $data = array(
                'country' => ( isset( $content['countryCode'] ) ? sanitize_text_field( $content['countryCode'] ) : '' )
            );
            break;
        case 'geoplugin':
            $url = "http://www.geoplugin.net/json.gp?ip={$ip}";

            $response = wp_remote_get( $url );

            if( is_wp_error( $response ) ) {
                return array();
            }

            $content = json_decode( $response['body'], true );

            $data = array(
                'country' => ( isset( $content['geoplugin_countryCode'] ) ? sanitize_text_field( $content['geoplugin_countryCode'] ) : '' )
            );
            break;
        default:
            $response = array();
            $content = array();
            break;
    }

    return apply_filters( 'shortlinkspro_geolocation_request_data', $data, $ip, $provider, $content, $response );

}

/**
 * Gets the visitor ID
 *
 * @since 1.0.0
 *
 * @return string
 */
function shortlinkspro_get_visitor_id() {

    // Cookie name
    $visitor_cookie = 'shortlinkspro_visitor';

    // Expiration for 1 year
    $expiration_time = time() +  YEAR_IN_SECONDS;

    // Retrieve / Generate visitor id
    if( ! isset( $_COOKIE[$visitor_cookie] ) ) {
        $visitor_id = uniqid();
        setcookie( $visitor_cookie, $visitor_id, $expiration_time, '/', '', is_ssl() );
    } else {
        $visitor_id = sanitize_text_field( $_COOKIE[$visitor_cookie] );
    }

    return $visitor_id;

}