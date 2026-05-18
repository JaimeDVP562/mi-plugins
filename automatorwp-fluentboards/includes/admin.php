<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the FluentBoards settings section in AutomatorWP settings
 *
 * @since 1.0.0
 *
 * @param array $sections
 *
 * @return array
 */
function automatorwp_fluentboards_settings_sections( $sections )
{
    $sections['fluentboards'] = array(
        'title' => __( 'FluentBoards', 'automatorwp-fluentboards' ),
        'icon'  => '',
    );

    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_fluentboards_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_fluentboard_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_fluentboard_';

    $meta_boxes['automatorwp-fluentboard-settings'] = array(
            'title' => automatorwp_dashicon( 'fluentboard' ) . __( 'FluentBoards', 'automatorwp-fluentboard' ),
            'fields' => apply_filters( 'automatorwp_fluentboard_settings_fields', array(
                    $prefix . 'url' => array(
                            'name' => __( 'API URL:', 'automatorwp-fluentboard' ),
                            'desc' => sprintf( __( 'Your FluentBoard url.'), 'automatorwp-fluentboard' ),
                            'type' => 'text',
                    ),
                    $prefix . 'key' => array(
                            'name' => __( 'API key:', 'automatorwp-fluentboard' ),
                            'desc' => sprintf( __( 'Your FluentBoard API key.'), 'automatorwp-fluentboard' ),
                            'type' => 'text',
                    ),
                    $prefix . 'webhook' => array(
                            'type' => 'text',
                            'render_row_cb' => 'automatorwp_fluentboard_webhook_url_cb',
                    ),
                    $prefix . 'authorize' => array(
                            'type' => 'text',
                            'render_row_cb' => 'automatorwp_fluentboard_authorize_display_cb'
                    ),
            ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_fluentboard_meta_boxes", 'automatorwp_fluentboard_settings_meta_boxes' );
/**
 * Display callback for the webhook URL
 *
 * @since  1.0.0
 *
 */

function automatorwp_fluentboard_webhook_url_cb( ) {

    $webhook_url = automatorwp_fluentboard_get_webhook_url();

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-fluentboard-redirect-url table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Webhook URL:', 'automatorwp-fluentboard' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="text" class="regular-text" value="<?php echo $webhook_url; ?>" readonly>
            <a id="automatorwp_fluentboard_refresh" class="button" href="#"><?php echo __( 'Regenerate URL', 'automatorwp-fluentboard' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Copy this URL and place it on your FluentBoards account.', 'automatorwp-fluentboard' ); ?></p>
        </div>
    </div>
    <?php

}
/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_fluentboards_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];

    $url = automatorwp_fluentboard_get_option( 'url', '' );
    $key = automatorwp_fluentboard_get_option( 'key', '' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-fluentboard-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with FluentBoards:', 'automatorwp-fluentboard' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', 'automatorwp-fluentboard' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you FluentBoards API key and URL fields and click on "Authorize" to connect.', 'automatorwp-fluentboard' ); ?></p>
            <?php if ( ! empty( $url ) && ! empty( $key ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with FluentBoards successfully.', 'automatorwp-fluentboard' ); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
