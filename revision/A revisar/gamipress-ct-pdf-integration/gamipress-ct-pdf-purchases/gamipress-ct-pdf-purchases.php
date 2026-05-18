<?php
/**
 * Plugin Name: GamiPress - CT PDF Purchases
 * Description: CT PDF integration for GamiPress Purchases.
 * Version: 1.0.0
 * Author: Bryant Giorgini
 * Text Domain: gamipress-ct-pdf-purchases
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GAMIPRESS_CT_PDF_PURCHASES_VERSION', '1.0.0' );
define( 'GAMIPRESS_CT_PDF_PURCHASES_FILE', __FILE__ );
define( 'GAMIPRESS_CT_PDF_PURCHASES_DIR', plugin_dir_path( __FILE__ ) );
define( 'GAMIPRESS_CT_PDF_PURCHASES_URL', plugin_dir_url( __FILE__ ) );

function gamipress_ct_pdf_purchases_init() {

    require_once GAMIPRESS_CT_PDF_PURCHASES_DIR . 'includes/admin/settings.php';
    require_once GAMIPRESS_CT_PDF_PURCHASES_DIR . 'includes/helpers.php';
    require_once GAMIPRESS_CT_PDF_PURCHASES_DIR . 'includes/pdf-functions.php';
    require_once GAMIPRESS_CT_PDF_PURCHASES_DIR . 'includes/payment-data.php';
    require_once GAMIPRESS_CT_PDF_PURCHASES_DIR . 'includes/tags.php';
    require_once GAMIPRESS_CT_PDF_PURCHASES_DIR . 'includes/hooks.php';
}

add_action( 'plugins_loaded', 'gamipress_ct_pdf_purchases_init', 20 );