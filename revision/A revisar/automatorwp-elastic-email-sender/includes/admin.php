<?php
/**
 * Admin Functions
 *
 * @package     AutomatorWP\Integrations\ElasticEmailSender
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


function automatorwp_elasticmailsender_admin_menu() {
}
add_action( 'admin_menu', 'automatorwp_elasticmailsender_admin_menu' );