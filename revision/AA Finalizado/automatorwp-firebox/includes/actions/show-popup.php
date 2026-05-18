<?php
/**
 * Show Popup Action
 *
 * Shows a FireBox popup to the user on their next page load.
 *
 * The action stores the popup ID in a transient keyed by user ID. On the next
 * frontend page load, scripts.php reads the transient, deletes it, and passes
 * the popup ID to automatorwp-firebox.js, which opens it via the FireBox JS API.
 *
 * @package     AutomatorWP\Integrations\FireBox\Actions\Show_Popup
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_FireBox_Show_Popup_Action extends AutomatorWP_Integration_Action
{

    public $integration = 'firebox';
    public $action      = 'firebox_show_popup_action';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Show a popup to the user', 'automatorwp-firebox'),
            'select_option' => __('Show <strong>a popup</strong> to the user', 'automatorwp-firebox'),
            'edit_label'    => sprintf(__('Show %1$s to the user', 'automatorwp-firebox'), '{post}'),
            'log_label'     => sprintf(__('Show %1$s to the user', 'automatorwp-firebox'), '{post}'),
            'options'       => array(
                'post' => automatorwp_utilities_post_option(array(
                    'name'              => __('Popup:', 'automatorwp-firebox'),
                    'option_none_label' => __('any popup', 'automatorwp-firebox'),
                    'post_type'         => 'firebox',
                    'option_none'       => false,
                )),
            ),
        ));

    }

    /**
     * Action execution
     *
     * Stores the popup ID in a short-lived transient so the JS can pick it up
     * on the user's next page load.
     *
     * @since 1.0.0
     *
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options
     * @param stdClass $automation     The automation object
     */
    public function execute($action, $user_id, $action_options, $automation)
    {

        if ($user_id === 0) {
            $this->result = __('Action requires a logged-in user.', 'automatorwp-firebox');
            return;
        }

        $post_id = absint($action_options['post']);

        if ($post_id === 0) {
            $this->result = __('No popup selected.', 'automatorwp-firebox');
            return;
        }

        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'firebox') {
            $this->result = __('Popup not found.', 'automatorwp-firebox');
            return;
        }

        // Store for 10 minutes — enough time for the user to load the next page
        set_transient('automatorwp_firebox_show_popup_' . $user_id, $post_id, 10 * MINUTE_IN_SECONDS);

        $this->result = sprintf(__('Popup "%s" scheduled to show on next page load.', 'automatorwp-firebox'), $post->post_title);

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {

        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();
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
            'name' => __('Result:', 'automatorwp-firebox'),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_FireBox_Show_Popup_Action();
