<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\senpulse\Admin
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
function automatorwp_senpulse_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_senpulse_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_senpulse_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['senpulse'] = array(
        'title' => __( 'senpulse', 'automatorwp-senpulse' ),
        'icon' => 'dashicons-admin-comments',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_senpulse_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_senpulse_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_senpulse_';

    $meta_boxes['automatorwp-senpulse-settings'] = array(
        'title' => automatorwp_dashicon( 'senpulse' ) . __( 'senpulse', 'automatorwp-senpulse' ),
        'fields' => apply_filters( 'automatorwp_senpulse_settings_fields', array(
            $prefix . 'token' => array(
                'name' => __( 'API token:', 'automatorwp-senpulse' ),
                'desc' => sprintf( __( 'Your senpulse API token.'), 'automatorwp-senpulse' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_senpulse_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_senpulse_meta_boxes", 'automatorwp_senpulse_settings_meta_boxes' );


/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_senpulse_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $token = automatorwp_senpulse_get_option( 'token', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-senpulse-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with senpulse:', 'automatorwp-senpulse' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp-senpulse' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you senpulse API Token and click on "Authorize" to connect.', 'automatorwp-senpulse' ); ?></p>
            <?php if ( ! empty( $token ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with senpulse successfully.', 'automatorwp-senpulse' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php
}