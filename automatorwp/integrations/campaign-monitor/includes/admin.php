<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Campaign_Monitor\Admin
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
function automatorwp_campaign_monitor_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_campaign_monitor_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_campaign_monitor_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['campaign_monitor'] = array(
        'title' => __( 'Campaign Monitor', 'automatorwp' ),
        'icon' => 'dashicons-email',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_campaign_monitor_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_campaign_monitor_settings_meta_boxes( $meta_boxes )  {
    $prefix = 'automatorwp_campaign_monitor_';

    $meta_boxes['automatorwp-campaign-monitor-settings'] = array(
        'title' => automatorwp_dashicon( 'email' ) . __( 'Campaign Monitor', 'automatorwp' ),
        'fields' => apply_filters( 'automatorwp_campaign_monitor_settings_fields', array(
            $prefix . 'client_id' => array(
                'name' => __( 'Client ID:', 'automatorwp' ),
                'desc' => __( 'Your Campaign Monitor Client ID.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp' ),
                'desc' => __( 'Your Campaign Monitor API Key.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_campaign_monitor_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;
}
add_filter( "automatorwp_settings_campaign_monitor_meta_boxes", 'automatorwp_campaign_monitor_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_campaign_monitor_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    $client_id = automatorwp_campaign_monitor_get_option( 'client_id', '' );
    $api_key = automatorwp_campaign_monitor_get_option( 'api_key', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-campaign-monitor-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Campaign Monitor:', 'automatorwp' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo esc_attr( $field_id ); ?>" class="button button-primary" href="#"><?php echo __( 'Save Credentials', 'automatorwp' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Enter your Client ID and API Key, then click "Save Credentials" to connect.', 'automatorwp' ); ?></p>
            <?php if ( ! empty( $client_id ) && ! empty( $api_key ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Connected to Campaign Monitor successfully.', 'automatorwp' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php

}
