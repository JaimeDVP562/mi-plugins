<?php
/**
 * Update Option Action
 *
 * @package     AutomatorWP\Integrations\WP_Options\Actions\Update_Option
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_WP_Options_Update_Option extends AutomatorWP_Integration_Action
{

    public $integration = 'wp_options';
    public $action = 'wp_options_update_option';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Update a WordPress option', 'automatorwp-wp-options'),
            'select_option' => __('Update a WordPress <strong>option</strong>', 'automatorwp-wp-options'),
            'edit_label'    => sprintf(__('Update option %1$s to %2$s', 'automatorwp-wp-options'), '{option_name}', '{option_value}'),
            'log_label'     => sprintf(__('Updated option %1$s to %2$s', 'automatorwp-wp-options'), '{option_name}', '{option_value}'),
            'options'       => array(
                'option_name' => array(
                    'from' => 'option_name',
                    'fields' => array(
                        'option_name' => array(
                            'name' => __('Option name:', 'automatorwp-wp-options'),
                            'type' => 'text',
                            'default' => '',
                            'desc' => __('The name of the WordPress option to update (e.g. blogname, blogdescription, my_custom_option). You can use tags.', 'automatorwp-wp-options'),
                        ),
                    ),
                ),
                'option_value' => array(
                    'from' => 'option_value',
                    'fields' => array(
                        'option_value' => array(
                            'name' => __('Option value:', 'automatorwp-wp-options'),
                            'type' => 'text',
                            'default' => '',
                            'desc' => __('The new value for the option. You can use tags like {user_email}, {user_id}, etc.', 'automatorwp-wp-options'),
                        ),
                    ),
                ),
                'autoload' => array(
                    'from' => 'autoload',
                    'fields' => array(
                        'autoload' => array(
                            'name' => __('Autoload:', 'automatorwp-wp-options'),
                            'type' => 'select',
                            'options' => array(
                                'yes' => __('Yes', 'automatorwp-wp-options'),
                                'no'  => __('No', 'automatorwp-wp-options'),
                            ),
                            'default' => 'yes',
                            'desc' => __('Whether to autoload this option on every page load. Use "No" for rarely used options.', 'automatorwp-wp-options'),
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     */
    public function execute($action, $user_id, $action_options, $automation)
    {
        // Get and sanitize option name
        $option_name = sanitize_text_field($action_options['option_name']);

        // Bail if option name is empty
        if (empty($option_name)) {
            return;
        }

        // Bail if option is protected
        if (!automatorwp_wp_options_is_safe_option($option_name)) {
            return;
        }

        // Get and sanitize option value
        $option_value = wp_kses_post($action_options['option_value']);

        // Get autoload setting
        $autoload = isset($action_options['autoload'])
            ? automatorwp_wp_options_sanitize_autoload($action_options['autoload'])
            : 'yes';

        // Update the option
        update_option($option_name, $option_value, $autoload);
    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {

        // Log meta data
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);

        // Log fields
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();
    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {

        // Bail if action type don't match this action
        if ($action->type !== $this->action) {
            return $log_meta;
        }

        $log_meta['option_name']  = sanitize_text_field($action_options['option_name']);
        $log_meta['option_value'] = wp_kses_post($action_options['option_value']);
        $log_meta['autoload']     = isset($action_options['autoload']) ? $action_options['autoload'] : 'yes';

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields     The log fields
     * @param stdClass  $log            The log object
     * @param stdClass  $object         The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields($log_fields, $log, $object)
    {

        // Bail if log is not assigned to an action
        if ($log->type !== 'action') {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if ($object->type !== $this->action) {
            return $log_fields;
        }

        $log_fields['option_name'] = array(
            'name' => __('Option name', 'automatorwp-wp-options'),
            'type' => 'text',
        );

        $log_fields['option_value'] = array(
            'name' => __('Option value', 'automatorwp-wp-options'),
            'type' => 'text',
        );

        $log_fields['autoload'] = array(
            'name' => __('Autoload', 'automatorwp-wp-options'),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_WP_Options_Update_Option();
