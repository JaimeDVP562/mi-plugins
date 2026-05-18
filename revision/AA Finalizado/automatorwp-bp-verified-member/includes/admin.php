<?php
/**
 * Admin Functions
 * @package AutomatorWP\Integrations\BP_Verified_Member
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function automatorwp_bp_verified_member_admin_menu() {
    // Espacio para futuros menús de configuración
}
add_action( 'admin_menu', 'automatorwp_bp_verified_member_admin_menu' );