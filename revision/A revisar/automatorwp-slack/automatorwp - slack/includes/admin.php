<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Slack\Admin
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
function automatorwp_slack_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_slack_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}


/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_slack_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['slack'] = array(
        'title' => __( 'Slack', 'automatorwp-slack' ),
        'icon' => 'dashicons-admin-comments',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_slack_settings_sections' );


/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_slack_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_slack_';

    $meta_boxes['automatorwp-slack-settings'] = array(
        'title' => automatorwp_dashicon( 'slack' ) . __( 'Slack', 'automatorwp-slack' ),
        'fields' => apply_filters( 'automatorwp_slack_settings_fields', array(
            $prefix . 'token' => array(
                'name' => __( 'API token:', 'automatorwp-slack' ),
                'desc' => sprintf( __( 'Your Slack API token.'), 'automatorwp-slack' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_slack_authorize_display_cb'
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_slack_meta_boxes", 'automatorwp_slack_settings_meta_boxes' );


/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_slack_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $token = automatorwp_slack_get_option( 'token', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-slack-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Slack:', 'automatorwp-slack' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp-slack' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you Slack API Token and click on "Authorize" to connect.', 'automatorwp-slack' ); ?></p>
            <?php if ( ! empty( $token ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with Slack successfully.', 'automatorwp-slack' ); ?></div>
            <?php endif; ?>
        </div>    
    </div>
    <?php
}