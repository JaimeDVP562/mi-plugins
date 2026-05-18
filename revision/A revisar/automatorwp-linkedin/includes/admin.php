<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Linkedin\Admin
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
function automatorwp_linkedin_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_linkedin_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_linkedin_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['linkedin'] = array(
        'title' => __( 'Linkedin', 'automatorwp-linkedin' ),
        'icon' => 'dashicons-linkedin',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_linkedin_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_linkedin_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_linkedin_';

    $meta_boxes['automatorwp-linkedin-settings'] = array(
        'title' => automatorwp_dashicon( 'linkedin' ) . __( 'Linkedin', 'automatorwp-linkedin' ),
        'fields' => apply_filters( 'automatorwp_linkedin_settings_fields', array(
            $prefix . 'consumer_key' => array(
                'name' => __( 'API Key:', 'automatorwp-linkedin' ),
                'desc' => __( 'Your linkedin app API key.', 'automatorwp-linkedin' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_linkedin_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_linkedin_meta_boxes", 'automatorwp_linkedin_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_linkedin_authorize_display_cb( $field_args, $field ) {

    $access_valid = automatorwp_linkedin_get_option( 'access_valid' );

    $field_id = $field_args['id'];

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-linkedin-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with linkedin:', 'automatorwp-linkedin' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="hidden" name="awp-linkedin-outh-ajax-nonce" id="awp-linkedin-outh-ajax-nonce" value="<?php echo wp_create_nonce( 'awp-outh-ajax-nonce' ); ?>" />           
            <input type="button" name="automatorwp_save_linkedin_oauth" id="<?php echo $field_id; ?>" value="Save Credentials" class="button button-primary" />
            <?php if ( $access_valid ){ ?>
            <input type="button" name="automatorwp_remove_linkedin_oauth" id="automatorwp_remove_linkedin_oauth" value="Delete Credentials" class="button button-danger" /><br>
            <?php } ?>
            <p id='awp_linkedin_oauth_status'></p>
        </div>
    </div>
    	
    <?php
}