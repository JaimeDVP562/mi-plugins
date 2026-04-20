<?php

/**
 * Admin (Settings section for DeepSeek)
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register DeepSeek section in AutomatorWP settings
 */
function automatorwp_deepseek_settings_sections($sections)
{
    $sections['deepseek'] = array(
        'title' => __('DeepSeek', 'automatorwp-deepseek'),
        'icon'  => 'dashicons-admin-generic', // This can be customized via CSS later
    );

    return $sections;
}
add_filter('automatorwp_settings_sections', 'automatorwp_deepseek_settings_sections');

/**
 * Register DeepSeek meta boxes and fields
 */
function automatorwp_deepseek_settings_meta_boxes($meta_boxes)
{
    $prefix = 'automatorwp_deepseek_';

    $meta_boxes['automatorwp-deepseek-settings'] = array(
        'title'  => __('DeepSeek API Configuration', 'automatorwp-deepseek'),
        'fields' => array(
            $prefix . 'token' => array(
                'name' => __('API token:', 'automatorwp-deepseek'),
                'desc' => sprintf(
                    __('Generate your token in the <a href="%s" target="_blank">DeepSeek Platform</a> and paste it here.', 'automatorwp-deepseek'),
                    'https://platform.deepseek.com/'
                ),
                'type' => 'text',
                'attributes' => array(
                    'placeholder' => 'sk-...'
                ),
            ),
            $prefix . 'model' => array(
                'name'    => __('Default text model:', 'automatorwp-deepseek'),
                'desc'    => __('Default model used for text generation (e.g., deepseek-chat).', 'automatorwp-deepseek'),
                'type'    => 'text',
                'default' => 'deepseek-chat',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_deepseek_authorize_display_cb',
            ),
        ),
    );

    return $meta_boxes;
}
add_filter('automatorwp_settings_deepseek_meta_boxes', 'automatorwp_deepseek_settings_meta_boxes');

/**
 * Callback to render the authorization button and connection status
 */
function automatorwp_deepseek_authorize_display_cb($field_args, $field)
{
    $prefix = 'automatorwp_deepseek_';
    // Using a helper function to get the saved token
    $token  = function_exists('automatorwp_deepseek_get_token') ? automatorwp_deepseek_get_token() : '';
?>
    <div class="cmb-row cmb-type-custom table-layout">
        <div class="cmb-th">
            <label><?php echo esc_html__('Connection Status', 'automatorwp-deepseek'); ?></label>
        </div>
        <div class="cmb-td">
            <button type="button" id="<?php echo esc_attr($prefix . 'authorize_btn'); ?>" class="button button-secondary">
                <?php echo esc_html__('Verify Connection', 'automatorwp-deepseek'); ?>
            </button>

            <p class="cmb2-metabox-description">
                <?php echo esc_html__('Verify if your API token is active and has the correct permissions.', 'automatorwp-deepseek'); ?>
            </p>

            <?php if (! empty($token)) : ?>
                <div class="automatorwp-notice-success" style="margin-top: 10px; padding: 8px; border-left: 4px solid #46b450; background: #fff;">
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; vertical-align: text-bottom;"></span>
                    <?php echo esc_html__('A token is saved. Click "Verify Connection" to test it.', 'automatorwp-deepseek'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
