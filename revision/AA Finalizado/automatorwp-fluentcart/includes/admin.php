<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\FluentCart\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function automatorwp_fluentcart_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_fluentcart_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_fluentcart_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['fluentcart'] = array(
            'title' => __( 'FluentCart', 'automatorwp-fluentcart' ),
            'icon'  => 'dashicons-cart',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_fluentcart_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_fluentcart_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_fluentcart_';

    $meta_boxes['automatorwp-fluentcart-settings'] = array(
            'title'  => automatorwp_dashicon( 'fluentcart' ) . __( 'FluentCart', 'automatorwp-fluentcart' ),
            'fields' => apply_filters( 'automatorwp_fluentcart_settings_fields', array(
                    $prefix . 'status' => array(
                            'type'          => 'text',
                            'render_row_cb' => 'automatorwp_fluentcart_status_display_cb',
                    ),
            ) ),
    );

    return $meta_boxes;

}
add_filter( 'automatorwp_settings_fluentcart_meta_boxes', 'automatorwp_fluentcart_settings_meta_boxes' );

/**
 * Display callback for the connection status field
 *
 * @since  1.0.0
 *
 * @param array      $field_args
 * @param CMB2_Field $field
 */
function automatorwp_fluentcart_status_display_cb( $field_args, $field ) {

    $fc_active  = defined( 'FLUENTCART_PLUGIN_PATH' );
    $fc_version = defined( 'FLUENTCART_VERSION' ) ? FLUENTCART_VERSION : __( 'Unknown', 'automatorwp-fluentcart' );

    ?>
    <div class="cmb-row cmb-type-custom table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php esc_html_e( 'FluentCart Status:', 'automatorwp-fluentcart' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="automatorwp_fluentcart_status" class="button button-primary" href="#"><?php esc_html_e( 'Check connection', 'automatorwp-fluentcart' ); ?></a>
            <p class="cmb2-metabox-description"><?php esc_html_e( 'This integration adds FluentCart triggers and actions to AutomatorWP. No API key required.', 'automatorwp-fluentcart' ); ?></p>
            <?php if( $fc_active ) : ?>
                <div class="automatorwp-notice-success">
                    <?php printf( esc_html__( 'FluentCart v%s detected and connected successfully.', 'automatorwp-fluentcart' ), esc_html( $fc_version ) ); ?>
                </div>
            <?php else : ?>
                <div class="automatorwp-notice-error">
                    <?php esc_html_e( 'FluentCart is not active. Please install and activate FluentCart to use this integration.', 'automatorwp-fluentcart' ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

}