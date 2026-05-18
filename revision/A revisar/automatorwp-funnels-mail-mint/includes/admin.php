<?php
/**
 * Admin
 * 
 * @package AutomatorWP\Mailmint\Admin
 * @author  AutomatorWP
 * @since   0.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 * 
 * @since 0.1.0
 * 
 * @param string  $name
 * @param string  $default
 * 
 * @return mixed
 */
function automatorwp_mailmint_get_option( $name, $default = '' ) {
    $settings = get_option( 'automatorwp_settings', array() );
    $key = 'automatorwp_mailmint_' . $name;
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Register plugin settings sections
 * 
 * @since 0.1.0
 * 
 * @return array
 */
function automatorwp_mailmint_settings_sections( $sections ) {
    $sections['mailmint'] = array(
        'title' => __( 'Mail Mint', 'automatorwp-funnels-mail-mint' ),
        'icon'  => 'dashicons-email',
    );
    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_mailmint_settings_sections' );

/**
 * Register plugin settings meta boxes
 * 
 * @since 0.1.0
 * 
 * @return array
 */
function automatorwp_mailmint_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'automatorwp_mailmint_';
    $meta_boxes['automatorwp-mailmint-settings'] = array(
        'title' => __( 'Mail Mint', 'automatorwp-funnels-mail-mint' ),
        'fields' => array(
            $prefix . 'auth_method' => array(
                'name' => __( 'Auth method', 'automatorwp-funnels-mail-mint' ),
                'type' => 'select',
                'options' => array(
                    'internal' => __( 'Use internal Mail Mint plugin functions (preferred)', 'automatorwp-funnels-mail-mint' ),
                    'api' => __( 'API Key', 'automatorwp-funnels-mail-mint' ),
                ),
                'default' => 'internal',
            ),
            $prefix . 'api_key' => array(
                'name' => __( 'API Key', 'automatorwp-funnels-mail-mint' ),
                'type' => 'text',
            ),
            $prefix . 'api_base' => array(
                'name' => __( 'API Base URL', 'automatorwp-funnels-mail-mint' ),
                'type' => 'text',
                'desc' => __( 'Ej: https://api.mailmint.example/v1 (Optional if you use internal functions)', 'automatorwp-funnels-mail-mint' )
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_mailmint_authorize_display_cb',
            ),
        ),
    );
    return $meta_boxes;
}
add_filter( 'automatorwp_settings_mailmint_meta_boxes', 'automatorwp_mailmint_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 * 
 * @since  0.1.0
 * 
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_mailmint_authorize_display_cb( $field_args, $field ) {
    ?>
    <div class="cmb-row">
        <div class="cmb-th"><label><?php _e( 'Connect Mail Mint', 'automatorwp-funnels-mail-mint' ); ?></label></div>
        <div class="cmb-td">
            <a id="automatorwp_mailmint_authorize_btn" class="button button-primary" href="#"><?php echo __( 'Save / Validate', 'automatorwp-funnels-mail-mint' ); ?></a>
            <p class="cmb2-metabox-description"><?php  _e( 'Save and validate the credentials.', 'automatorwp-funnels-mail-mint' ); ?></p>
        </div>
    </div>
    <?php
}
