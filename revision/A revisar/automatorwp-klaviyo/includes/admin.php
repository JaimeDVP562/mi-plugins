<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Klaviyo\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Irene Ródenas <irener.rglez@gmail.com>
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
function automatorwp_klaviyo_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_klaviyo_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @param array    $automatorwp_settings_sections
 *
 * @return array
 */
function automatorwp_klaviyo_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['klaviyo'] = array(
        'title' => __( 'Klaviyo', 'automatorwp-klaviyo' ),
        'icon' => 'dashicons-email',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_klaviyo_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array    $meta_boxes
 *
 * @return array
 */
function automatorwp_klaviyo_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_klaviyo_';

    $meta_boxes['automatorwp-klaviyo-settings'] = array(
        'title' => automatorwp_dashicon( 'email' ) . __( 'Klaviyo', 'automatorwp-klaviyo' ),
        'fields' => apply_filters( 'automatorwp_klaviyo_settings_fields', array(
            $prefix . 'key' => array(
                'name' => __( 'API key:', 'automatorwp-klaviyo' ),
                'desc' => sprintf( __( 'Your Klaviyo API key.'), 'automatorwp-klaviyo' ),
                'type' => 'text',
            ),
            $prefix . 'secret' => array(
                'name' => __( 'API Secret:', 'automatorwp-klaviyo' ),
                'desc' => sprintf( __( 'Your Klaviyo API Secret.'), 'automatorwp-klaviyo' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_klaviyo_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_klaviyo_meta_boxes", 'automatorwp_klaviyo_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_klaviyo_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $api_key = automatorwp_klaviyo_get_option( 'api_key', '' );
    $api_secret = automatorwp_klaviyo_get_option( 'api_secret', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-klaviyo-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Klaviyo:', 'automatorwp-klaviyo' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp-klaviyo' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add your Klaviyo API key and API secret fields and click on "Authorize" to connect.', 'automatorwp-klaviyo' ); ?></p>
            <?php if ( ! empty( $api_key ) && ! empty( $api_secret ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with Klaviyo successfully.', 'automatorwp-klaviyo' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php
}
