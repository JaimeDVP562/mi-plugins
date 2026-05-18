<?php
/**
 * Admin
 *
 * @package     CMB2_Licenses_Page\Admin
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get an option value.
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed Option value or default parameter value if not exists.
 */
function cmb2_licenses_page_get_option( $page, $option_name, $default = false ) {

    global $cmb2_lp_settings;

    $page_args = cmb2_lp_get_page_args( $page );

    if( ! is_array( $cmb2_lp_settings ) ) {
        $cmb2_lp_settings = array();
    }

    if( ! isset( $cmb2_lp_settings[$page] ) ) {
        $cmb2_lp_settings[$page] = get_option( $page_args['options_key'] );
    }

    return isset( $cmb2_lp_settings[$page][ $option_name ] ) ? $cmb2_lp_settings[$page][ $option_name ] : $default;

}

/**
 * Add admin bar menu
 *
 * @since 1.0.0
 *
 * @param object $wp_admin_bar The WordPress toolbar object
 */
function cmb2_licenses_page_admin_bar_submenu( $wp_admin_bar ) {

    $pages = cmb2_lp_get_pages();

    foreach ( $pages as $page => $page_args ) {
        if( isset( $page_args['admin_bar_parent'] ) && ! empty( $page_args['admin_bar_parent'] ) ) {
            // Licenses
            $wp_admin_bar->add_node( array(
                'id'     => str_replace( '_', '-', $page ) . '-licenses',
                'title'  => __( 'Licenses', 'cmb2-licenses-page' ),
                'parent' => $page_args['admin_bar_parent'],
                'href'   => admin_url( 'admin.php?page=' . $page . '_licenses' )
            ) );
        }
    }

}
add_action( 'admin_bar_menu', 'cmb2_licenses_page_admin_bar_submenu', 999 );

/**
 * Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function cmb2_licenses_page_licenses_meta_boxes( $meta_boxes ) {

    global $cmb2_lp_licenses;

    if( ! isset( $_GET['page'] ) ) {
        return $meta_boxes;
    }

    if( ! is_array( $cmb2_lp_licenses ) ) {
        $cmb2_lp_licenses = array();
    }

    $page = sanitize_text_field( $_GET['page'] );

    if( ! isset( $cmb2_lp_licenses[$page] ) ) {
        return $meta_boxes;
    }

    foreach( $cmb2_lp_licenses[$page] as $page => $license ) {

        $meta_boxes[$license['key']] = array(
            'title' => $license['item_name'],
            'fields' => array(
                $license['key'] => array(
                    'name' => __( 'License', 'cmb2-licenses-page' ),
                    'type' => 'edd_license',
                    'file' => $license['file'],
                    'item_name' => $license['item_name'],
                ),
            )
        );

    }


    return $meta_boxes;
}
