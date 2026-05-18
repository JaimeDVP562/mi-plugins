<?php
/**
 * Admin settings for 7todos
 *
 * @package     AutomatorWP\7todos
 * @since       1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Añadir pestaña en AutomatorWP → Ajustes
 */
function automatorwp_7todos_settings_sections( $sections ) {

    $sections['7todos'] = array(
        'title' => __( '7todos', 'automatorwp-7todos' ),
        'icon'  => 'dashicons-list-view',
    );

    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_7todos_settings_sections' );

/**
 * Añadir meta box en la pestaña 7todos
 */
function automatorwp_7todos_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_7todos_';

    $meta_boxes['automatorwp-7todos-settings'] = array(
        'title'  => automatorwp_dashicon( 'list-view' ) . __( '7todos', 'automatorwp-7todos' ),
        'fields' => apply_filters( 'automatorwp_7todos_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-7todos' ),
                'desc' => __( 'Pega aquí tu clave API de 7todos.', 'automatorwp-7todos' ),
                'type' => 'text',
                'attributes' => array(
                    'style' => 'width: 100%; max-width: 500px; font-family: monospace;',
                ),
            ),
        ) ),
    );

    return $meta_boxes;
}
add_filter( 'automatorwp_settings_7todos_meta_boxes', 'automatorwp_7todos_settings_meta_boxes' );