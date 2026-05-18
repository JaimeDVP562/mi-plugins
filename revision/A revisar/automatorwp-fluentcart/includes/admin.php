<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\FluentCart\Admin
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_get_option( $option_name, $default = false ) {
    $prefix = 'automatorwp_fluentcart_';
    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register the FluentCart settings section.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_settings_sections( $sections ) {
    $sections['fluentcart'] = array(
        'title' => __( 'FluentCart', 'automatorwp-fluentcart' ),
        'icon'  => 'dashicons-cart',
    );
    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_fluentcart_settings_sections' );

/**
 * Register the FluentCart settings meta boxes.
 *
 * @since 1.0.0
 */
function automatorwp_fluentcart_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'automatorwp_fluentcart_';
    $meta_boxes['automatorwp-fluentcart-settings'] = array(
        'title'  => __( 'FluentCart', 'automatorwp-fluentcart' ),
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
 * Render callback for the connection status field.
 *
 * @since 1.0.0
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
            <?php if ( $fc_active ) : ?>
                <div class="automatorwp-notice-success">
                    <?php printf( esc_html__( 'FluentCart v%s detected and connected successfully.', 'automatorwp-fluentcart' ), esc_html( $fc_version ) ); ?>
                </div>
            <?php else : ?>
                <div class="automatorwp-notice-error">
                    <?php esc_html_e( 'FluentCart is not active. Please install and activate FluentCart to use this integration.', 'automatorwp-fluentcart' ); ?>
                </div>
            <?php endif; ?>
            <p class="cmb2-metabox-description">
                <?php esc_html_e( 'This integration adds FluentCart triggers and actions to AutomatorWP. No API key required.', 'automatorwp-fluentcart' ); ?>
            </p>
            <table class="widefat striped" style="max-width:600px;margin-top:12px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Type', 'automatorwp-fluentcart' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'automatorwp-fluentcart' ); ?></th>
                        <th><?php esc_html_e( 'Hook', 'automatorwp-fluentcart' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Trigger</td><td><?php esc_html_e( 'User completes a purchase', 'automatorwp-fluentcart' ); ?></td><td><code>fluent_cart/order_paid_done</code></td></tr>
                    <tr><td>Trigger</td><td><?php esc_html_e( 'User activates a subscription', 'automatorwp-fluentcart' ); ?></td><td><code>fluent_cart/payments/subscription_active</code></td></tr>
                    <tr><td>Trigger</td><td><?php esc_html_e( 'User cancels a subscription', 'automatorwp-fluentcart' ); ?></td><td><code>fluent_cart/payments/subscription_cancelled</code></td></tr>
                    <tr><td>Action</td><td><?php esc_html_e( 'Create a coupon', 'automatorwp-fluentcart' ); ?></td><td><code>Coupon::create()</code></td></tr>
                    <tr><td>Action</td><td><?php esc_html_e( 'Add a note to an order', 'automatorwp-fluentcart' ); ?></td><td><code>Order->addLog()</code></td></tr>
                    <tr><td>Action</td><td><?php esc_html_e( 'Cancel a subscription', 'automatorwp-fluentcart' ); ?></td><td><code>Subscription->update()</code></td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}