<?php
// Archivo: includes/scripts.php
if ( ! defined( 'ABSPATH' ) ) exit;

function gamipress_llms_pp_register_scripts() {
    // CORRECCIÓN 3: el archivo JS se ha renombrado para que coincida con LifterLMS (llms)
    // en lugar de WooCommerce (wc)
    wp_register_script(
        'gamipress-llms-pp-checkout',
        GAMIPRESS_LLMS_PP_URL . 'assets/js/gamipress-llms-partial-payments-checkout.js',
        array( 'jquery' ),
        GAMIPRESS_LLMS_PP_VER,
        true
    );

    wp_register_script(
        'gamipress-llms-pp-admin',
        GAMIPRESS_LLMS_PP_URL . 'assets/js/gamipress-llms-partial-payments-admin.js',
        array( 'jquery' ),
        GAMIPRESS_LLMS_PP_VER,
        true
    );
}
add_action( 'init', 'gamipress_llms_pp_register_scripts' );


function gamipress_llms_pp_enqueue_scripts() {

    if ( ! is_llms_checkout() ) return;

    $prefix       = '_gamipress_llms_pp_';
    $points_types = array();

    foreach ( gamipress_get_points_types() as $points_type => $data ) {
        if ( (bool) gamipress_get_post_meta( $data['ID'], $prefix . 'enable' ) ) {
            $data['conversion'] = gamipress_get_post_meta( $data['ID'], $prefix . 'conversion' );
            if ( ! empty( $data['conversion'] ) ) {
                $points_types[ $points_type ] = $data;
            }
        }
    }

    wp_localize_script( 'gamipress-llms-pp-checkout', 'gamipress_llms_pp', array(
        'ajaxurl'         => admin_url( 'admin-ajax.php' ),
        'nonce'           => wp_create_nonce( 'gamipress_llms_pp' ),
        'points_types'    => $points_types,
        'apply_action'    => 'gamipress_llms_pp_apply',
        'remove_action'   => 'gamipress_llms_pp_remove',
        'currency_symbol' => get_lifterlms_currency_symbol(),
    ) );

    wp_enqueue_script( 'gamipress-llms-pp-checkout' );
}
add_action( 'wp_enqueue_scripts', 'gamipress_llms_pp_enqueue_scripts', 100 );


function gamipress_llms_pp_admin_enqueue_scripts( $hook ) {

    if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'gamipress_page_gamipress_settings' ) ) ) return;

    wp_enqueue_script( 'gamipress-llms-pp-admin' );
}
add_action( 'admin_enqueue_scripts', 'gamipress_llms_pp_admin_enqueue_scripts' );
