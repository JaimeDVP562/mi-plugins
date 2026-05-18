<?php

/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Bitly\Admin
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string $option_name
 * @param bool   $default
 *
 * @return mixed
 */
function automatorwp_bitly_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_bitly_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @param array $automatorwp_settings_sections
 *
 * @return array
 */
function automatorwp_bitly_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['bitly'] = array(
        'title' => __( 'Bitly', 'automatorwp-bitly' ),
        'icon'  => '',
    );

    return $automatorwp_settings_sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_bitly_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function automatorwp_bitly_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_bitly_';

    $meta_boxes['automatorwp-bitly-settings'] = array(
        'title'  => automatorwp_dashicon( 'admin-links' ) . __( 'Bitly', 'automatorwp-bitly' ),
        'fields' => apply_filters( 'automatorwp_bitly_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-bitly' ),
                'desc' => __( 'Your Bitly API key. You can find it in your Bitly account under Settings > Developer Settings > API.', 'automatorwp-bitly' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_bitly_authorize_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;
}
add_filter( 'automatorwp_settings_bitly_meta_boxes', 'automatorwp_bitly_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args
 * @param CMB2_Field $field
 */
function automatorwp_bitly_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];

?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-bitly-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Bitly:', 'automatorwp-bitly' ); ?></label>
        </div>
        <div class="cmb-td">
            <div id="automatorwp-bitly-authorize-wrapper">
                <button id="<?php echo esc_attr( $field_id ); ?>" class="button button-primary" type="button">
                    <?php echo __( 'Try credentials', 'automatorwp-bitly' ); ?>
                </button>
                <p class="cmb2-metabox-description">
                    <?php echo __( 'Enter your Bitly API Key above and click "Try credentials" to connect.', 'automatorwp-bitly' ); ?>
                </p>
            </div>
        </div>
    </div>
<?php
}