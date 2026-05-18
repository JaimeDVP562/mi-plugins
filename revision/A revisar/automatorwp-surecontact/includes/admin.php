<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\surecontact\Admin
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
function AutomatorWP_SureContact_get_option( $option_name, $default = false ) {

    $prefix = 'AutomatorWP_SureContact_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function AutomatorWP_SureContact_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['surecontact'] = array(
        'title' => __( 'surecontact', 'automatorwp-surecontact' ),
        'icon' => 'dashicons-admin-comments',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'AutomatorWP_SureContact_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function AutomatorWP_SureContact_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'AutomatorWP_SureContact_';

    $meta_boxes['automatorwp-surecontact-settings'] = array(
        'title' => automatorwp_dashicon( 'surecontact' ) . __( 'surecontact', 'automatorwp-surecontact' ),
        'fields' => apply_filters( 'AutomatorWP_SureContact_settings_fields', array(
            $prefix . 'token' => array(
                'name' => __( 'API token:', 'automatorwp-surecontact' ),
                'desc' => sprintf( __( 'Your surecontact API token.'), 'automatorwp-surecontact' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'AutomatorWP_SureContact_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_surecontact_meta_boxes", 'AutomatorWP_SureContact_settings_meta_boxes' );


/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function AutomatorWP_SureContact_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $token = AutomatorWP_SureContact_get_option( 'token', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-surecontact-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with surecontact:', 'automatorwp-surecontact' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp-surecontact' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you surecontact API Token and click on "Authorize" to connect.', 'automatorwp-surecontact' ); ?></p>
            <?php if ( ! empty( $token ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with surecontact successfully.', 'automatorwp-surecontact' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php
}