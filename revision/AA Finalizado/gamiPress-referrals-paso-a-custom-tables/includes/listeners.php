<?php
/**
 * Listeners
 *
 * @package GamiPress\Referrals\Listeners
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Referral visit listener
 *
 * @since 1.0.0
 */
function gamipress_referrals_visit_listener() {

    $url_parameter = gamipress_referrals_get_option( 'url_parameter', 'ref' );

    // Return if referral parameter not given or empty
    if( ! isset( $_GET[ $url_parameter ] ) ) return;
    if( empty( $_GET[ $url_parameter ] ) ) return;

    // Return if already tracked
    if( isset( $_COOKIE[ 'gamipress_referrals_ref' ] ) ) return;

    // Get the affiliate links setting
    $affiliate_links = gamipress_referrals_get_option( 'affiliate_links', 'user_id' );

    if( $affiliate_links === 'user_id' )
        $affiliate = gamipress_referrals_get_affiliate_by_user_id( $_GET[ $url_parameter ] );
    else if( $affiliate_links === 'user_login' )
        $affiliate = gamipress_referrals_get_affiliate_by_username( $_GET[ $url_parameter ] );
    else
        $affiliate = gamipress_referrals_get_affiliate( $_GET[ $url_parameter ] );

    // Return if affiliate not found
    if( ! $affiliate ) return;

    // Return if affiliate is trying to refer himself
    if( is_user_logged_in() && get_current_user_id() === $affiliate->ID ) return;

    // Setup vars
    $affiliate_id   = $affiliate->ID;
    $referral_id    = get_current_user_id();
    $referral_ip    = gamipress_referrals_get_ip();
    $post_id        = get_the_ID();
    $post_url       = gamipress_referrals_get_current_page_url();
    $referrer       = ! empty( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( $_SERVER['HTTP_REFERER'] ) : '';

    /**
     * Filter to skip a referral visit
     *
     * @since 1.0.0
     *
     * @param bool      $skip           Whatever if visit has been skipped or not
     * @param int       $affiliate_id   Affiliate ID
     * @param int       $referral_id    Referral ID
     * @param string    $referral_ip    Referral IP address
     * @param int       $post_id        Post ID
     * @param string    $post_url       Post URL
     * @param string    $referrer       Referrer
     * @param WP_User   $affiliate      Affiliate object
     *
     * @return bool
     */
    if ( true === apply_filters( 'gamipress_referrals_skip_visit', false, $affiliate_id, $referral_id, $referral_ip, $post_id, $post_url, $referrer, $affiliate ) ) {
        // Return if visit skipped
        return;
    }

    // Log the referral visit
    gamipress_referrals_log_visit( $affiliate_id, $referral_id, $referral_ip, $post_id, $post_url, $referrer );

    // Update the user's affiliate id
    gamipress_referrals_set_user_affiliate( $referral_id, $affiliate_id );

    // Trigger referral visit
    do_action( 'gamipress_referrals_referral_visit', $affiliate_id, $referral_id, $referral_ip, $post_id, $post_url, $referrer, $affiliate );

    // Trigger specific referral visit
    do_action( 'gamipress_referrals_specific_referral_visit', $affiliate_id, $referral_id, $referral_ip, $post_id, $post_url, $referrer, $affiliate );

    // Get the referral count
    if( gamipress_referrals_is_migrated() ) {
        $count = gamipress_referrals_count_referrals( array( 'user_id' => $affiliate_id, 'type' => 'referral_visit' ) );
    } else {
        $count = gamipress_get_user_log_count( $affiliate_id, array( 'type' => 'referral_visit' ) );
    }
    $count = max( $count, 0 );

    // Store the count in a meta
    gamipress_update_user_meta( $affiliate_id, 'gamipress_referrals_visits', $count );

    // Trigger referral visits
    do_action( 'gamipress_referrals_referral_visits', $affiliate_id, $count, $affiliate );

    // Set cookie to avoid duplications
    if ( ! headers_sent() ) {

        /**
         * Filter to cookie life time
         *
         * @since 1.0.0
         *
         * @param int       $lifetime       Lifetime timestamp, by default 1 day
         * @param int       $affiliate_id   Affiliate ID
         * @param int       $referral_id    Referral ID
         * @param WP_User   $affiliate      Affiliate object
         *
         * @return int
         */
        $cookie_lifetime = apply_filters( 'gamipress_referrals_cookie_lifetime', ( time() + 3600 * 24 ), $affiliate_id, $referral_id, $affiliate );

        setcookie( 'gamipress_referrals_ref', $_GET[ $url_parameter ], $cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN );

    }

    // Redirect removing the query parameter
    wp_redirect( remove_query_arg( $url_parameter ), 301 );
    exit;

}
add_action( 'template_redirect', 'gamipress_referrals_visit_listener', -9999 );

/**
 * Referral sign up listener
 *
 * @since 1.0.0
 *
 * @param int $user_id New registered user ID.
 */
function gamipress_referrals_signup_listener( $user_id ) {

    $affiliate = gamipress_referrals_get_affiliate_from_cookie( $user_id );

    // Return if affiliate not found
    if( ! $affiliate ) return;

    // Delete the cookie by setting an expired lifetime time
    if ( ! headers_sent() && isset( $_COOKIE['gamipress_referrals_ref'] ) ) {
        setcookie( 'gamipress_referrals_ref', $_COOKIE['gamipress_referrals_ref'], time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
    }

    // Setup vars
    $affiliate_id   = $affiliate->ID;
    $referral_id    = $user_id;
    $referral_ip    = gamipress_referrals_get_ip();

    /**
     * Filter to skip a referral sign up
     *
     * @since 1.0.0
     *
     * @param bool      $skip           Whatever if sign up has been skipped or not
     * @param int       $affiliate_id   Affiliate ID
     * @param int       $referral_id    Referral ID
     * @param string    $referral_ip    Referral IP address
     * @param WP_User   $affiliate      Affiliate object
     *
     * @return bool
     */
    if ( true === apply_filters( 'gamipress_referrals_skip_signup', false, $affiliate_id, $referral_id, $referral_ip, $affiliate ) ) {
        // Return if signup skipped
        return;
    }

    // Log the referral sign up
    gamipress_referrals_log_signup( $affiliate_id, $referral_id, $referral_ip );

    // Update the user's affiliate id
    gamipress_referrals_set_user_affiliate( $referral_id, $affiliate_id );

    // Trigger referral sign up
    do_action( 'gamipress_referrals_referral_signup', $affiliate_id, $referral_id, $referral_ip, $affiliate );

    // Trigger register to website through a referral
    do_action( 'gamipress_referrals_register', $affiliate_id, $referral_id, $referral_ip, $affiliate );

    // Get the referral count
    if( gamipress_referrals_is_migrated() ) {
        $count = gamipress_referrals_count_referrals( array( 'user_id' => $affiliate_id, 'type' => 'referral_signup' ) );
    } else {
        $count = gamipress_get_user_log_count( $affiliate_id, array( 'type' => 'referral_signup' ) );
    }
    $count = max( $count, 0 );

    // Store the count in a meta
    gamipress_update_user_meta( $affiliate_id, 'gamipress_referrals_signups', $count );

    // Trigger referral visits
    do_action( 'gamipress_referrals_referral_signups', $affiliate_id, $count, $affiliate );

}
add_action( 'user_register', 'gamipress_referrals_signup_listener' );

/**
 * Referral sale listener
 *
 * @since 1.0.0
 *
 * @param int       $sale_id        Sale ID.
 * @param int       $user_id        User ID.
 * @param string    $integration    Integration prefix.
 */
function gamipress_referrals_sale_listener( $sale_id, $user_id, $integration ) {

    $affiliate = gamipress_referrals_get_affiliate_from_cookie( $user_id );

    // Return if affiliate not found
    if( ! $affiliate ) return;

    // Setup vars
    $affiliate_id   = $affiliate->ID;
    $referral_id    = $user_id;
    $referral_ip    = gamipress_referrals_get_ip();

    $skip = false;

    $tracked = get_post_meta( $sale_id, '_gamipress_referrals_sale_tracked', true );

    // Skip already tracked sales
    if( (bool) $tracked ) {
        $skip = true;
    }

    /**
     * Filter to skip a referral sale
     *
     * @since 1.0.0
     *
     * @param bool      $skip           Whatever if sale has been skipped or not
     * @param int       $affiliate_id   Affiliate ID
     * @param int       $referral_id    Referral ID
     * @param string    $referral_ip    Referral IP address
     * @param int       $sale_id        Sale ID
     * @param string    $integration    Integration
     * @param WP_User   $affiliate      Affiliate object
     *
     * @return bool
     */
    if ( true === apply_filters( 'gamipress_referrals_skip_sale', $skip, $affiliate_id, $referral_id, $referral_ip, $sale_id, $integration, $affiliate ) ) {
        // Return if sale skipped
        return;
    }

    // Set a post meta to meet that sale has been tracked
    update_post_meta( $sale_id, '_gamipress_referrals_sale_tracked', '1' );

    // Log the referral sale
    gamipress_referrals_log_sale( $affiliate_id, $referral_id, $referral_ip, $sale_id, $integration );

    // Update the user's affiliate id
    gamipress_referrals_set_user_affiliate( $referral_id, $affiliate_id );

    // Award the sale commission
    gamipress_referrals_award_sale_commission( $affiliate_id, $referral_id, $sale_id, $integration );

    // Trigger referral sale
    do_action( "gamipress_referrals_{$integration}_referral_sale", $affiliate_id, $referral_id, $referral_ip, $sale_id, $affiliate );

    // Trigger complete a sale through a referral
    do_action( "gamipress_referrals_{$integration}_sale", $affiliate_id, $referral_id, $referral_ip, $sale_id, $affiliate );

    // Get the referral count
    if( gamipress_referrals_is_migrated() ) {
        $sales = gamipress_referrals_count_referrals( array( 'user_id' => $affiliate_id, 'type' => 'referral_sale' ) );
        $refunds = gamipress_referrals_count_referrals( array( 'user_id' => $affiliate_id, 'type' => 'referral_sale_refund' ) );
        $count = $sales - $refunds;
    } else {
        $count = gamipress_get_user_log_count( $affiliate_id, array( 'type' => 'referral_sale' ) ) - gamipress_get_user_log_count( $affiliate_id, array( 'type' => 'referral_sale_refund' ) );
    }
    $count = max( $count, 0 );

    // Store the count in a meta
    gamipress_update_user_meta( $affiliate_id, 'gamipress_referrals_sales', $count );

    // Trigger referral sales
    do_action( "gamipress_referrals_{$integration}_referral_sales", $affiliate_id, $count, $affiliate );

}

/**
 * Referral sale listener
 *
 * @since 1.0.0
 *
 * @param int       $sale_id        Sale ID.
 * @param int       $product_id     Product ID.
 * @param int       $user_id        User ID.
 * @param string    $integration    Integration prefix.
 */
function gamipress_referrals_product_sale_listener( $sale_id, $product_id, $user_id, $integration ) {

    $affiliate = gamipress_referrals_get_affiliate_from_cookie( $user_id );

    // Return if affiliate not found
    if( ! $affiliate ) return;

    // Setup vars
    $affiliate_id   = $affiliate->ID;
    $referral_id    = $user_id;
    $referral_ip    = gamipress_referrals_get_ip();

    $skip = false;

    /**
     * Filter to skip a referral sale
     *
     * @since 1.0.0
     *
     * @param bool      $skip           Whatever if sale has been skipped or not
     * @param int       $affiliate_id   Affiliate ID
     * @param int       $referral_id    Referral ID
     * @param string    $referral_ip    Referral IP address
     * @param int       $sale_id        Sale ID
     * @param int       $product_id     Product ID
     * @param string    $integration    Integration
     * @param WP_User   $affiliate      Affiliate object
     *
     * @return bool
     */
    if ( true === apply_filters( 'gamipress_referrals_skip_product_sale', $skip, $affiliate_id, $referral_id, $referral_ip, $sale_id, $product_id, $integration, $affiliate ) ) {
        // Return if sale skipped
        return;
    }

    // Update the user's affiliate id
    gamipress_referrals_set_user_affiliate( $referral_id, $affiliate_id );

    // Trigger referral product sale
    do_action( "gamipress_referrals_{$integration}_referral_product_sale", $affiliate_id, $referral_id, $referral_ip, $sale_id, $product_id, $affiliate );

    // Trigger purchase product through a referral
    do_action( "gamipress_referrals_{$integration}_product_sale", $affiliate_id, $referral_id, $referral_ip, $sale_id, $product_id, $affiliate );

}

/**
 * Referral sale refund listener
 *
 * @since 1.0.0
 *
 * @param int $user_id New registered user ID.
 */
function gamipress_referrals_sale_refund_listener( $sale_id, $user_id, $integration ) {

    $affiliate = gamipress_referrals_get_affiliate_from_cookie( $user_id );

    // Return if affiliate not found
    if( ! $affiliate ) return;

    // Setup vars
    $affiliate_id   = $affiliate->ID;
    $referral_id    = $user_id;
    $referral_ip    = gamipress_referrals_get_ip();

    $skip = false;

    $tracked = get_post_meta( $sale_id, '_gamipress_referrals_sale_refund_tracked', true );

    // Skip already tracked sales
    if( (bool) $tracked ) {
        $skip = true;
    }

    /**
     * Filter to skip a referral sale refund
     *
     * @since 1.0.0
     *
     * @param bool      $skip           Whatever if sale has been skipped or not
     * @param int       $affiliate_id   Affiliate ID
     * @param int       $referral_id    Referral ID
     * @param string    $referral_ip    Referral IP address
     * @param int       $sale_id        Sale ID
     * @param string    $integration    Integration
     * @param WP_User   $affiliate      Affiliate object
     *
     * @return bool
     */
    if ( true === apply_filters( 'gamipress_referrals_skip_sale_refund', $skip, $affiliate_id, $referral_id, $referral_ip, $sale_id, $integration, $affiliate ) ) {
        // Return if sale refund skipped
        return;
    }

    // Set a post meta to meet that sale refund has been tracked
    update_post_meta( $sale_id, '_gamipress_referrals_sale_refund_tracked', '1' );

    // Log the referral sale refund
    gamipress_referrals_log_sale_refund( $affiliate_id, $referral_id, $referral_ip, $sale_id, $integration );

    // Revoke the sale commission
    gamipress_referrals_revoke_sale_commission( $affiliate_id, $referral_id, $sale_id, $integration );

    // Trigger referral sale refund
    do_action( "gamipress_referrals_{$integration}_referral_sale_refund", $affiliate_id, $referral_id, $referral_ip, $sale_id, $affiliate );

    // Trigger complete a sale refund through a referral
    do_action( "gamipress_referrals_{$integration}_sale_refund", $affiliate_id, $referral_id, $referral_ip, $sale_id, $affiliate );

    // Get the referral count
    if( gamipress_referrals_is_migrated() ) {
        $count = gamipress_referrals_count_referrals( array( 'user_id' => $affiliate_id, 'type' => 'referral_sale_refund' ) );
    } else {
        $count = gamipress_get_user_log_count( $affiliate_id, array( 'type' => 'referral_sale_refund' ) );
    }
    $count = max( $count, 0 );

    // Store the count in a meta
    gamipress_update_user_meta( $affiliate_id, 'gamipress_referrals_refunds', $count );

    // Trigger referral sales refunds
    do_action( "gamipress_referrals_{$integration}_referral_sales_refunds", $affiliate_id, $count, $affiliate );

}