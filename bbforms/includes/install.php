<?php
/**
 * Install
 *
 * @package     BBForms\Install
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Installation
 *
 * @since 1.0.0
 */
function bbforms_install() {

    // Setup default installation date
    $install_date = ( $exists = get_option( 'bbforms_install_date' ) ) ? $exists : '';

    if ( empty( $install_date ) ) {
        update_option( 'bbforms_install_date', gmdate( 'Y-m-d H:i:s' ) );
    }

    // Register custom DB tables
    bbforms_register_custom_tables();

    // Schedule events
    bbforms_schedule_events();

}
