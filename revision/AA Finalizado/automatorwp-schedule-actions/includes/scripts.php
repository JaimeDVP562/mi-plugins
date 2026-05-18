<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\Schedule_Actions\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since 1.0.0
 */
function automatorwp_schedule_actions_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style(
        'automatorwp-schedule-actions-admin-css',
        AUTOMATORWP_SCHEDULE_ACTIONS_URL . 'assets/css/automatorwp-schedule-actions-admin' . $suffix . '.css',
        array(),
        AUTOMATORWP_SCHEDULE_ACTIONS_VER,
        'all'
    );

    // Scripts
    wp_register_script(
        'automatorwp-schedule-actions-admin-js',
        AUTOMATORWP_SCHEDULE_ACTIONS_URL . 'assets/js/automatorwp-schedule-actions-admin' . $suffix . '.js',
        array( 'jquery' ),
        AUTOMATORWP_SCHEDULE_ACTIONS_VER,
        true
    );

    // Pasar datos al JS: cadenas traducibles y nonce global
    wp_localize_script(
        'automatorwp-schedule-actions-admin-js',
        'automatorwpSA',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'i18n'    => array(
                'modalTitle'  => __( 'Cambiar fecha programada', 'automatorwp-schedule-actions' ),
                'utcNotice'   => __( 'Introduce la nueva fecha en UTC.', 'automatorwp-schedule-actions' ),
                'save'        => __( 'Guardar nueva fecha', 'automatorwp-schedule-actions' ),
                'cancel'      => __( 'Cancelar', 'automatorwp-schedule-actions' ),
                'saving'      => __( 'Guardando…', 'automatorwp-schedule-actions' ),
                'emptyDate'   => __( 'Selecciona una fecha y hora.', 'automatorwp-schedule-actions' ),
                'pastDate'    => __( 'La fecha debe ser en el futuro.', 'automatorwp-schedule-actions' ),
                'ajaxError'   => __( 'Error de conexión. Inténtalo de nuevo.', 'automatorwp-schedule-actions' ),
            ),
        )
    );
}
add_action( 'admin_init', 'automatorwp_schedule_actions_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since 1.0.0
 *
 * @param string $hook
 */
function automatorwp_schedule_actions_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-schedule-actions-admin-css' );

    // Scripts
    wp_enqueue_script( 'automatorwp-schedule-actions-admin-js' );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_schedule_actions_admin_enqueue_scripts' );