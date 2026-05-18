<?php
/**
 * Admin 
 *
 * @package     AutomatorWP\Shortio\Admin
 * @since       1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options.
 *
 * @since 1.0.0
 * @param string $option_name Option name to retrieve.
 * @param mixed  $default     Default value if option is not set.
 * @return mixed
 */
function automatorwp_shortio_get_option( $option_name, $default = false ) {
    $prefix = 'automatorwp_shortio_';
    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections.
 *
 * @since 1.0.0
 */
function automatorwp_shortio_settings_sections( $automatorwp_settings_sections ) {
    $automatorwp_settings_sections['shortio'] = array(
        'title' => __( 'Short.io', 'automatorwp-shortio' ),
        'icon'  => '',
    );
    return $automatorwp_settings_sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_shortio_settings_sections' );

/**
 * Register plugin settings meta boxes.
 *
 * @since 1.0.0
 */
function automatorwp_shortio_settings_meta_boxes( $meta_boxes )  {
    $prefix = 'automatorwp_shortio_';

    $meta_boxes['automatorwp-shortio-settings'] = array(
        'title'  => automatorwp_dashicon( 'groups' ) . __( 'Short.io Settings', 'automatorwp-shortio' ),
        'fields' => apply_filters( 'automatorwp_shortio_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-shortio' ),
                'desc' => __( 'Enter your Short.io API Key (Secret Key).', 'automatorwp-shortio' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_shortio_authorize_display_cb'
            ),
        ) ),
    );
    return $meta_boxes;
}
add_filter( "automatorwp_settings_shortio_meta_boxes", 'automatorwp_shortio_settings_meta_boxes' );

/**
 * Display callback for the authorize button.
 *
 * @since 1.0.0
 */
function automatorwp_shortio_authorize_display_cb( $field_args, $field ) {
    $field_id = $field_args['id'];
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-shortio-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php esc_html_e( 'Connect with Short.io:', 'automatorwp-shortio' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo esc_attr( $field_id ); ?>" class="button button-primary" href="#"><?php esc_html_e( 'Try credentials', 'automatorwp-shortio' ); ?></a>
            <p class="cmb2-metabox-description"><?php esc_html_e( 'Enter your API Key and click on "Try credentials" to verify the connection.', 'automatorwp-shortio' ); ?></p>
        </div>    
    </div>
    <?php
}