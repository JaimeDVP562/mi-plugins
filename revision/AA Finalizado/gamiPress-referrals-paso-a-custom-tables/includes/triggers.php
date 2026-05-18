<?php
/**
 * Triggers
 *
 * @package GamiPress\Referrals\Triggers
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register activity triggers
 *
 * @since  1.0.0
 *
 * @param array $triggers
 * @return mixed
 */
function gamipress_referrals_activity_triggers( $triggers ) {

    $triggers[__( 'Referrals', 'gamipress-referrals' )] = array(
        'gamipress_referrals_referral_visit'            => __( 'Get a referral visit', 'gamipress-referrals' ),
        'gamipress_referrals_specific_referral_visit'   => __( 'Get a specific referral visit', 'gamipress-referrals' ),
        'gamipress_referrals_referral_signup'           => __( 'Get a referral sign up', 'gamipress-referrals' ),
        'gamipress_referrals_register'                  => __( 'Register to website through a referral', 'gamipress-referrals' ),
        'gamipress_referrals_referral_visits'           => __( 'Reach a referral visits amount', 'gamipress-referrals' ),
        'gamipress_referrals_referral_signups'          => __( 'Reach a referral sign ups amount', 'gamipress-referrals' ),
    );

    if ( class_exists( 'WooCommerce' ) ) {
        $triggers[__( 'Referrals - WooCommerce integration', 'gamipress-referrals' )] = array(
            'gamipress_referrals_woocommerce_referral_sale'             => __( 'Get a referral sale', 'gamipress-referrals' ),
            'gamipress_referrals_woocommerce_sale'                      => __( 'Complete a purchase through a referral', 'gamipress-referrals' ),
            'gamipress_referrals_woocommerce_referral_product_sale'             => __( 'Get a referral sale of a product', 'gamipress-referrals' ),
            'gamipress_referrals_woocommerce_product_sale'                      => __( 'Purchase a product through a referral', 'gamipress-referrals' ),
            'gamipress_referrals_woocommerce_referral_sale_refund'      => __( 'Get a referral sale refunded', 'gamipress-referrals' ),
            'gamipress_referrals_woocommerce_sale_refund'               => __( 'Refund product purchased through a referral', 'gamipress-referrals' ),
            'gamipress_referrals_woocommerce_referral_sales'            => __( 'Reach a referral sales amount', 'gamipress-referrals' ),
            'gamipress_referrals_woocommerce_referral_sales_refunds'    => __( 'Reach a referral sales refunded amount', 'gamipress-referrals' ),
        );
    }

    if ( class_exists( 'Easy_Digital_Downloads' ) ) {
        $triggers[__( 'Referrals - Easy Digital Downloads integration', 'gamipress-referrals' )] = array(
            'gamipress_referrals_easy_digital_downloads_referral_sale'          => __( 'Get a referral sale', 'gamipress-referrals' ),
            'gamipress_referrals_easy_digital_downloads_sale'                   => __( 'Complete a purchase through a referral', 'gamipress-referrals' ),
            'gamipress_referrals_easy_digital_downloads_referral_product_sale'          => __( 'Get a referral sale of a download', 'gamipress-referrals' ),
            'gamipress_referrals_easy_digital_downloads_product_sale'                   => __( 'Purchase a download through a referral', 'gamipress-referrals' ),
            'gamipress_referrals_easy_digital_downloads_referral_sale_refund'   => __( 'Get a referral sale refunded', 'gamipress-referrals' ),
            'gamipress_referrals_easy_digital_downloads_sale_refund'            => __( 'Refund product purchased through a referral', 'gamipress-referrals' ),
            'gamipress_referrals_easy_digital_downloads_referral_sales'         => __( 'Reach a referral sales amount', 'gamipress-referrals' ),
            'gamipress_referrals_easy_digital_downloads_referral_sales_refunds' => __( 'Reach a referral sales refunded amount', 'gamipress-referrals' ),
        );
    }

    return $triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_referrals_activity_triggers' );

/**
 * Register specific activity triggers
 *
 * @since  1.0.2
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_referrals_specific_activity_triggers( $specific_activity_triggers ) {

    // Get all public post types which means they are visitable
    $public_post_types = get_post_types( array( 'public' => true ) );

    // Remove attachment from public post types
    if( isset( $public_post_types['attachment'] ) ) {
        unset( $public_post_types['attachment'] );
    }

    /**
     * Filter specific referral visit post types
     *
     * @since 1.0.2
     *
     * @param array $public_post_types
     * @return array
     */
    $public_post_types = apply_filters( 'gamipress_referrals_specific_referral_visit_post_types', $public_post_types );

    // Remove keys
    $public_post_types = array_values( $public_post_types );

    $specific_activity_triggers['gamipress_referrals_specific_referral_visit'] = $public_post_types;

    // WooCommerce
    $specific_activity_triggers['gamipress_referrals_woocommerce_referral_product_sale'] = array( 'product' );
    $specific_activity_triggers['gamipress_referrals_woocommerce_product_sale'] = array( 'product' );

    // EDD
    $specific_activity_triggers['gamipress_referrals_easy_digital_downloads_referral_product_sale'] = array( 'download' );
    $specific_activity_triggers['gamipress_referrals_easy_digital_downloads_product_sale'] = array( 'download' );

    return $specific_activity_triggers;
}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_referrals_specific_activity_triggers' );

