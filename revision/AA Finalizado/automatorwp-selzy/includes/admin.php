<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Selzy\Admin
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
function automatorwp_selzy_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_selzy_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_selzy_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['selzy'] = array(
        'title' => __( 'Selzy', 'automatorwp-selzy' ),
        'icon' => 'dashicons-admin-comments',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_selzy_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_selzy_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_selzy_';

    $meta_boxes['automatorwp-selzy-settings'] = array(
        'title' => automatorwp_dashicon( 'selzy' ) . __( 'Selzy', 'automatorwp-selzy' ),
        'fields' => apply_filters( 'automatorwp_selzy_settings_fields', array(
            $prefix . 'token' => array(
                'name' => __( 'API token:', 'automatorwp-selzy' ),
                'desc' => sprintf( __( 'Your Selzy API token.'), 'automatorwp-selzy' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_selzy_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_selzy_meta_boxes", 'automatorwp_selzy_settings_meta_boxes' );


/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_selzy_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $token = automatorwp_selzy_get_option( 'token', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-selzy-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Selzy:', 'automatorwp-selzy' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp-selzy' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you Selzy API Token and click on "Authorize" to connect.', 'automatorwp-selzy' ); ?></p>
            <?php if ( ! empty( $token ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with Selzy successfully.', 'automatorwp-selzy' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php
}