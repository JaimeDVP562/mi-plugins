<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Smoove\Admin
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
function automatorwp_smoove_get_option( $option_name, $default = false ) {
    $prefix = 'automatorwp_smoove_';
    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_smoove_settings_sections( $automatorwp_settings_sections ) {
    $automatorwp_settings_sections['smoove'] = array(
        'title' => __( 'Smoove', 'automatorwp-smoove' ),
        'icon' => 'dashicons-admin-generic',
    );
    return $automatorwp_settings_sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_smoove_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_smoove_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'automatorwp_smoove_';
    $meta_boxes['automatorwp-smoove-settings'] = array(
        'title' => automatorwp_dashicon( 'admin-generic' ) . __( 'Smoove', 'automatorwp-smoove' ),
        'fields' => apply_filters( 'automatorwp_smoove_settings_fields', array(
            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-smoove' ),
                'desc' => __( 'Your Smoove app API key.', 'automatorwp-smoove' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_smoove_authorize_display_cb'
            ),
        ) ),
    );
    return $meta_boxes;
}
add_filter( "automatorwp_settings_smoove_meta_boxes", 'automatorwp_smoove_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_smoove_authorize_display_cb( $field_args, $field ) {
    $access_valid = automatorwp_smoove_get_option( 'access_valid' );
    $field_id = $field_args['id'];
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-smoove-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Smoove:', 'automatorwp-smoove' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="hidden" name="awp-smoove-outh-ajax-nonce" id="awp-smoove-outh-ajax-nonce" value="<?php echo wp_create_nonce( 'awp-outh-ajax-nonce' ); ?>" />           
            <input type="button" name="automatorwp_save_smoove_oauth" id="<?php echo $field_id; ?>" value="Save Credentials" class="button button-primary" />
            <?php if ( $access_valid ){ ?>
            <input type="button" name="automatorwp_remove_smoove_oauth" id="automatorwp_remove_smoove_oauth" value="Delete Credentials" class="button button-danger" /><br>
            <?php } ?>
            <p id='awp_smoove_oauth_status'></p>
        </div>
    </div>
    <?php
}