/**
 * Build custom activity trigger label
 *
 * @param string    $title
 * @param integer   $requirement_id
 * @param array     $requirement
 *
 * @return string
 */
function gamipress_referrals_activity_trigger_label( $title, $requirement_id, $requirement ) {

    $count = ( isset( $requirement['referrals_count'] ) ) ? absint( $requirement['referrals_count'] ) : 0;

    switch( $requirement['trigger_type'] ) {

        // Count events
        case 'gamipress_referrals_referral_visits':
            return sprintf( __( 'Reach %d referral visits', 'gamipress-referrals' ), $count );
            break;
        case 'gamipress_referrals_referral_signups':
            return sprintf( __( 'Reach %d referral sign ups', 'gamipress-referrals' ), $count );
            break;
        case 'gamipress_referrals_woocommerce_referral_sales':
        case 'gamipress_referrals_easy_digital_downloads_referral_sales':
            return sprintf( __( 'Reach %d referral sales', 'gamipress-referrals' ), $count );
            break;
        case 'gamipress_referrals_woocommerce_referral_sales_refunded':
        case 'gamipress_referrals_easy_digital_downloads_referral_sales_refunded':
            return sprintf( __( 'Reach %d referral sales refunded', 'gamipress-referrals' ), $count );
            break;

    }

    return $title;
}
add_filter( 'gamipress_activity_trigger_label', 'gamipress_referrals_activity_trigger_label', 10, 3 );

/**
 * Register specific activity triggers labels
 *
 * @since  1.0.2
 *
 * @param  array $specific_activity_trigger_labels
 * @return array
 */
function gamipress_referrals_specific_activity_trigger_label( $specific_activity_trigger_labels ) {

    $specific_activity_trigger_labels['gamipress_referrals_specific_referral_visit'] = __( 'Get a referral visit on %s', 'gamipress-referrals' );

    // WooCommerce
    $specific_activity_trigger_labels['gamipress_referrals_woocommerce_referral_product_sale'] = __( 'Get a referral sale of %s', 'gamipress-referrals' );
    $specific_activity_trigger_labels['gamipress_referrals_woocommerce_product_sale'] = __( 'Purchase %s through a referral', 'gamipress-referrals' );

    // EDD
    $specific_activity_trigger_labels['gamipress_referrals_easy_digital_downloads_referral_product_sale'] = __( 'Get a referral sale of %s', 'gamipress-referrals' );
    $specific_activity_trigger_labels['gamipress_referrals_easy_digital_downloads_product_sale'] = __( 'Purchase %s through a referral', 'gamipress-referrals' );

    return $specific_activity_trigger_labels;
}
add_filter( 'gamipress_specific_activity_trigger_label', 'gamipress_referrals_specific_activity_trigger_label' );

/**
 * Get activity triggers excluded from activity time limits
 *
 * @since 1.0.0
 *
 * @param array $triggers_excluded
 *
 * @return array
 */
function gamipress_referrals_activity_triggers_excluded_from_activity_limit( $triggers_excluded ) {

    $triggers_excluded[] = 'gamipress_referrals_referral_visits';
    $triggers_excluded[] = 'gamipress_referrals_referral_signups';
    $triggers_excluded[] = 'gamipress_referrals_woocommerce_referral_sales';
    $triggers_excluded[] = 'gamipress_referrals_woocommerce_referral_sales_refunds';
    $triggers_excluded[] = 'gamipress_referrals_easy_digital_downloads_referral_sales';
    $triggers_excluded[] = 'gamipress_referrals_easy_digital_downloads_referral_sales_refunds';

    return $triggers_excluded;

}
add_filter( 'gamipress_activity_triggers_excluded_from_activity_limit', 'gamipress_referrals_activity_triggers_excluded_from_activity_limit' );

/**
 * Get user for a given trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id user ID to override.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 * @return integer          User ID.
 */
