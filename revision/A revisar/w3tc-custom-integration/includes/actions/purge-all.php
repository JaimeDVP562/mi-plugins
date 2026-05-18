<?php
/**
 * Purge All Caches
 *
 * @package     AutomatorWP\Integrations\W3TC\Actions\Purge_All
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_W3TC_Purge_All extends AutomatorWP_Integration_Action
{

    public $integration = 'w3_total_cache';
    public $action = 'w3tc_purge_all';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_action(
            $this->action,
            array(
                'integration' => $this->integration,
                'label' => __('Purge all W3 Total Cache caches', 'automatorwp-w3tc'),
                'select_option' => __('Purge all <strong>W3 Total Cache</strong> caches', 'automatorwp-w3tc'),
                'edit_label' => __('Purge all W3 Total Cache caches', 'automatorwp-w3tc'),
                'log_label' => __('Purge all W3 Total Cache caches', 'automatorwp-w3tc'),
                'options' => array(),
            )
        );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute($action, $user_id, $action_options, $automation)
    {

        $this->result = '';

        // Check if W3 Total Cache is available
        if (!automatorwp_w3tc_is_available()) {
            $this->result = __('W3 Total Cache is not active or available.', 'automatorwp-w3tc');
            return;
        }

        $success = automatorwp_w3tc_purge_all();

        if ($success) {
            $this->result = __('All W3 Total Cache caches purged successfully.', 'automatorwp-w3tc');
        } else {
            $this->result = __('Failed to purge W3 Total Cache caches.', 'automatorwp-w3tc');
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {

        // Configuration notice
        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);

        // Log meta data
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);

        // Log fields
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice($object, $item_type)
    {

        // Bail if action type don't match this action
        if ($item_type !== 'action') {
            return;
        }

        if ($object->type !== $this->action) {
            return;
        }

        // Warn user if W3 Total Cache is not active
        if (!automatorwp_w3tc_is_available()): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __('You need to install and activate %s to get this action to work.', 'automatorwp-w3tc'),
                    '<a href="https://wordpress.org/plugins/w3-total-cache/" target="_blank">W3 Total Cache</a>'
                ); ?>
            </div>
        <?php endif;

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
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

        // Store the action's result
        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
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

        $log_fields['result'] = array(
            'name' => __('Result:', 'automatorwp-w3tc'),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_W3TC_Purge_All();
