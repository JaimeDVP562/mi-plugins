<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\GoHighLevel\Admin
 * @since       1.0.0
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Shortcut function to get plugin options.
 *
 * @since 1.0.0
 *
 * @param string $option_name
 * @param bool   $default
 *
 * @return mixed
 */
function automatorwp_gohighlevel_get_option($option_name, $default = false)
{
	$prefix = 'automatorwp_gohighlevel_';

	return automatorwp_get_option($prefix . $option_name, $default);
}

/**
 * Register plugin settings sections.
 *
 * @since 1.0.0
 *
 * @param array $automatorwp_settings_sections
 *
 * @return array
 */
function automatorwp_gohighlevel_settings_sections($automatorwp_settings_sections)
{
	$automatorwp_settings_sections['gohighlevel'] = array(
		'title' => __('GoHighLevel', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
		'icon'  => 'dashicons-admin-links',
	);

	return $automatorwp_settings_sections;
}
add_filter('automatorwp_settings_sections', 'automatorwp_gohighlevel_settings_sections');

/**
 * Register plugin settings meta boxes.
 *
 * @since 1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function automatorwp_gohighlevel_settings_meta_boxes($meta_boxes)
{
	$prefix = 'automatorwp_gohighlevel_';

	$meta_boxes['automatorwp-gohighlevel-settings'] = array(
		'title'  => automatorwp_dashicon('admin-links') . __('GoHighLevel', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
		'fields' => apply_filters('automatorwp_gohighlevel_settings_fields', array(
			$prefix . 'api_key' => array(
				'name' => __('Access Token:', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
				'desc' => __('Use a Private Integration Token or OAuth Access Token from GoHighLevel.', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
				'type' => 'text',
			),
			$prefix . 'location_id' => array(
				'name' => __('Location ID:', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
				'desc' => __('Default GoHighLevel location (optional).', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
				'type' => 'text',
			),
			$prefix . 'webhook_token' => array(
				'name' => __('Webhook Secret Token:', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
				'desc' => __('Secret token used to validate GoHighLevel webhooks.', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN),
				'type' => 'text',
			),
			$prefix . 'webhook_url' => array(
				'type'          => 'text',
				'render_row_cb' => 'automatorwp_gohighlevel_webhook_url_display_cb',
			),
			$prefix . 'authorize' => array(
				'type'          => 'text',
				'render_row_cb' => 'automatorwp_gohighlevel_authorize_display_cb',
			),
			$prefix . 'test_trigger' => array(
				'type'          => 'text',
				'render_row_cb' => 'automatorwp_gohighlevel_test_trigger_display_cb',
			),
		)),
	);

	return $meta_boxes;
}
add_filter('automatorwp_settings_gohighlevel_meta_boxes', 'automatorwp_gohighlevel_settings_meta_boxes');

/**
 * Display callback for the authorize setting.
 *
 * @since 1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object.
 */
function automatorwp_gohighlevel_authorize_display_cb($field_args, $field)
{
	$access_valid = automatorwp_gohighlevel_get_option('access_valid');

	$field_id = $field_args['id'];
	?>

	<div class="cmb-row cmb-type-custom cmb2-id-automatorwp-gohighlevel-authorize table-layout" data-fieldtype="custom">
		<div class="cmb-th">
			<label><?php echo esc_html__('Connect with GoHighLevel:', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<input type="hidden" name="awp-gohighlevel-oauth-ajax-nonce" id="awp-gohighlevel-oauth-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('awp-oauth-ajax-nonce')); ?>" />
			<input type="button" name="automatorwp_save_gohighlevel_oauth" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr__('Save Credentials', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN); ?>" class="button button-primary" />
			<?php if ($access_valid) { ?>
				<input type="button" name="automatorwp_remove_gohighlevel_oauth" id="automatorwp_remove_gohighlevel_oauth" value="<?php echo esc_attr__('Delete Credentials', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN); ?>" class="button button-danger" /><br>
			<?php } ?>
			<p id="awp_gohighlevel_oauth_status"></p>
		</div>
	</div>

	<?php
}

/**
 * Display callback for test trigger.
 *
 * @since 1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object.
 */
function automatorwp_gohighlevel_test_trigger_display_cb($field_args, $field)
{
	?>
	<div class="cmb-row cmb-type-custom cmb2-id-automatorwp-gohighlevel-test table-layout" data-fieldtype="custom">
		<div class="cmb-th">
			<label><?php echo esc_html__('Test Trigger:', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<input type="button" id="automatorwp-gohighlevel-test-trigger" value="<?php echo esc_attr__('Fire Test Trigger', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN); ?>" class="button button-secondary" />
			<span class="spinner" id="automatorwp-gohighlevel-test-spinner" style="display: none; float: none;"></span>
			<p id="automatorwp-gohighlevel-test-result" style="margin-top: 10px;"></p>
		</div>
	</div>
	<script>
		jQuery(document).ready(function($) {
			$('#automatorwp-gohighlevel-test-trigger').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var $spinner = $('#automatorwp-gohighlevel-test-spinner');
				var $result = $('#automatorwp-gohighlevel-test-result');

				$btn.prop('disabled', true);
				$spinner.show();
				$result.html('');

				$.post(
					ajaxurl,
					{
						action: 'automatorwp_gohighlevel_test_trigger',
						nonce: '<?php echo esc_js(wp_create_nonce('automatorwp-gohighlevel-test')); ?>'
					},
					function(response) {
						$spinner.hide();
						if (response.success) {
							$result.html('<span style="color: green;">✅ ' + response.data.message + '</span>');
						} else {
							$result.html('<span style="color: red;">❌ ' + response.data.message + '</span>');
						}
						$btn.prop('disabled', false);
					}
				);
			});
		});
	</script>
	<?php
}

/**
 * Display callback for webhook URL.
 *
 * @since 1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object.
 */
function automatorwp_gohighlevel_webhook_url_display_cb($field_args, $field)
{
	$webhook_url = function_exists('automatorwp_gohighlevel_get_webhook_url') ? automatorwp_gohighlevel_get_webhook_url() : '';
	?>
	<div class="cmb-row cmb-type-custom cmb2-id-automatorwp-gohighlevel-webhook-url table-layout" data-fieldtype="custom">
		<div class="cmb-th">
			<label><?php echo esc_html__('Webhook URL:', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<input type="text" readonly="readonly" class="regular-text" value="<?php echo esc_attr($webhook_url); ?>" onclick="this.select();" />
			<p class="cmb2-metabox-description"><?php echo esc_html__('Use this endpoint in GoHighLevel webhooks. If Webhook Secret Token is set, send the same value in header X-Webhook-Token or as Bearer token.', AUTOMATORWP_GOHIGHLEVEL_TEXT_DOMAIN); ?></p>
		</div>
	</div>
	<?php
}
