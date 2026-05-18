<?php
/**
 * Open Popup Trigger
 *
 * @package     AutomatorWP\Integrations\FireBox\Triggers\Open_Event
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_FireBox_Show_Popup extends AutomatorWP_Integration_Trigger
{

    public $integration = 'firebox';
    public $trigger = 'firebox_show_popup';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_trigger(
            $this->trigger,
            array(
                'integration'   => $this->integration,
                'label'         => __('User opens a popup', 'automatorwp-firebox'),
                'select_option' => __('User opens <strong>a popup</strong>', 'automatorwp-firebox'),
                'edit_label'    => sprintf(__('User opens %1$s %2$s time(s)', 'automatorwp-firebox'), '{post}', '{times}'),
                'log_label'     => sprintf(__('User opens %1$s', 'automatorwp-firebox'), '{post}'),
                'action'        => array(
                    'firebox/box/on_open',
                ),
                'function'      => array($this, 'listener'),
                'priority'      => 10,
                'accepted_args' => 1,
                'options'       => array(
                    'post'  => automatorwp_utilities_post_option(
                        array(
                            'name'              => __('Popup:', 'automatorwp-firebox'),
                            'option_none_label' => __('any popup', 'automatorwp-firebox'),
                            'post_type'         => 'firebox'
                        )
                    ),
                    'times' => automatorwp_utilities_times_option(),
                ),
                'tags' => array_merge(
                    automatorwp_utilities_times_tag()
                )
            )
        );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param object|array|int $box The campaign object or ID passed by FireBox
     */
    public function listener($box)
    {

        $user_id = get_current_user_id();

        // Login is required
        if ($user_id === 0) {
            return;
        }

        $post_id = $this->get_post_id($box);

        if ($post_id === 0) {
            return;
        }

        automatorwp_trigger_event(
            array(
                'trigger' => $this->trigger,
                'user_id' => $user_id,
                'post_id' => $post_id,
            )
        );

    }

    /**
     * Extract the post ID from the $box parameter FireBox provides
     *
     * @since 1.0.0
     *
     * @param object|array|int $box
     *
     * @return int
     */
    private function get_post_id($box)
    {
        // FireBox passes the box as a WP_Post-like object, but may also be an array or a plain ID.
        if (is_object($box) && isset($box->ID)) {
            return absint($box->ID);
        }

        if (is_array($box) && isset($box['ID'])) {
            return absint($box['ID']);
        }

        if (is_numeric($box)) {
            return absint($box);
        }

        return 0;

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
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

new AutomatorWP_FireBox_Show_Popup();
