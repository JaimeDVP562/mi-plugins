<?php

/**
 * Scripts side
 * 
 * @author AutomatorWP
 * @since 1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;


function automatorwp_github_admin_register_scripts(){

    // Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Register Stylesheets
    wp_register_style(
        'automatorwp-github-css',
        AUTOMATORWP_GITHUB_URL . 'assets/css/automatorwp-github' . $suffix . '.css',
        array(),
        AUTOMATORWP_GITHUB_VER,
        'all'
    );
    
    // Register Script
    wp_register_script( 'automatorwp-github-js',
    AUTOMATORWP_GITHUB_URL . 'assets/js/automatorwp-github' . $suffix . '.js', array( 'jquery' ),
    AUTOMATORWP_GITHUB_VER,
    true );

    

}
add_action('admin_init','automatorwp_github_admin_register_scripts');

function automatorwp_github_admin_enqueue_scripts(){

    //Enqueue Stylesheets
    wp_enqueue_style('automatorwp-github-css');

    //Enqueue Scripts
    wp_localize_script( 
        'automatorwp-github-js', 
        'automatorwp_github', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-github-js' );

}
add_action('admin_enqueue_scripts' , 'automatorwp_github_admin_enqueue_scripts');