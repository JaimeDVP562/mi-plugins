<?php
/**
 * Scripts
 * 
 * @author AutomatorWP
 * @since  1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 * 
 * @since   1.0.0
 * @return  void
 */
function github_social_proof_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Register Stylesheets
    wp_register_style(
    'gamipress-social-proof-css',
    GAMIPRESS_SOCIAL_PROOF_URL . 'assets/css/gamipress-social-proof' . $suffix .'.css',
    array(),
    GAMIPRESS_SOCIAL_PROOF_VER,
    'all');

    // Register Script
    wp_register_script( 
    'gamipress-social-proof-js',
     GAMIPRESS_SOCIAL_PROOF_URL . 'assets/js/gamipress-social-proof' . $suffix . '.js',
     array( 'jquery' ),
     GAMIPRESS_SOCIAL_PROOF_VER,
     true);
}
add_action('init','github_social_proof_register_scripts');

/**
 * 
 */
function github_social_proof_enqueue_scripts(){

    // Enqueue Stylesheets
    wp_enqueue_style( 'gamipress-social-proof-css' );

    // Enqueue Scripts
        wp_localize_script(
        'gamipress-social-proof-js',
        'fomo_ajax',
        array(
            'url' => admin_url('admin-ajax.php')
        )
    );
  
    wp_enqueue_script( 'gamipress-social-proof-js' );
}
add_action( 'wp_enqueue_scripts', 'github_social_proof_enqueue_scripts' );