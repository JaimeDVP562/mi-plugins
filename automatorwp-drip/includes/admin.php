<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Drip\Admin
 * @author      AutomatorWP <contact@automatorwp.com>
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
function automatorwp_drip_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_drip_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_drip_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['drip'] = array(
        'title' => __( 'Drip', 'automatorwp' ),
        'icon' => '',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_drip_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_drip_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_drip_';

    $meta_boxes['automatorwp-drip-settings'] = array(
        'title' => automatorwp_dashicon( 'groups' ) . __( 'Drip', 'automatorwp' ),
        'fields' => apply_filters( 'automatorwp_drip_settings_fields', array(
            $prefix . 'client_id' => array(
                'name' => __( 'Account ID:', 'automatorwp' ),
                'desc' => __( 'Your Drip app account ID.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'client_secret' => array(
                'name' => __( 'API Key:', 'automatorwp' ),
                'desc' => __( 'Your Drip app API key.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_drip_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_drip_meta_boxes", 'automatorwp_drip_settings_meta_boxes' );


/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_drip_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $key = automatorwp_drip_get_option( 'client_id' );
    $secret = automatorwp_drip_get_option( 'client_secret' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-drip-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Drip:', 'automatorwp' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Try credentials', 'automatorwp' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you Drip API key and API secret fields and click on "Authorize" to connect.', 'automatorwp' ); ?></p>
        </div>    
    </div>
    <?php
}