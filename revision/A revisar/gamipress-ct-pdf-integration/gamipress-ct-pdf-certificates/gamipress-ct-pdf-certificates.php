<?php
/**
 * Plugin Name: GamiPress - CT PDF Certificates
 * Description: CT PDF integration for GamiPress Certificates.
 * Version: 1.0.0
 * Author: Bryant Giorgini
 * Text Domain: gamipress-ct-pdf-certificates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GAMIPRESS_CT_PDF_CERTIFICATES_VERSION', '1.0.0' );
define( 'GAMIPRESS_CT_PDF_CERTIFICATES_FILE', __FILE__ );
define( 'GAMIPRESS_CT_PDF_CERTIFICATES_DIR', plugin_dir_path( __FILE__ ) );
define( 'GAMIPRESS_CT_PDF_CERTIFICATES_URL', plugin_dir_url( __FILE__ ) );

function gamipress_ct_pdf_certificates_init() {
    require_once GAMIPRESS_CT_PDF_CERTIFICATES_DIR . 'includes/admin/metabox.php';
    require_once GAMIPRESS_CT_PDF_CERTIFICATES_DIR . 'includes/helpers.php';
    require_once GAMIPRESS_CT_PDF_CERTIFICATES_DIR . 'includes/pdf-functions.php';
    require_once GAMIPRESS_CT_PDF_CERTIFICATES_DIR . 'includes/hooks.php';
}

add_action( 'plugins_loaded', 'gamipress_ct_pdf_certificates_init', 20 );

add_action( 'wp_enqueue_scripts', 'ctpdf_certificates_enqueue_assets' );

function ctpdf_certificates_enqueue_assets() {
    wp_enqueue_style(
        'ctpdf-certificates-style',
        GAMIPRESS_CT_PDF_CERTIFICATES_URL . 'assets/css/certificates.css',
        array(),
        GAMIPRESS_CT_PDF_CERTIFICATES_VERSION
    );
}