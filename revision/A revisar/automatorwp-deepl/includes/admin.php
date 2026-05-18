<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\DeepL\Admin
 * @author      AutomatorWP <contact@automatorwp.com>
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
function automatorwp_deepl_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_deepl_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @param array $automatorwp_settings_sections
 * @return array
 */
function automatorwp_deepl_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['deepl'] = array(
        'title' => __( 'DeepL', 'automatorwp-deepl' ),
        'icon'  => 'dashicons-translation',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_deepl_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 * @return array
 */
function automatorwp_deepl_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_deepl_';

    $meta_boxes['automatorwp-deepl-settings'] = array(
        'title'  => __( 'DeepL', 'automatorwp-deepl' ),
        'fields' => apply_filters( 'automatorwp_deepl_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-deepl' ),
                'desc' => __( 'Your DeepL API key. Free plan keys end in <strong>:fx</strong>. Get yours at <a href="https://www.deepl.com/pro#developer" target="_blank">deepl.com/pro</a>.', 'automatorwp-deepl' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_deepl_authorize_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( 'automatorwp_settings_deepl_meta_boxes', 'automatorwp_deepl_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args
 * @param CMB2_Field $field
 */
function automatorwp_deepl_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    $api_key  = automatorwp_deepl_get_option( 'api_key', '' );

    // Detect plan from key suffix
    $plan = '';
    if ( ! empty( $api_key ) ) {
        $plan = ( substr( $api_key, -3 ) === ':fx' )
            ? __( 'Free plan detected', 'automatorwp-deepl' )
            : __( 'Pro plan detected', 'automatorwp-deepl' );
    }

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-deepl-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with DeepL:', 'automatorwp-deepl' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo esc_attr( $field_id ); ?>" class="button button-primary" href="#">
                <?php echo __( 'Save credentials', 'automatorwp-deepl' ); ?>
            </a>
            <p class="cmb2-metabox-description">
                <?php echo __( 'Add your DeepL API Key and click "Save credentials" to connect.', 'automatorwp-deepl' ); ?>
            </p>
            <?php if ( ! empty( $api_key ) ) : ?>
                <div class="automatorwp-notice-success">
                    <?php echo __( 'Site connected with DeepL successfully.', 'automatorwp-deepl' ); ?>
                    <?php if ( ! empty( $plan ) ) : ?>
                        <strong> &mdash; <?php echo esc_html( $plan ); ?></strong>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

}
