<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Brevo\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_brevo_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['brevo'] = array(
        'title' => __( 'Brevo', 'automatorwp-brevo' ),
        'icon' => 'dashicons-admin-comments',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_brevo_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_brevo_settings_meta_boxes( $meta_boxes )  {
    $prefix = 'automatorwp_brevo_';

    $meta_boxes['automatorwp-brevo-settings'] = array(
        'title' => automatorwp_dashicon( 'brevo' ) . __( 'Brevo', 'automatorwp-brevo' ),
        'fields' => apply_filters( 'automatorwp_brevo_settings_fields', array(
            $prefix . 'token' => array(
                'name' => __( 'API token:', 'automatorwp-brevo' ),
                'desc' => sprintf( __( 'Your Brevo API token.'), 'automatorwp-brevo' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_brevo_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_brevo_meta_boxes", 'automatorwp_brevo_settings_meta_boxes' );


/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_brevo_authorize_display_cb( $field_args, $field ) {
    $field_id = $field_args['id'];
    
    $token = automatorwp_brevo_get_option( 'token', '' );
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-brevo-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with brevo:', 'automatorwp-brevo' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp-brevo' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you brevo API Token and click on "Authorize" to connect.', 'automatorwp-brevo' ); ?></p>
            <?php if ( ! empty( $token ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with brevo successfully.', 'automatorwp-brevo' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php
}