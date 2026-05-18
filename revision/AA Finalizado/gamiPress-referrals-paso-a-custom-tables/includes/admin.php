<?php
/**
 * Admin
 *
 * @package GamiPress\Referrals\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function gamipress_referrals_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_referrals_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * GamiPress Referrals Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_referrals_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_referrals_';

    $user = get_userdata( get_current_user_id() );

    $meta_boxes['gamipress-referrals-settings'] = array(
        'title' => gamipress_dashicon( 'megaphone' ) . __( 'Referrals', 'gamipress-referrals' ),
        'fields' => apply_filters( 'gamipress_referrals_settings_fields', array(

            $prefix . 'url_parameter' => array(
                'name' => __( 'URL Parameter', 'gamipress-referrals' ),
                'desc' => __( 'Set the URL parameter to be used on affiliate links. By default, ref.', 'gamipress-referrals' )
                    . '<br>'
                    . sprintf( __( '<strong>Important:</strong> This parameter shouldn\'t match any of the <a href="%s" target="_blank">WordPress URL parameters</a>.', 'gamipress-referrals' ), 'https://codex.wordpress.org/WordPress_Query_Vars' ),
                'type' => 'text',
                'default' => 'ref',
            ),
            $prefix . 'affiliate_links' => array(
                'name' => __( 'Affiliate Links', 'gamipress-referrals' ),
                'type' => 'radio',
                'options' => apply_filters( 'gamipress_referrals_affiliate_links_settings_options', array(
                    'user_id' => __( 'Assign the user ID.', 'gamipress-referrals' )
                        . '<br>'
                        . '<small>' . __( 'Example:', 'gamipress-referrals' ) . ' <span class="gamipress-referrals-sample-url">' . esc_url( add_query_arg( array( 'ref' => $user->ID ), home_url( '/' ) ) ) . '</span>' . '</small>',
                    'user_login' => __( 'Assign the username.', 'gamipress-referrals' )
                        . '<br>'
                        . '<small>' . __( 'Example:', 'gamipress-referrals' ) . ' <span class="gamipress-referrals-sample-url">' . esc_url( add_query_arg( array( 'ref' => urlencode( $user->user_login ) ), home_url( '/' ) ) ) . '</span>' . '</small>',
                ) ),
                'default' => 'user_id',
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_referrals_settings_meta_boxes' );

/**
 * Referrals Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_referrals_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-referrals-license'] = array(
        'title' => __( 'Referrals', 'gamipress-referrals' ),
        'fields' => array(
            'gamipress_referrals_license' => array(
                'name' => __( 'License', 'gamipress-referrals' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_REFERRALS_FILE,
                'item_name' => 'Referrals',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_referrals_licenses_meta_boxes' );

/**
 * Referrals automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_referrals_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-referrals'] = __( 'Referrals', 'gamipress-referrals' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_referrals_automatic_updates' );