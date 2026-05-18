<?php
/**
 * Delete Option Action
 *
 * @package     AutomatorWP\Integrations\WP_Options\Actions\Delete_Option
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_WP_Options_Delete_Option extends AutomatorWP_Integration_Action
{

    public $integration = 'wp_options';
    public $action = 'wp_options_delete_option';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
 public function register()
{
    automatorwp_register_action($this->action, array(
        'integration'   => $this->integration,
        'label'         => __('Delete a WordPress option', 'automatorwp-wp-options'),
        'select_option' => __('Delete a WordPress <strong>option</strong>', 'automatorwp-wp-options'),
        'edit_label'    => sprintf(__('Delete option %1$s', 'automatorwp-wp-options'), '{wp_option_name}'),
        'log_label'     => sprintf(__('Deleted option %1$s', 'automatorwp-wp-options'), '{wp_option_name}'),
        'options'       => array(
            'wp_option_name' => array(
                'from' => 'wp_option_name',
                'fields' => array(
                    'wp_option_name' => array(
                        'name'    => __('Option name:', 'automatorwp-wp-options'),
                        'type'    => 'text',
                        'default' => '',
                        'desc'    => __('The name of the WordPress option to delete. You can use tags.', 'automatorwp-wp-options'),
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
        $option_name = sanitize_text_field($action_options['wp_option_name']);

        // Bail if option name is empty
        if (empty($option_name)) {
            return;
        }

        // Bail if option is protected
        if (!automatorwp_wp_options_is_safe_option($option_name)) {
            return;
        }

        // Delete the option
        delete_option($option_name);
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

    add_action('automatorwp_execute_action', array($this, 'maybe_execute'), 10, 5);
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

       $log_meta['wp_option_name'] = sanitize_text_field($action_options['wp_option_name']);

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

       $log_fields['wp_option_name'] = array( 
        'name' => __('Option name', 'automatorwp-wp-options'),
        'type' => 'text',
        );

        return $log_fields;

    }
}

$delete = new AutomatorWP_WP_Options_Delete_Option();
$delete->register();



