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
 * Uninstallation
 *
 * @since 1.0.0
 */
function bbforms_uninstall() {

    // Clear scheduled events
    bbforms_clear_scheduled_events();

}
