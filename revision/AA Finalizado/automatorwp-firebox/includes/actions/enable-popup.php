<?php
/**
 * Enable Popup Action
 *
 * Publishes a FireBox campaign (post_status → publish).
 * Works in User, Anonymous, and All Users automations.
 *
 * @package     AutomatorWP\Integrations\FireBox\Actions\Enable_Popup
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_FireBox_Enable_Popup extends AutomatorWP_Integration_Action
{

    public $integration = 'firebox';
    public $action      = 'firebox_enable_popup';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Enable a popup', 'automatorwp-firebox'),
            'select_option' => __('Enable <strong>a popup</strong>', 'automatorwp-firebox'),
            'edit_label'    => sprintf(__('Enable %1$s', 'automatorwp-firebox'), '{post}'),
            'log_label'     => sprintf(__('Enable %1$s', 'automatorwp-firebox'), '{post}'),
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
     * @since 1.0.0
     *
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options
     * @param stdClass $automation     The automation object
     */
    public function execute($action, $user_id, $action_options, $automation)
    {

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

        if ($post->post_status === 'publish') {
            $this->result = sprintf(__('Popup "%s" was already enabled.', 'automatorwp-firebox'), $post->post_title);
            return;
        }

        wp_update_post(array(
            'ID'          => $post_id,
            'post_status' => 'publish',
        ));

        $this->result = sprintf(__('Popup "%s" enabled.', 'automatorwp-firebox'), $post->post_title);

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

new AutomatorWP_FireBox_Enable_Popup();
