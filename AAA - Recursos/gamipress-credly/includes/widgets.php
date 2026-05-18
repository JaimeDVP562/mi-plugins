<?php
/**
 * Widgets
 *
 * @package GamiPress\Credly\Widgets
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Widgets
require_once GAMIPRESS_CREDLY_DIR . 'includes/widgets/credly-login-widget.php';

// Register plugin widgets
function gamipress_credly_register_widgets() {

    register_widget('gamipress_credly_login_widget');

}
add_action('widgets_init', 'gamipress_credly_register_widgets');