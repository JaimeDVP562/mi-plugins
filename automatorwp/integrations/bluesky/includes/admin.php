<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Bluesky\Admin
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
function automatorwp_bluesky_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_bluesky_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_bluesky_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['bluesky'] = array(
        'title' => __( 'Bluesky', 'automatorwp' ),
        'icon' => 'dashicons-bluesky',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_bluesky_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_bluesky_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_bluesky_';

    $meta_boxes['automatorwp-bluesky-settings'] = array(
        'title' => automatorwp_dashicon( 'bluesky' ) . __( 'Bluesky', 'automatorwp' ),
        'fields' => apply_filters( 'automatorwp_bluesky_settings_fields', array(
            $prefix . 'user_handle' => array(
                'name' => __( 'Bluesky Handle:', 'automatorwp' ),
                'desc' => __( 'Your bluesky account handle.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'user_password' => array(
                'name' => __( 'Password:', 'automatorwp' ),
                'desc' => __( 'Your bluesky account password.', 'automatorwp' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_bluesky_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_bluesky_meta_boxes", 'automatorwp_bluesky_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_bluesky_authorize_display_cb( $field_args, $field ) {

    $user_handle = automatorwp_bluesky_get_option( 'user_handle' );
    $user_password = automatorwp_bluesky_get_option( 'user_password' );

    $field_id = $field_args['id'];

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-bluesky-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with bluesky:', 'automatorwp' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add your User and Password and click on "Authorize" to connect.', 'automatorwp' ); ?></p>
            <?php if ( ! empty( $user_handle )  && ! empty( $user_password ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with Bluesky successfully.', 'automatorwp' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    	
    <?php
}