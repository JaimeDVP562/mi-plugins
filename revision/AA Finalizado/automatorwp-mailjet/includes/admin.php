<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Mailjet\Admin
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
function automatorwp_mailjet_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_mailjet_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_mailjet_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['mailjet'] = array(
        'title' => __( 'Mailjet', 'automatorwp-mailjet' ),
        'icon' => 'dashicons-mailjet',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_mailjet_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_mailjet_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_mailjet_';

    $meta_boxes['automatorwp-mailjet-settings'] = array(
        'title' => automatorwp_dashicon( 'mailjet' ) . __( 'Mailjet', 'automatorwp-mailjet' ),
        'fields' => apply_filters( 'automatorwp_mailjet_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API_KEY:', 'automatorwp-mailjet' ),
                'desc' => __( 'Your mailjet API_KEY.', 'automatorwp-mailjet' ),
                'type' => 'text',
            ),
            $prefix . 'secret_key' => array(
                'name' => __( 'SECRET_KEY:', 'automatorwp-mailjet' ),
                'desc' => __( 'Your mailjet SECRET_KEY.', 'automatorwp-mailjet' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_mailjet_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_mailjet_meta_boxes", 'automatorwp_mailjet_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_mailjet_authorize_display_cb( $field_args, $field ) {

    $access_valid = automatorwp_mailjet_get_option( 'access_valid' );

    $field_id = $field_args['id'];

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-mailjet-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with mailjet:', 'automatorwp-mailjet' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="hidden" name="awp-mailjet-outh-ajax-nonce" id="awp-mailjet-outh-ajax-nonce" value="<?php echo wp_create_nonce( 'awp-outh-ajax-nonce' ); ?>" />           
            <input type="button" name="automatorwp_save_mailjet_oauth" id="<?php echo $field_id; ?>" value="Save Credentials" class="button button-primary" />
            <?php if ( $access_valid ){ ?>
            <input type="button" name="automatorwp_remove_mailjet_oauth" id="automatorwp_remove_mailjet_oauth" value="Delete Credentials" class="button button-danger" /><br>
            <?php } ?>
            <p id='awp_mailjet_oauth_status'></p>
        </div>
    </div>
    	
    <?php
}