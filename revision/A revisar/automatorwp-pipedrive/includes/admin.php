<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Pipedrive\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Jonathan Agudo <jonathanagudo8@gmail.com>
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
function automatorwp_pipedrive_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_pipedrive_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_pipedrive_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['pipedrive'] = array(
        'title' => __( 'Pipedrive', 'automatorwp-pipedrive' ),
        'icon' => 'dashicons-pipedrive',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_pipedrive_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_pipedrive_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_pipedrive_';

    $meta_boxes['automatorwp-pipedrive-settings'] = array(
        'title' => automatorwp_dashicon( 'pipedrive' ) . __( 'pipedrive', 'automatorwp-pipedrive' ),
        'fields' => apply_filters( 'automatorwp_pipedrive_settings_fields', array(
            $prefix . 'consumer_key' => array(
                'name' => __( 'API Key:', 'automatorwp-pipedrive' ),
                'desc' => __( 'Your pipedrive app API key.', 'automatorwp-pipedrive' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_pipedrive_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_pipedrive_meta_boxes", 'automatorwp_pipedrive_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_pipedrive_authorize_display_cb( $field_args, $field ) {

    $access_valid = automatorwp_pipedrive_get_option( 'access_valid' );

    $field_id = $field_args['id'];

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-pipedrive-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with pipedrive:', 'automatorwp-pipedrive' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="hidden" name="awp-pipedrive-outh-ajax-nonce" id="awp-pipedrive-outh-ajax-nonce" value="<?php echo wp_create_nonce( 'awp-outh-ajax-nonce' ); ?>" />           
            <input type="button" name="automatorwp_save_pipedrive_oauth" id="<?php echo $field_id; ?>" value="Save Credentials" class="button button-primary" />
            <?php if ( $access_valid ){ ?>
            <input type="button" name="automatorwp_remove_pipedrive_oauth" id="automatorwp_remove_pipedrive_oauth" value="Delete Credentials" class="button button-danger" /><br>
            <?php } ?>
            <p id='awp_pipedrive_oauth_status'></p>
        </div>
    </div>	
    
    <?php
}