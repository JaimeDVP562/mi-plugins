<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Cohere\Admin
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_settings_sections( $automatorwp_settings_sections )
{
    $automatorwp_settings_sections['cohere'] = array(
        'title' => __( 'Cohere', 'automatorwp-cohere' ),
        'icon'  => 'dashicons-admin-generic',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_cohere_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_cohere_settings_meta_boxes( $meta_boxes )
{
    $prefix = 'automatorwp_cohere_';

    $meta_boxes['automatorwp-cohere-settings'] = array(
        'title'  => __( 'Cohere', 'automatorwp-cohere' ),
        'fields' => apply_filters( 'automatorwp_cohere_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-cohere' ),
                'desc' => __( 'Your Cohere API key. Find it at dashboard.cohere.com → API Keys.', 'automatorwp-cohere' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_cohere_authorize_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( 'automatorwp_settings_cohere_meta_boxes', 'automatorwp_cohere_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_cohere_authorize_display_cb( $field_args, $field )
{
    $field_id = $field_args['id'];
    $api_key  = automatorwp_cohere_get_api_key();
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-cohere-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php esc_html_e( 'Connect with Cohere:', 'automatorwp-cohere' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo esc_attr( $field_id ); ?>" class="button button-primary" href="#"><?php esc_html_e( 'Authorize', 'automatorwp-cohere' ); ?></a>
            <p class="cmb2-metabox-description"><?php esc_html_e( 'Enter your Cohere API key above and click "Authorize" to connect.', 'automatorwp-cohere' ); ?></p>
            <?php if ( ! empty( $api_key ) ) : ?>
                <div class="automatorwp-notice-success"><?php esc_html_e( 'Site connected with Cohere successfully.', 'automatorwp-cohere' ); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
