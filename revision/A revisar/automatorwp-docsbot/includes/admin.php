<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\DocsBot\Admin
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param mixed     $default
 *
 * @return mixed
 */
function automatorwp_docsbot_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_docsbot_';

    return automatorwp_get_option( $prefix . $option_name, $default );

}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @param array $automatorwp_settings_sections
 *
 * @return array
 */
function automatorwp_docsbot_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['docsbot'] = array(
        'title' => __( 'DocsBot AI', 'automatorwp-docsbot' ),
        'icon'  => 'dashicons-format-chat',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_docsbot_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function automatorwp_docsbot_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_docsbot_';

    $meta_boxes['automatorwp-docsbot-settings'] = array(
        'title'  => __( 'DocsBot AI', 'automatorwp-docsbot' ),
        'fields' => apply_filters( 'automatorwp_docsbot_settings_fields', array(

            $prefix . 'api_key' => array(
                'name' => __( 'API Key:', 'automatorwp-docsbot' ),
                'desc' => __( 'Your DocsBot AI API key. Found in your DocsBot account settings.', 'automatorwp-docsbot' ),
                'type' => 'text',
            ),

            $prefix . 'team_id' => array(
                'name' => __( 'Team ID:', 'automatorwp-docsbot' ),
                'desc' => __( 'Your DocsBot AI Team ID.', 'automatorwp-docsbot' ),
                'type' => 'text',
            ),

            $prefix . 'bot_id' => array(
                'name' => __( 'Bot ID:', 'automatorwp-docsbot' ),
                'desc' => __( 'The ID of the DocsBot bot to use for actions.', 'automatorwp-docsbot' ),
                'type' => 'text',
            ),

            $prefix . 'webhook_secret' => array(
                'name' => __( 'Webhook Secret:', 'automatorwp-docsbot' ),
                'desc' => __( 'The signing secret from your DocsBot webhook configuration. Used to verify incoming webhook requests.', 'automatorwp-docsbot' ),
                'type' => 'text',
            ),

            $prefix . 'webhook_info' => array(
                'type'           => 'text',
                'render_row_cb'  => 'automatorwp_docsbot_webhook_info_display_cb',
            ),

        ) ),
    );

    return $meta_boxes;

}
add_filter( 'automatorwp_settings_docsbot_meta_boxes', 'automatorwp_docsbot_settings_meta_boxes' );

/**
 * Display callback showing the webhook URL to register in DocsBot
 *
 * @since  1.0.0
 *
 * @param array      $field_args
 * @param CMB2_Field $field
 */
function automatorwp_docsbot_webhook_info_display_cb( $field_args, $field ) {

    $webhook_url = rest_url( 'automatorwp-docsbot/v1/webhook' );
    $api_key     = automatorwp_docsbot_get_option( 'api_key' );
    $team_id     = automatorwp_docsbot_get_option( 'team_id' );
    $bot_id      = automatorwp_docsbot_get_option( 'bot_id' );
    $configured  = ! empty( $api_key ) && ! empty( $team_id ) && ! empty( $bot_id );

    ?>
    <div class="cmb-row cmb-type-custom table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php esc_html_e( 'Webhook URL:', 'automatorwp-docsbot' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="text" readonly value="<?php echo esc_attr( $webhook_url ); ?>" style="width: 100%;" onclick="this.select();" />
            <p class="cmb2-metabox-description">
                <?php esc_html_e( 'Register this URL in your DocsBot bot\'s webhook settings to receive events (lead.created, conversation.escalated, conversation.rated).', 'automatorwp-docsbot' ); ?>
            </p>
            <?php if ( $configured ) : ?>
                <div class="automatorwp-notice-success">
                    <?php esc_html_e( 'DocsBot AI is configured.', 'automatorwp-docsbot' ); ?>
                </div>
            <?php else : ?>
                <div class="automatorwp-notice-warning">
                    <?php esc_html_e( 'Please fill in the API Key, Team ID, and Bot ID to enable DocsBot AI actions.', 'automatorwp-docsbot' ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

}
