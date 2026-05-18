<?php

/**
 * Admin (Settings section for Grok)
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register Grok section in AutomatorWP settings
 */
function automatorwp_grok_settings_sections($sections)
{
    $sections['grok'] = array(
        'title' => __('Grok', 'automatorwp-grok'),
        'icon'  => 'dashicons-grok', // Custom CSS class defined in our CSS file
    );

    return $sections;
}
add_filter('automatorwp_settings_sections', 'automatorwp_grok_settings_sections');

/**
 * Register Grok meta boxes and fields
 */
function automatorwp_grok_settings_meta_boxes($meta_boxes)
{
    $prefix = 'automatorwp_grok_';

    $meta_boxes['automatorwp-grok-settings'] = array(
        'title'  => __('Grok API Configuration', 'automatorwp-grok'),
        'fields' => array(
            $prefix . 'token' => array(
                'name' => __('API token:', 'automatorwp-grok'),
                'desc' => sprintf(
                    __('Generate your token in the <a href="%s" target="_blank">xAI Console</a> and paste it here.', 'automatorwp-grok'),
                    'https://console.x.ai/'
                ),
                'type' => 'text',
                'attributes' => array(
                    'placeholder' => 'xai-...'
                ),
            ),
            $prefix . 'model' => array(
                'name'    => __('Default text model:', 'automatorwp-grok'),
                'desc'    => __('Default model used for text generation (e.g., grok-2-1212).', 'automatorwp-grok'),
                'type'    => 'text',
                'default' => 'grok-2-1212',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_grok_authorize_display_cb',
            ),
        ),
    );

    return $meta_boxes;
}
add_filter('automatorwp_settings_grok_meta_boxes', 'automatorwp_grok_settings_meta_boxes');

/**
 * Callback to render the authorization button and connection status
 */
function automatorwp_grok_authorize_display_cb($field_args, $field)
{
    $prefix = 'automatorwp_grok_';

    // Get the saved token using the WordPress option
    $token = get_option('automatorwp_grok_api_key');
?>
    <div class="cmb-row cmb-type-custom table-layout">
        <div class="cmb-th">
            <label><?php echo esc_html__('Connection Status', 'automatorwp-grok'); ?></label>
        </div>
        <div class="cmb-td">
            <button type="button" id="<?php echo esc_attr($prefix . 'authorize_btn'); ?>" class="button button-secondary">
                <?php echo esc_html__('Verify Connection', 'automatorwp-grok'); ?>
            </button>

            <p class="cmb2-metabox-description">
                <?php echo esc_html__('Verify if your xAI API token is active and has the correct permissions.', 'automatorwp-grok'); ?>
            </p>

            <?php if (! empty($token)) : ?>
                <div class="automatorwp-notice-success" style="margin-top: 10px; padding: 8px; border-left: 4px solid #46b450; background: #fff;">
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; vertical-align: text-bottom;"></span>
                    <?php echo esc_html__('A token is saved. Click "Verify Connection" to test it.', 'automatorwp-grok'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
