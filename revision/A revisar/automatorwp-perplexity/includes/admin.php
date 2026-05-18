<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Perplexity\Admin
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_perplexity_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['perplexity'] = array(
        'title' => __( 'Perplexity', 'automatorwp-perplexity' ),
        'icon'  => 'dashicons-admin-generic',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_perplexity_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_perplexity_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'automatorwp_perplexity_';

    $meta_boxes['automatorwp-perplexity-settings'] = array(
        'title'  => __( 'Perplexity', 'automatorwp-perplexity' ),
        'fields' => apply_filters( 'automatorwp_perplexity_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-perplexity' ),
                'desc' => __( 'Your Perplexity API key. Find it in your Perplexity account settings.', 'automatorwp-perplexity' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_perplexity_authorize_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( 'automatorwp_settings_perplexity_meta_boxes', 'automatorwp_perplexity_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_perplexity_authorize_display_cb( $field_args, $field ) {
    $field_id = $field_args['id'];

    $api_key = automatorwp_perplexity_get_api_key();
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-perplexity-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Perplexity:', 'automatorwp-perplexity' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo esc_attr( $field_id ); ?>" class="button button-primary" href="#"><?php echo __( 'Authorize', 'automatorwp-perplexity' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Enter your Perplexity API key above and click "Authorize" to connect.', 'automatorwp-perplexity' ); ?></p>
            <?php if ( ! empty( $api_key ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with Perplexity successfully.', 'automatorwp-perplexity' ); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
