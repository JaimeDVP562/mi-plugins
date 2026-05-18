<?php
/**
 * @package      RGC\CMB2\Licenses_Page
 * @copyright    Copyright (c) GamiPress
 *
 * Plugin Name: CMB2 Licenses Page
 * Plugin URI: https://github.com/rubengc/cmb2-licenses-page
 * GitHub Plugin URI: https://github.com/rubengc/cmb2-licenses-page
 * Description: CMB2 helper to register a licenses page
 * Version: 1.0.0
 * Author: GamiPress
 * Author URI: https://gamipress.com/
 * License: GPLv2+
 */

global $cmb2_licenses_page;

// Prevent CMB2 autoload adding "RGC_" at start
if( ! class_exists( 'RGC_CMB2_Licenses_Page' ) ) {

    /**
     * Class RGC_CMB2_Licenses_Page
     */
    class RGC_CMB2_Licenses_Page {

        /**
         * Current version number
         */
        const VERSION = '1.0.0';

        /**
         * Initialize the plugin by hooking into CMB2
         */
        public function __construct() {

            $this->includes();
            $this->hooks();

        }

        public function includes() {
            // EDD License Field
            if ( ! class_exists('RGC_CMB2_Field_EDD_License') ) {
                require_once __DIR__ . '/lib/cmb2-field-edd-license/cmb2-field-edd-license.php';
            }

            require_once __DIR__ . '/includes/admin.php';
            require_once __DIR__ . '/includes/licenses-page.php';
        }

        public function hooks() {
            add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ) );
        }

        /**
         * Enqueue scripts and styles
         */
        public function setup_admin_scripts() {

            // Styles
            wp_enqueue_style( 'cmb2-licenses-page-css', plugins_url( 'css/cmb2-licenses-page.css', __FILE__ ), array(), self::VERSION );

        }

    }

    // Hack function to register a page with only the prefix (eg: gamipress or automatorwp)
    function cmb2_lp_reg( $prefix ) {

        cmb2_lp_register( $prefix . '_licenses', array(
            'admin_parent'              => $prefix,
            'admin_bar_parent'          => $prefix,
            'option_key'                => $prefix . '_settings',
            'view_capability_cb'        => $prefix . '_get_manager_capability',
            'installation_instructions' => sprintf(
                __( 'Looking to install a pro add-on? Check the <a href="%s" target="_blank">installation instructions</a>.', 'cmb2-licenses-page' ),
                'https://' . $prefix . '.com/docs/getting-started/installing-pro-add-ons/'
            ),
            'plugins_api_cb'            => $prefix . '_plugins_api',
            'server'                    => 'https://' . $prefix . '.com/edd-sl-api',
            'renew_license_link'        => 'https://' . $prefix . '.com/renew-a-license',
            'license_management_link'   => 'https://' . $prefix . '.com/account',
            'contact_link'              => 'https://' . $prefix . '.com/contact',
        ) );

    }

    function cmb2_lp_reg_license( $page, $key, $item_name, $file ) {

        global $cmb2_lp_licenses;

        if( ! is_array( $cmb2_lp_licenses ) ) {
            $cmb2_lp_licenses = array();
        }

        $page = $page . '_licenses';

        if( ! isset( $cmb2_lp_licenses[$page] ) ) {
            $cmb2_lp_licenses[$page] = array();
            // Register hook
            add_filter( $page . '_meta_boxes', 'cmb2_licenses_page_licenses_meta_boxes' );
        }

        $cmb2_lp_licenses[$page][] = array(
            'key' => $key,
            'item_name' => $item_name,
            'file' => $file,
        );
    }

    /**
     * Function to register a license page
     *
     * @param string $page  Page key
     * @param array $args   Args
     */
    function cmb2_lp_register( $page, $args ) {

        global $cmb2_lp_pages;

        if( ! is_array( $cmb2_lp_pages ) ) {
            $cmb2_lp_pages = array();
        }

        // Bail if page already registered
        if( isset( $cmb2_lp_pages[$page] ) ) {
            return;
        }

        $args = wp_parse_args( $args, array(
//            'admin_parent'              => 'gamipress',
//            'admin_bar_parent'          => 'gamipress',
//            'option_key'                => 'gamipress_settings',
//            'view_capability'           => '', // Use view_capability_cb instead
//            'view_capability_cb'        => 'gamipress_get_manager_capability',
//            'installation_instructions' => sprintf(
//                __( 'Looking to install a pro add-on? Check the <a href="%s" target="_blank">installation instructions</a>.', 'gamipress' ),
//                'https://gamipress.com/docs/getting-started/installing-pro-add-ons/'
//            ),
//            'plugins_api_cb'            => 'gamipress_plugins_api',
//            'server'                    => 'https://gamipress.com/edd-sl-api',
//            'renew_license_link'        => 'https://gamipress.com/renew-a-license',
//            'license_management_link'   => 'https://gamipress.com/account',
//            'contact_link'              => 'https://gamipress.com/contact',

            'admin_parent'              => '',
            'admin_bar_parent'          => '',
            'option_key'                => '',
            'view_capability'           => '', // Use view_capability_cb instead
            'view_capability_cb'        => '',
            'installation_instructions' => '',
            'plugins_api_cb'            => '',
            'server'                    => '',
            'renew_license_link'        => '',
            'license_management_link'   => '',
            'contact_link'              => '',
        ) );

        // Add page to array
        $cmb2_lp_pages[$page] = $args;
    }

    function cmb2_lp_get_pages() {

        global $cmb2_lp_pages;

        if( ! is_array( $cmb2_lp_pages ) ) {
            $cmb2_lp_pages = array();
        }

        return $cmb2_lp_pages;

    }

    function cmb2_lp_get_page_args( $page ) {

        global $cmb2_lp_pages;

        if( ! is_array( $cmb2_lp_pages ) ) {
            $cmb2_lp_pages = array();
        }

        return ( isset( $cmb2_lp_pages[$page] ) ? $cmb2_lp_pages[$page] : array() );

    }

    function cmb2_lp_is_licenses_page() {

        // Check if we are on the licenses page to prevent API calls outside this page
        if( ! isset( $_GET['page'] ) ) {
            return false;
        }

        $pages = cmb2_lp_get_pages();

        if( count( $pages ) === 0 ) {
            return false;
        }

        $pages = array_keys( $pages );

        if( ! in_array( $_GET['page'], $pages ) ) {
            return false;
        }

        return true;

    }

    function cmb2_lp_detect_page_by_prefix( $prefix ) {

        $pages = cmb2_lp_get_pages();
        $prefix = explode( '_', $prefix )[0]; // expects "gamipress" from "gamipress_pro_addon"

        foreach( $pages as $page => $page_args ) {
            $page_prefix = explode( '_', $page )[0]; // expects "gamipress" from "gamipress_licenses"

            if(  $prefix === $page_prefix ) {
                return $page;
            }

        }

        return false;

    }

    $cmb2_licenses_page = new RGC_CMB2_Licenses_Page();

}