<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Bento\Admin
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
function automatorwp_bento_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_bento_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_bento_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['bento'] = array(
        'title' => __( 'Bento', 'automatorwp' ),
        'icon' => '',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_bento_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_bento_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_bento_';

    $meta_boxes['automatorwp-bento-settings'] = array(
        'title' => automatorwp_dashicon( 'groups' ) . __( 'Bento', 'automatorwp' ),
        'fields' => apply_filters( 'automatorwp_bento_settings_fields', array(
            $prefix . 'client_id' => array(
                'name' => __( 'Account ID:', 'automatorwp' ),
                'desc' => __( 'Your Bento app account ID.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'client_secret' => array(
                'name' => __( 'API Key:', 'automatorwp' ),
                'desc' => __( 'Your Bento app API key.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_bento_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_bento_meta_boxes", 'automatorwp_bento_settings_meta_boxes' );


/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_bento_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $key = automatorwp_bento_get_option( 'client_id' );
    $secret = automatorwp_bento_get_option( 'client_secret' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-bento-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Bento:', 'automatorwp' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Try credentials', 'automatorwp' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you Bento API key and API secret fields and click on "Authorize" to connect.', 'automatorwp' ); ?></p>
        </div>    
    </div>
    <?php
}