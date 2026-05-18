<?php
/**
 * Admin
 *  
 * @since    1.0.0
 * @package  AutomatorWP\DaIlybot\Admin
 * @author   AutomatorWP
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 * 
 * @since 1.0.0
 * 
 * @param string
 * 
 * @return mixed
 */
function automatorwp_dailybot_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_dailybot_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 * 
 * @since   1.0.0
 * 
 * @return  array
 */
function automatorwp_dailybot_settings_sections( $automatorwp_settings_section ) {

    $automatorwp_settings_section['dailybot'] = array(
        'title' => __( 'Dailybot', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
        'icon'  => 'dashicons-dailybot'
    );

    return $automatorwp_settings_section;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_dailybot_settings_sections' );

/**
 * Register plugin settings meta boxes
 * 
 * @since   1.0.0
 * 
 * @return  array
 */
function automatorwp_dailybot_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_dailybot_';

    $meta_boxes['automatorwp-dailybot-settings'] = array(
        'title'  => automatorwp_dashicon('dailybot') . __( 'Dailybot' , AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ),
        'fields' => apply_filters( 'automatorwp_dailybot_settings_fields', array(
            $prefix . 'key' => array(
                'name' => __('API key', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
                'desc' => sprintf(__('Your Dailybot API key.', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) ),
                'type' => 'text'
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_dailybot_authorize_display_cb'
            )
        ) )
    );

    return $meta_boxes;
}
add_filter("automatorwp_settings_dailybot_meta_boxes", 'automatorwp_dailybot_settings_meta_boxes');

/**
 * Display callback for the authorize setting
 * 
 * @since  1.0.0
 * 
 * @param array      $field_args  Array of field arguments.
 * @param CMB2_Field $field       The field object.
 */
function automatorwp_dailybot_authorize_display_cb( $field_args, $field ) {
      $field_id = $field_args['id'];

      $key = automatorwp_dailybot_get_option( 'key', '' );
      ?>
      <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php _e('Connect with Dailybot:',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id ?>" class="button button-primary" href="#" ><?php _e('Save credentials', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) ?></a>
            <p class="cmb2-metabox-description"><?php _e('Add you Dailybot API key and click on "Authorize" to connect.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) ?></p>
            <?php if( ! empty( $key ) ) { ?>
                <div class="automatorwp-notice-success"><?php _e('Site connected with Dailybot successfully.',AUTOMATORWP_DAILYBOT_TEXT_DOMAIN) ?></div>
            <?php } ?>
        </div>
      </div>
     <?php 
}