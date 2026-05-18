<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\IContact\Admin
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
function automatorwp_iContact_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_iContact_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_iContact_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['iContact'] = array(
        'title' => __( 'IContact', 'automatorwp-iContact' ),
        'icon' => 'dashicons-iContact',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_iContact_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_iContact_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_iContact_';

    $meta_boxes['automatorwp-iContact-settings'] = array(
        'title' => automatorwp_dashicon( 'iContact' ) . __( 'IContact', 'automatorwp-iContact' ),
        'fields' => apply_filters( 'automatorwp_iContact_settings_fields', array(
            $prefix . 'consumer_key' => array(
                'name' => __( 'API Key:', 'automatorwp-iContacto' ),
                'desc' => __( 'Your iContact app API key.', 'automatorwp-iContact' ),
                'type' => 'text',
            ),
            $prefix . 'access_token' => array(
                'name' => __( 'Access Token:', 'automatorwp-iContact' ),
                'desc' => __( 'Your iContact app access token.', 'automatorwp-iContact' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_iContact_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_iContact_meta_boxes", 'automatorwp_iContact_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_iContact_authorize_display_cb( $field_args, $field ) {

    $access_valid = automatorwp_iContact_get_option( 'access_valid' );

    $field_id = $field_args['id'];

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-iContact-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with iContact:', 'automatorwp-iContact' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="hidden" name="awp-iContact-outh-ajax-nonce" id="awp-iContact-outh-ajax-nonce" value="<?php echo wp_create_nonce( 'awp-outh-ajax-nonce' ); ?>" />
            <input type="button" name="automatorwp_save_iContact_oauth" id="<?php echo $field_id; ?>" value="Save Credentials" class="button button-primary" />
            <?php if ( $access_valid ){ ?>
            <input type="button" name="automatorwp_remove_iContact_oauth" id="automatorwp_remove_iContact_oauth" value="Delete Credentials" class="button button-danger" /><br>
            <?php } ?>
            <p id='awp_iContact_oauth_status'></p>
        </div>
    </div>
    	
    <?php
}