<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Ontraport\Admin
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
function automatorwp_ontraport_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_ontraport_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_ontraport_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['ontraport'] = array(
        'title' => __( 'Ontraport', 'automatorwp-ontraport' ),
        'icon' => 'dashicons-ontraport',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_ontraport_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_ontraport_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_ontraport_';

    $meta_boxes['automatorwp-ontraport-settings'] = array(
        'title' => automatorwp_dashicon( 'ontraport' ) . __( 'Ontraport', 'automatorwp-ontraport' ),
        'fields' => apply_filters( 'automatorwp_ontraport_settings_fields', array(
            $prefix . 'app_id' => array(
                'name' => __( 'APP_ID:', 'automatorwp-ontraport' ),
                'desc' => __( 'Your ontraport APP_ID.', 'automatorwp-ontraport' ),
                'type' => 'text',
            ),
            $prefix . 'api_key' => array(
                'name' => __( 'API_KEY:', 'automatorwp-ontraport' ),
                'desc' => __( 'Your ontraport API_KEY.', 'automatorwp-ontraport' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_ontraport_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_ontraport_meta_boxes", 'automatorwp_ontraport_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_ontraport_authorize_display_cb( $field_args, $field ) {

    $access_valid = automatorwp_ontraport_get_option( 'access_valid' );

    $field_id = $field_args['id'];

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-ontraport-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with ontraport:', 'automatorwp-ontraport' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="hidden" name="awp-ontraport-outh-ajax-nonce" id="awp-ontraport-outh-ajax-nonce" value="<?php echo wp_create_nonce( 'awp-outh-ajax-nonce' ); ?>" />           
            <input type="button" name="automatorwp_save_ontraport_oauth" id="<?php echo $field_id; ?>" value="Save Credentials" class="button button-primary" />
            <?php if ( $access_valid ){ ?>
            <input type="button" name="automatorwp_remove_ontraport_oauth" id="automatorwp_remove_ontraport_oauth" value="Delete Credentials" class="button button-danger" /><br>
            <?php } ?>
            <p id='awp_ontraport_oauth_status'></p>
        </div>
    </div>
    	
    <?php
}