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
 * Uninstallation
 *
 * @since 1.0.0
 */
function shortlinkspro_uninstall() {

    // Clear scheduled events
    shortlinkspro_clear_scheduled_events();

}
