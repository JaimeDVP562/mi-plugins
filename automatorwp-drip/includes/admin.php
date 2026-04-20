<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Drip\Admin
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since 1.0.0
 *
 * @param string $option_name
 * @param bool   $default
 *
 * @return mixed
 */
function automatorwp_drip_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_drip_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Show an admin notice when AutomatorWP is not active.
 *
 * @since 1.0.0
 */
function automatorwp_drip_admin_notices() {

    if ( ! class_exists( 'AutomatorWP' ) ) : ?>
        <div id="message" class="notice notice-error is-dismissible">
            <p>
                <?php echo sprintf(
                    __( '<strong>AutomatorWP - Drip</strong> requires <a href="%s" target="_blank">AutomatorWP</a> to be installed and active.', 'automatorwp-drip' ),
                    'https://wordpress.org/plugins/automatorwp/'
                ); ?>
            </p>
        </div>
    <?php endif;

}
add_action( 'admin_notices', 'automatorwp_drip_admin_notices' );

/**
 * Register plugin settings sections
 *
 * @since 1.0.0
 *
 * @param array $automatorwp_settings_sections
 *
 * @return array
 */
function automatorwp_drip_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['drip'] = array(
        'title' => __( 'Drip', 'automatorwp-drip' ),
        'icon'  => '',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_drip_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since 1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function automatorwp_drip_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_drip_';

    $meta_boxes['automatorwp-drip-settings'] = array(
        'title'  => automatorwp_dashicon( 'groups' ) . __( 'Drip', 'automatorwp-drip' ),
        'fields' => apply_filters( 'automatorwp_drip_settings_fields', array(
            $prefix . 'key' => array(
                'name' => __( 'Account ID:', 'automatorwp-drip' ),
                'desc' => __( 'Your Drip app account ID.', 'automatorwp-drip' ),
                'type' => 'text',
            ),
            $prefix . 'secret' => array(
                'name' => __( 'API Key:', 'automatorwp-drip' ),
                'desc' => __( 'Your Drip app API key.', 'automatorwp-drip' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_drip_authorize_display_cb',
            ),
            $prefix . 'webhook_info' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_drip_webhook_info_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( 'automatorwp_settings_drip_meta_boxes', 'automatorwp_drip_settings_meta_boxes' );

/**
 * Display callback showing the webhook URL to register in Drip.
 *
 * @since 1.0.0
 *
 * @param array      $field_args
 * @param CMB2_Field $field
 */
function automatorwp_drip_webhook_info_display_cb( $field_args, $field ) {

    $webhook_url = rest_url( 'automatorwp-drip/v1/webhook' );

    ?>
    <div class="cmb-row cmb-type-custom table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php esc_html_e( 'Webhook URL:', 'automatorwp-drip' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="text" readonly value="<?php echo esc_attr( $webhook_url ); ?>" style="width: 100%;" onclick="this.select();" />
            <p class="cmb2-metabox-description">
                <?php esc_html_e( 'Register this URL in your Drip account (Settings → Webhooks) to receive events and fire AutomatorWP triggers.', 'automatorwp-drip' ); ?>
            </p>
        </div>
    </div>
    <?php

}

/**
 * Display callback for the authorize setting.
 *
 * @since 1.0.0
 *
 * @param array      $field_args
 * @param CMB2_Field $field
 */
function automatorwp_drip_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-drip-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php esc_html_e( 'Connect with Drip:', 'automatorwp-drip' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo esc_attr( $field_id ); ?>" class="button button-primary" href="#"><?php esc_html_e( 'Try credentials', 'automatorwp-drip' ); ?></a>
            <p class="cmb2-metabox-description"><?php esc_html_e( 'Enter your Account ID and API Key above, then click "Try credentials" to verify the connection.', 'automatorwp-drip' ); ?></p>
        </div>
    </div>
    <?php

}