function gamipress_referrals_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        case 'gamipress_referrals_referral_visit':
        case 'gamipress_referrals_referral_visits':
        case 'gamipress_referrals_specific_referral_visit':
        case 'gamipress_referrals_referral_signup':
        case 'gamipress_referrals_referral_signups':
        // WooCommerce
        case 'gamipress_referrals_woocommerce_referral_sale':
        case 'gamipress_referrals_woocommerce_referral_product_sale':
        case 'gamipress_referrals_woocommerce_referral_sale_refund':
        case 'gamipress_referrals_woocommerce_referral_sales':
        case 'gamipress_referrals_woocommerce_referral_sales_refunds':
        // Easy Digital Downloads
        case 'gamipress_referrals_easy_digital_downloads_referral_sale':
        case 'gamipress_referrals_easy_digital_downloads_referral_product_sale':
        case 'gamipress_referrals_easy_digital_downloads_referral_sale_refund':
        case 'gamipress_referrals_easy_digital_downloads_referral_sales':
        case 'gamipress_referrals_easy_digital_downloads_referral_sales_refunds':
        $user_id = $args[0];
            break;
        case 'gamipress_referrals_register':
        // WooCommerce
        case 'gamipress_referrals_woocommerce_sale':
        case 'gamipress_referrals_woocommerce_product_sale':
        case 'gamipress_referrals_woocommerce_sale_refund':
        // Easy Digital Downloads
        case 'gamipress_referrals_easy_digital_downloads_sale':
        case 'gamipress_referrals_easy_digital_downloads_product_sale':
        case 'gamipress_referrals_easy_digital_downloads_sale_refund':
        $user_id = $args[1];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_referrals_trigger_get_user_id', 10, 3 );

/**
 * Get the id for a given specific trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $specific_id     Specific ID.
 * @param  string  $trigger         Trigger name.
 * @param  array   $args            Passed trigger args.
 *
 * @return integer                  Specific ID.
 */
function gamipress_referrals_specific_trigger_get_id( $specific_id, $trigger = '', $args = array() ) {

    switch ( $trigger ) {
        case 'gamipress_referrals_specific_referral_visit':
            $specific_id = $args[3];
            break;
        case 'gamipress_referrals_woocommerce_referral_product_sale':
        case 'gamipress_referrals_woocommerce_product_sale':
        case 'gamipress_referrals_easy_digital_downloads_referral_product_sale':
        case 'gamipress_referrals_easy_digital_downloads_product_sale':
            $specific_id = $args[4];
            break;
    }

    return $specific_id;
}
add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_referrals_specific_trigger_get_id', 10, 3 );

/**
 * Extended meta data for event trigger logging
 *
 * @since 1.0.0
 *
 * @param array 	$log_meta
 * @param integer 	$user_id
 * @param string 	$trigger
 * @param integer 	$site_id
 * @param array 	$args
 *
 * @return array
 */
function gamipress_referrals_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {
        case 'gamipress_referrals_referral_visit':
        case 'gamipress_referrals_specific_referral_visit':
            // Add the affiliate ID, referral ID and IP
            $log_meta['affiliate_id'] = $args[0];
            $log_meta['referral_id'] = $args[1];
            $log_meta['referral_ip'] = $args[2];
            $log_meta['post_id'] = $args[3];
            $log_meta['post_url'] = $args[4];
            $log_meta['referrer'] = $args[5];
            break;
        case 'gamipress_referrals_referral_signup':
        case 'gamipress_referrals_register':
            // Add the affiliate ID, referral ID and IP
            $log_meta['affiliate_id'] = $args[0];
            $log_meta['referral_id'] = $args[1];
            $log_meta['referral_ip'] = $args[2];
            break;
        // WooCommerce
        case 'gamipress_referrals_woocommerce_referral_sale':
        case 'gamipress_referrals_woocommerce_sale':
        case 'gamipress_referrals_woocommerce_referral_sale_refund':
        case 'gamipress_referrals_woocommerce_sale_refund':
        // Easy Digital Downloads
        case 'gamipress_referrals_easy_digital_downloads_referral_sale':
        case 'gamipress_referrals_easy_digital_downloads_sale':
        case 'gamipress_referrals_easy_digital_downloads_referral_sale_refund':
        case 'gamipress_referrals_easy_digital_downloads_sale_refund':
            // Add the affiliate ID, referral ID and IP
            $log_meta['affiliate_id'] = $args[0];
            $log_meta['referral_id'] = $args[1];
            $log_meta['referral_ip'] = $args[2];
            $log_meta['post_id'] = $args[3]; // Order ID
            break;
        // WooCommerce
        case 'gamipress_referrals_woocommerce_referral_product_sale':
        case 'gamipress_referrals_woocommerce_product_sale':
        // Easy Digital Downloads
        case 'gamipress_referrals_easy_digital_downloads_referral_product_sale':
        case 'gamipress_referrals_easy_digital_downloads_product_sale':
            // Add the affiliate ID, referral ID and IP
            $log_meta['affiliate_id'] = $args[0];
            $log_meta['referral_id'] = $args[1];
            $log_meta['referral_ip'] = $args[2];
            $log_meta['order_id'] = $args[3]; // Order ID
            $log_meta['post_id'] = $args[4]; // Product ID
            break;
        case'gamipress_referrals_referral_visits':
        case'gamipress_referrals_referral_signups':
        case'gamipress_referrals_woocommerce_referral_sales':
        case'gamipress_referrals_woocommerce_referral_sales_refunds':
        case'gamipress_referrals_easy_digital_downloads_referral_sales':
        case'gamipress_referrals_easy_digital_downloads_referral_sales_refunds':
            // Add the affiliate ID
            $log_meta['affiliate_id'] = $args[0];
            break;

    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_referrals_log_event_trigger_meta_data', 10, 5 );