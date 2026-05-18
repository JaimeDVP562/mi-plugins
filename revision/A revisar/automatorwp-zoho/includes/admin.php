<?php

/**
 * Admin functions for Zoho integration
 *
 * @package     AutomatorWP\Integrations\Zoho\Admin
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Shortcut function to get Zoho options
 */
function automatorwp_zoho_get_option($option_name, $default = false)
{
    // Usamos automatorwp_get_option que busca dentro del array 'automatorwp_settings'
    return automatorwp_get_option('automatorwp_zoho_' . $option_name, $default);
}

/**
 * Register Zoho settings section
 */
function automatorwp_zoho_settings_sections($sections)
{
    $sections['zoho'] = array(
        'title' => __('Zoho CRM', 'automatorwp-zoho'),
        // Cambiado a un icono más descriptivo
        'icon'  => 'dashicons-id-alt',
    );

    return $sections;
}
add_filter('automatorwp_settings_sections', 'automatorwp_zoho_settings_sections');

/**
 * Register Zoho settings meta boxes
 */
function automatorwp_zoho_settings_meta_boxes($meta_boxes)
{
    $prefix = 'automatorwp_zoho_';

    $meta_boxes['automatorwp-zoho-settings'] = array(
        'title'  => automatorwp_dashicon('admin-generic') . __('Zoho CRM Settings', 'automatorwp-zoho'),
        'fields' => array(
            $prefix . 'access_token' => array(
                'name' => __('Access Token:', 'automatorwp-zoho'),
                'desc' => __('Your Zoho API access token.', 'automatorwp-zoho'),
                'type' => 'text',
                'attributes' => array(
                    'placeholder' => '1000.xxxx...',
                ),
            ),
            $prefix . 'region' => array(
                'name'    => __('Region:', 'automatorwp-zoho'),
                'desc'    => __('Select your Zoho account region.', 'automatorwp-zoho'),
                'type'    => 'select',
                'options' => array(
                    'com'    => 'United States (.com)',
                    'eu'     => 'Europe (.eu)',
                    'in'     => 'India (.in)',
                    'com.au' => 'Australia (.com.au)',
                ),
                'default' => 'com',
            ),
            // Este es el campo que renderiza los botones
            'zoho_authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_zoho_authorize_display_cb'
            ),
        ),
    );

    return $meta_boxes;
}
add_filter('automatorwp_settings_zoho_meta_boxes', 'automatorwp_zoho_settings_meta_boxes');

/**
 * Display callback for the authorize setting
 */
function automatorwp_zoho_authorize_display_cb($field_args, $field)
{
    $prefix    = 'automatorwp_zoho_';
    // Comprobamos si ya hay un token guardado para habilitar o no el botón de borrar
    $token     = automatorwp_zoho_get_option('access_token');
    $has_token = ! empty($token);
?>
    <div class="cmb-row cmb-type-custom table-layout">
        <div class="cmb-th">
            <label><?php echo esc_html__('Connect with Zoho CRM:', 'automatorwp-zoho'); ?></label>
        </div>

        <div class="cmb-td">
            <button
                type="button"
                id="<?php echo esc_attr($prefix . 'authorize'); ?>"
                class="button button-primary">
                <?php echo esc_html__('Save Credentials', 'automatorwp-zoho'); ?>
            </button>

            <button
                type="button"
                id="<?php echo esc_attr($prefix . 'delete_credentials'); ?>"
                class="button"
                style="margin-left:8px; <?php echo $has_token ? '' : 'opacity:.5; cursor:not-allowed;'; ?>"
                <?php disabled(! $has_token); ?>>
                <?php echo esc_html__('Delete Credentials', 'automatorwp-zoho'); ?>
            </button>

            <div id="<?php echo esc_attr($prefix . 'response'); ?>"></div>
        </div>
    </div>
<?php
}
