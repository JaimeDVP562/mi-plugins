<?php
/**
 * Filters
 *
 * @package     ShortLinksPro_Dynamic_Redirects\Filters
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Redirect URL
 *
 * @param string $url URL to redirect
 * @param stdClass $link Link object
 * @param string $parameters The query parameters
 *
 * @return string
 */
function shortlinkspro_dynamic_redirects_redirect_url(  $url, $link, $parameters ) {

    $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url( $link );

    // Return dynamic URL
    if( $dynamic_url ) {
        return $dynamic_url;
    }

    return $url;

}
add_filter( 'shortlinkspro_redirect_url', 'shortlinkspro_dynamic_redirects_redirect_url', 10, 3 );

/**
 * Get the dynamic URL from a link
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url( $link ) {

    $dynamic_redirect = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect', true );
    $dynamic_url = false;

    switch( $dynamic_redirect ) {
        case 'rotation':
            $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url_from_rotation( $link );
            break;
        case 'geographic':
            $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url_from_geographic( $link );
            break;
        case 'technology':
            $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url_from_technology( $link );
            break;
        case 'referrer':
            $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url_from_referrer( $link );
            break;
        case 'date_range':
            $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url_from_date_range( $link );
            break;
        case 'time_range':
            $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url_from_time_range( $link );
            break;
        case 'clicks':
            $dynamic_url = shortlinkspro_dynamic_redirects_get_dynamic_url_from_clicks( $link );
            break;
    }

    return $dynamic_url;

}

/**
 * Rotation
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url_from_rotation( $link ) {

    $dynamic_url = false;

    $rules = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect_rotation_fields', true );

    // Bail if not rules defined
    if( ! is_array( $rules ) ) {
        return $dynamic_url;
    }

    if( count( $rules ) === 0 ) {
        return $dynamic_url;
    }

    $rotation_urls = array();

    foreach( $rules as $rule ) {
        if( ! empty( $rule['url'] ) && absint( $rule['weight'] ) > 0 ) {
            // Build an array of URLs repeated the same times as their weight
            $rotation_urls = array_merge(
                $rotation_urls,
                array_fill( 0, absint( $rule['weight'] ), $rule['url'] )
            );
        }
    }

    if( count( $rotation_urls ) ) {
        // Choose a random URL in the array
        $dynamic_url = $rotation_urls[array_rand( $rotation_urls)];
    }

    return $dynamic_url;

}

/**
 * Geographic
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url_from_geographic( $link ) {

    $dynamic_url = false;

    $rules = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect_geographic_fields', true );

    // Bail if not rules defined
    if( ! is_array( $rules ) ) {
        return $dynamic_url;
    }

    if( count( $rules ) === 0 ) {
        return $dynamic_url;
    }

    $ip = shortlinkspro_get_client_ip();
    $geolocation = shortlinkspro_geolocate_ip( $ip );
    $country = ( is_array( $geolocation ) && isset( $geolocation['country'] ) ? $geolocation['country'] : '' );

    // Bail if can not locate the IP
    if( empty( $country ) ) {
        return $dynamic_url;
    }

    foreach( $rules as $rule ) {
        if( ! empty( $rule['url'] ) && count( $rule['countries'] ) ) {
            // Update dynamic URL if country matches
            if( in_array( $country, $rule['countries'] ) ) {
                $dynamic_url = $rule['url'];
                break;
            }
        }
    }

    return $dynamic_url;

}

/**
 * Technology
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url_from_technology( $link ) {

    $dynamic_url = false;

    $rules = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect_technology_fields', true );

    // Bail if not rules defined
    if( ! is_array( $rules ) ) {
        return $dynamic_url;
    }

    if( count( $rules ) === 0 ) {
        return $dynamic_url;
    }

    // Get the user agent
    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

    // Bail if can not detect technology
    if( empty( $user_agent ) ) {
        return $dynamic_url;
    }

    // Setup the device detector
    $dd = new DeviceDetector\DeviceDetector( $user_agent );
    $dd->parse();

    // Get the important bits
    $device = $dd->getDeviceName();
    $os = DeviceDetector\Parser\OperatingSystem::getOsFamily( $dd->getOs( 'name' ) );
    $browser = DeviceDetector\Parser\Client\Browser::getBrowserFamily( $dd->getClient( 'name' ) );

    // Sanitize values
    if( $device === '' || $device === 'UNK' ) {
        $device = 'unknown';
    }

    if( $os === '' || $os === 'UNK' ) {
        $os = 'unknown';
    }

    if( $browser === '' || $browser === 'UNK' ) {
        $browser = 'unknown';
    }

    $device = strtolower( $device );
    $os = strtolower( $os );
    $browser = strtolower( $browser );

    $device = str_replace( ' ', '-', $device );
    $os = str_replace( ' ', '-', $os );
    $browser = str_replace( ' ', '-', $browser );

    // Check rules
    foreach( $rules as $rule ) {
        if( ! empty( $rule['url'] ) ) {

            $meet_device = false;
            $meet_os = false;
            $meet_browser = false;

            if( $rule['device'] === 'any' || $rule['device'] === $device ) {
                $meet_device = true;
            }

            if( $rule['os'] === 'any' || $rule['os'] === $os ) {
                $meet_os = true;
            }

            // Linux OS
            if( ! $meet_os && $rule['os'] === 'linux' ) {
                // Let pass if OS contains linux
                if( strpos( $os, 'linux' ) !== false ) {
                    $meet_os = true;
                }

                // Let pass if is in a list of common linux
                if( in_array( $os, shortlinkspro_dynamic_redirects_get_linux_os() ) ) {
                    $meet_os = true;
                }

            }

            // Apple OS works for iOS and MacOS
            if( ! $meet_os && $os === 'apple' && in_array( $rule['os'], array( 'ios', 'mac' ) ) ) {
                $meet_os = true;
            }

            if( $rule['browser'] === 'any' || $rule['browser'] === $browser ) {
                $meet_browser = true;
            }


            // Update dynamic URL if everything matches
            if( $meet_device && $meet_os && $meet_browser ) {
                $dynamic_url = $rule['url'];
                break;
            }
        }
    }

    return $dynamic_url;

}

/**
 * Referrer
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url_from_referrer( $link ) {

    $dynamic_url = false;
    $rules = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect_referrer_fields', true );

    // Bail if no rules defined
    if( ! is_array( $rules ) || count( $rules ) === 0 ) {
        return $dynamic_url;
    }

    // Bail if no referer header
    $referer = isset( $_SERVER['HTTP_REFERER'] ) ? trim( $_SERVER['HTTP_REFERER'] ) : '';
    if( empty( $referer ) ) {
        return $dynamic_url;
    }

    $referer_host = parse_url( $referer, PHP_URL_HOST );
    $referer_host = strtolower( trim( $referer_host ) );

    if( empty( $referer_host ) ) {
        return $dynamic_url;
    }

    foreach( $rules as $rule ) {
        if( ! empty( $rule['url'] ) && ! empty( $rule['referrers'] ) ) {
            $referrer_values = explode( ',', $rule['referrers'] );

            foreach( $referrer_values as $referrer_value ) {
                $referrer_value = strtolower( trim( $referrer_value ) );
                if( empty( $referrer_value ) ) {
                    continue;
                }

                // If user passed a full URL, use only host
                $referrer_host = parse_url( $referrer_value, PHP_URL_HOST );
                if( ! empty( $referrer_host ) ) {
                    $referrer_value = $referrer_host;
                }

                // Match exact or subdomain
                if( $referer_host === $referrer_value || substr( $referer_host, -strlen( $referrer_value ) ) === $referrer_value || substr( $referer_host, -strlen( '.' . $referrer_value ) ) === '.' . $referrer_value ) {
                    $dynamic_url = $rule['url'];
                    break 2;
                }
            }
        }
    }

    return $dynamic_url;

}

/**
 * Date Range
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url_from_date_range( $link ) {

    $dynamic_url = false;

    $rules = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect_date_range_fields', true );

    // Bail if not rules defined
    if( ! is_array( $rules ) ) {
        return $dynamic_url;
    }

    if( count( $rules ) === 0 ) {
        return $dynamic_url;
    }

    $now = current_time( 'timestamp' );

    foreach( $rules as $rule ) {
        if( ! empty( $rule['url'] ) ) {

            $start = $now - 100;
            $end = $now + 100;

            if( absint( $rule['start'] ) > 0 ) {
                $start = absint( $rule['start'] );
            }

            if( absint( $rule['end'] ) > 0 ) {
                $end = absint( $rule['end'] );
            }

            // Update dynamic URL if date matches
            if( $now >= $start && $now <= $end ) {
                $dynamic_url = $rule['url'];
                break;
            }
        }
    }

    return $dynamic_url;

}

/**
 * Time Range
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url_from_time_range( $link ) {

    $dynamic_url = false;

    $rules = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect_time_range_fields', true );

    // Bail if not rules defined
    if( ! is_array( $rules ) ) {
        return $dynamic_url;
    }

    if( count( $rules ) === 0 ) {
        return $dynamic_url;
    }

    // Set all dates to 1991-09-22 (Ruben Garcia birthday) to only compare hours
    $now = date( '1991-09-22 H:i:s', current_time( 'timestamp' ) );
    $now = strtotime( $now );

    foreach( $rules as $rule ) {
        if( ! empty( $rule['url'] ) ) {

            $start = $now - 100;
            $end = $now + 100;

            if( ! empty( $rule['start'] ) ) {
                $start = strtotime( '1991-09-22 ' . $rule['start'] );
            }

            if( ! empty( $rule['end'] ) ) {
                $end = strtotime( '1991-09-22 ' . $rule['end'] );
            }

            // Update dynamic URL if time matches
            if( $now >= $start && $now <= $end ) {
                $dynamic_url = $rule['url'];
                break;
            }
        }
    }

    return $dynamic_url;

}

/**
 * Clicks
 *
 * @param stdClass $link Link object
 *
 * @return string|false
 */
function shortlinkspro_dynamic_redirects_get_dynamic_url_from_clicks( $link ) {

    $dynamic_url = false;

    // Bail if tracking not active for this link
    if( ! $link->tracking ) {
        return $dynamic_url;
    }

    $rules = shortlinkspro_get_link_meta( $link->id, 'dynamic_redirect_clicks_fields', true );

    // Bail if not rules defined
    if( ! is_array( $rules ) ) {
        return $dynamic_url;
    }

    if( count( $rules ) === 0 ) {
        return $dynamic_url;
    }

    // Get clicks count
    $clicks = shortlinkspro_get_link_clicks( $link->id );

    foreach( $rules as $rule ) {
        if( ! empty( $rule['url'] ) ) {

            $min = true;
            $max = true;

            if( absint( $rule['min'] ) > 0 ) {
                $min = $clicks >= absint( $rule['min'] );
            }

            if( absint( $rule['max'] ) > 0 ) {
                $max = $clicks <= absint( $rule['max'] );
            }

            // Update dynamic URL if time matches
            if( $min && $max ) {
                $dynamic_url = $rule['url'];
                break;
            }
        }
    }

    return $dynamic_url;

}