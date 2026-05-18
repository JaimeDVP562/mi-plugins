<?php
/**
 * Install
 *
 * @package     ShortLinksPro\Install
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Installation
 *
 * @since 1.0.0
 */
function shortlinkspro_install() {

    // Setup default installation date
    $shortlinkspro_install_date = ( $exists = get_option( 'shortlinkspro_install_date' ) ) ? $exists : '';

    if ( empty( $shortlinkspro_install_date ) ) {
        update_option( 'shortlinkspro_install_date', gmdate( 'Y-m-d H:i:s' ) );
    }

    // Register custom DB tables
    shortlinkspro_register_custom_tables();

    // Schedule events
    shortlinkspro_schedule_events();

}
