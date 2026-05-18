<?php
/**
 * Redirect
 *
 * @package     ShortLinksPro\Redirect
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check for redirect
 *
 * @since 1.0.0
 */
function shortlinkspro_maybe_redirect() {

    // Bail on admin area
    if( is_admin() ) {
        return;
    }

    if( ! isset( $_SERVER["REQUEST_METHOD"] ) ) {
        return;
    }

    // Only accept GET requests
    if( $_SERVER["REQUEST_METHOD"] !== 'GET' ) {
        return;
    }

    // Bail on searches
    if( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
        return;
    }

    // Bail if can not detect requested URL
    if( ! isset( $_SERVER["REQUEST_URI"] ) ) {
        return;
    }

    // Sanitize request URL
    $request_uri = shortlinkspro_sanitize_request_uri( $_SERVER['REQUEST_URI'] );

    // Get the URL slug
    $parts = explode( '?', $request_uri, 2 );
    $slug = rtrim( current( $parts ), '/' );    
    $parameters = next( $parts );

    $link = shortlinkspro_get_link_by_slug( $slug );
    
    // Bail if link not found
    if( ! $link ) {
        return;
    }

    /**
     * Filter to decide if should redirect or not
     *
     * @since 1.0.0
     *
     * @param bool      $maybe_redirect Decides if redirect or not
     * @param stdClass  $link           Link object
     * @param string    $parameters     The query parameters
     *
     * @return bool
     */
    if( ! apply_filters( 'shortlinkspro_maybe_redirect', true, $link, $parameters ) ) {
        return;
    }

    shortlinkspro_redirect( $link, $parameters );

}
add_action( 'init', 'shortlinkspro_maybe_redirect', 1 );

/**
 * Link redirect
 *
 * @param stdClass $link Link object
 * @param string $parameters The query parameters
 *
 * @since 1.0.0
 */
function shortlinkspro_redirect( $link, $parameters ) {

    // Bail if link not found
    if( ! $link ) {
        return;
    }

    // Setup robots tags header
    $robots_tags = array();

    if( $link->nofollow) {
        $robots_tags[] = 'noindex';
        $robots_tags[] = 'nofollow';
    }
    if( $link->sponsored ) {
        $robots_tags[] = 'sponsored';
    }

    if( ! empty( $robots_tags ) ) {
        header( "X-Robots-Tag: " . implode( ', ', $robots_tags ), true );
    }

    // Setup the rest of headers
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sun, 22 Sep 1991 03:00:00 GMT"); // Ruben Garcia borns!
    header("X-Redirect-Powered-By: ShortLinksPro https://shortlinkspro.com");

    /**
     * Filter to override redirect URL (before parameter forwarding)
     *
     * @since 1.0.0
     *
     * @param string    $url        URL to redirect
     * @param stdClass  $link       Link object
     * @param string    $parameters The query parameters
     *
     * @return string
     */
    $url = apply_filters( 'shortlinkspro_redirect_url', $link->url, $link, $parameters );

    // Bail if URL to redirect is empty
    if( empty( $url ) ) {
        return;
    }

    // Forward parameters
    if( $link->parameter_forwarding && ! empty( $parameters ) ) {
        // Support for array parameters
        $parameters = preg_replace( array( "#%5B#i", "#%5D#i" ), array( "[", "]" ), $parameters );

        $url .= ( preg_match( "#\?#", $url ) ? '&' : '?' ) . $parameters;
    }

    /**
     * Filter to override redirect URL (after parameter forwarding)
     *
     * @since 1.0.0
     *
     * @param string    $url        URL to redirect
     * @param stdClass  $link       Link object
     * @param string    $parameters The query parameters
     *
     * @return string
     */
    $url = apply_filters( 'shortlinkspro_redirect_url_after_parameter_forwarding', $url, $link, $parameters );

    // Bail if URL to redirect is empty
    if( empty( $url ) ) {
        return;
    }

    /**
     * Before redirect
     *
     * @since 1.0.0
     *
     * @param stdClass  $link           Link object
     * @param string    $parameters     The query parameters
     * @param string    $url            URL to redirect
     */
    do_action( 'shortlinkspro_before_redirect', $link, $parameters, $url );

    switch ( $link->redirect_type ) {
        case '301':
            wp_redirect( esc_url_raw( $url ), 301 );
            exit;
        case '302':
            wp_redirect( esc_url_raw( $url ), 302 );
            exit;
        case '307':
            wp_redirect( esc_url_raw( $url ), 307 );
            exit;
        default:
            /**
             * Action to process the redirect
             *
             * @since 1.0.0
             *
             * @param stdClass  $link           Link object
             * @param string    $parameters     The query parameters
             * @param string    $url            URL to redirect
             */
            do_action( 'shortlinkspro_process_redirect', $link, $parameters, $url );
            break;
    }

}

/**
 * Process the default redirect
 *
 * @since 1.0.0
 *
 * @param stdClass  $link           Link object
 * @param string    $parameters     The query parameters
 * @param string    $url            URL to redirect
 */
function shortlinkspro_process_default_redirect( $link, $parameters, $url ) {

    wp_redirect( esc_url_raw( $url ) );
    exit;

}
add_action( 'shortlinkspro_process_redirect', 'shortlinkspro_process_default_redirect', 9999, 3 );