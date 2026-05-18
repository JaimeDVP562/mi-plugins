<?php
/**
 * Purge URL Cache
 *
 * @package     AutomatorWP\Integrations\W3TC\Actions\Purge_URL
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_W3TC_Purge_URL extends AutomatorWP_Integration_Action
{

    public $integration = 'w3_total_cache';
    public $action = 'w3tc_purge_url';

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
                'label' => __('Purge W3 Total Cache for a specific URL', 'automatorwp-w3tc'),
                'select_option' => __('Purge <strong>W3 Total Cache</strong> for a specific <strong>URL</strong>', 'automatorwp-w3tc'),
                'edit_label' => sprintf(__('Purge W3 Total Cache for URL %1$s', 'automatorwp-w3tc'), '{url}'),
                'log_label' => sprintf(__('Purge W3 Total Cache for URL %1$s', 'automatorwp-w3tc'), '{url}'),
                'options' => array(
                    'url' => array(
                        'from' => 'url',
                        'default' => __('URL', 'automatorwp-w3tc'),
                        'fields' => array(
                            'url' => array(
                                'name' => __('URL:', 'automatorwp-w3tc'),
                                'desc' => __('The full URL to purge from cache (e.g. https://example.com/my-page/). You can use tags to set the URL dynamically.', 'automatorwp-w3tc'),
                                'type' => 'text',
                                'required' => true,
                                'default' => '',
                            ),
                        ),
                    ),
                ),
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

        // Fallback to action options if needed
        if (empty((array)$action_options) && !empty($action->options)) {
            $action_options = (array) $action->options;
        }

        $url = isset($action_options['url']) ? $action_options['url'] : '';

        $this->result = '';

        // Check if W3 Total Cache is available
        if (!automatorwp_w3tc_is_available()) {
            $this->result = __('W3 Total Cache is not active or available.', 'automatorwp-w3tc');
            return;
        }

        // Validate URL
        $url = trim($url);

        if (empty($url)) {
            $this->result = __('URL field is empty.', 'automatorwp-w3tc');
            return;
        }

        $success = automatorwp_w3tc_purge_url($url);

        if ($success) {
            $this->result = sprintf(
                __('W3 Total Cache purged for URL: %s', 'automatorwp-w3tc'),
                $url
            );
        } else {
            $this->result = sprintf(__('Failed to purge W3 Total Cache for URL: %s', 'automatorwp-w3tc'), $url);
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {

        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
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

        if ($item_type !== 'action') {
            return;
        }

        if ($object->type !== $this->action) {
            return;
        }

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
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {

        if ($action->type !== $this->action) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields($log_fields, $log, $object)
    {

        if ($log->type !== 'action') {
            return $log_fields;
        }

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

new AutomatorWP_W3TC_Purge_URL();
