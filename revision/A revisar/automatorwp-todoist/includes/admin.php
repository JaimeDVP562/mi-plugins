<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Todoist\Admin
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
function automatorwp_todoist_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_todoist_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_todoist_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['todoist'] = array(
        'title' => __( 'Todoist', 'automatorwp-todoist' ),
        'icon' => 'dashicons-todoist',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_todoist_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_todoist_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_todoist_';

    $meta_boxes['automatorwp-todoist-settings'] = array(
        'title' => automatorwp_dashicon( 'todoist' ) . __( 'Todoist', 'automatorwp-todoist' ),
        'fields' => apply_filters( 'automatorwp_todoist_settings_fields', array(
            $prefix . 'consumer_key' => array(
                'name' => __( 'API Token:', 'automatorwp-todoist' ),
                'desc' => __( 'Your Todoist Dashboard API Token.', 'automatorwp-todoist' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_todoist_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_todoist_meta_boxes", 'automatorwp_todoist_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_todoist_authorize_display_cb( $field_args, $field ) {

    $access_valid = automatorwp_todoist_get_option( 'access_valid' );

    $field_id = $field_args['id'];

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-todoist-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with todoist:', 'automatorwp-todoist' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="hidden" name="awp-todoist-outh-ajax-nonce" id="awp-todoist-outh-ajax-nonce" value="<?php echo wp_create_nonce( 'awp-outh-ajax-nonce' ); ?>" />           
            <input type="button" name="automatorwp_save_todoist_oauth" id="<?php echo $field_id; ?>" value="Save Credentials" class="button button-primary" />
            <?php if ( $access_valid ){ ?>
            <input type="button" name="automatorwp_remove_todoist_oauth" id="automatorwp_remove_todoist_oauth" value="Delete Credentials" class="button button-danger" /><br>
            <?php } ?>
            <p id='awp_todoist_oauth_status'></p>
        </div>
    </div>
    	
    <?php
}