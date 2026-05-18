<?php
/**
 * Conversion Trigger (logged-in user)
 *
 * Fires when a logged-in user clicks a tracked Button or Image block inside a
 * FireBox popup. The event is captured in JS and relayed to PHP via AJAX.
 *
 * @package     AutomatorWP\Integrations\FireBox\Triggers\Conversion
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_FireBox_Conversion extends AutomatorWP_Integration_Trigger
{

    public $integration = 'firebox';
    public $trigger     = 'firebox_conversion';

    /**
     * Register the trigger
     *
     * No 'action' key is set because this trigger is fired programmatically
     * from the AJAX handler (automatorwp_firebox_ajax_conversion) instead of
     * hooking a WordPress action directly.
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_trigger($this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __('User clicks a conversion element in a popup', 'automatorwp-firebox'),
            'select_option' => __('User clicks a <strong>conversion element</strong> in a popup', 'automatorwp-firebox'),
            'edit_label'    => sprintf(__('User clicks a conversion element in %1$s %2$s time(s)', 'automatorwp-firebox'), '{post}', '{times}'),
            'log_label'     => sprintf(__('User clicks a conversion element in %1$s', 'automatorwp-firebox'), '{post}'),
            'action'        => '',
            'function'      => '',
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'post'  => automatorwp_utilities_post_option(array(
                    'name'              => __('Popup:', 'automatorwp-firebox'),
                    'option_none_label' => __('any popup', 'automatorwp-firebox'),
                    'post_type'         => 'firebox',
                )),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_times_tag()
            ),
        ));

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     */
    public function user_deserves_trigger($deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation)
    {

        if (!isset($event['post_id'])) {
            return false;
        }

        if ($trigger_options['post'] !== 'any' && absint($event['post_id']) !== absint($trigger_options['post'])) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {

        add_filter('automatorwp_user_completed_trigger_log_meta', array($this, 'log_meta'), 10, 6);
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta($log_meta, $trigger, $user_id, $event, $trigger_options, $automation)
    {

        if ($trigger->type !== $this->trigger) {
            return $log_meta;
        }

        $log_meta['post_id'] = isset($event['post_id']) ? $event['post_id'] : 0;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields($log_fields, $log, $object)
    {

        if ($log->type !== 'trigger') {
            return $log_fields;
        }

        if ($object->type !== $this->trigger) {
            return $log_fields;
        }

        $log_fields['post_id'] = array(
            'name' => __('Popup:', 'automatorwp-firebox'),
            'type' => 'post_link',
        );

        return $log_fields;

    }

}

new AutomatorWP_FireBox_Conversion();
