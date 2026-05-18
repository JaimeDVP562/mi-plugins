<?php
/**
 * Anonymous Successful Form Submission Trigger
 *
 * @package     AutomatorWP\Integrations\FireBox\Triggers\Anonymous_Successful_Form_Submission
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_FireBox_Anonymous_Successful_Form_Submission extends AutomatorWP_Integration_Trigger
{

    public $integration = 'firebox';
    public $trigger = 'firebox_anonymous_successful_form_submission';
    public $anonymous = true;

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_trigger($this->trigger, array(
            'integration'   => $this->integration,
            'anonymous'     => true,
            'label'         => __('Guest submits a form', 'automatorwp-firebox'),
            'select_option' => __('Guest submits <strong>a form</strong>', 'automatorwp-firebox'),
            'edit_label'    => sprintf(__('Guest submits %1$s %2$s time(s)', 'automatorwp-firebox'), '{form}', '{times}'),
            'log_label'     => sprintf(__('Guest submits %1$s', 'automatorwp-firebox'), '{form}'),
            'action'        => 'firebox/form/success',
            'function'      => array($this, 'listener'),
            'priority'      => 10,
            'accepted_args' => 3,
            'options'       => array(
                'form'  => automatorwp_utilities_ajax_selector_option(
                    array(
                        'field'             => 'form',
                        'name'              => __('Form:', 'automatorwp-firebox'),
                        'option_none_value' => 'any',
                        'option_none_label' => __('any form', 'automatorwp-firebox'),
                        'action_cb'         => 'automatorwp_firebox_get_forms',
                        'options_cb'        => 'automatorwp_firebox_options_cb_form',
                        'default'           => 'any'
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'form_field:FIELD_NAME' => array(
                        'label'   => __('Form field value', 'automatorwp-firebox'),
                        'type'    => 'text',
                        'preview' => __('Form field value, replace "FIELD_NAME" by the field name', 'automatorwp-firebox'),
                    ),
                ),
                automatorwp_utilities_times_tag()
            )
        ));

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param object|array|int $box        The campaign object or ID
     * @param array            $values     The submitted form field values
     * @param array            $submission The submission data
     */
    public function listener($box, $values, $submission)
    {

        $user_id = get_current_user_id();

        // Only for guests (not logged-in users)
        if ($user_id !== 0) {
            return;
        }

        $post_id = $this->get_post_id($box);

        if ($post_id === 0) {
            return;
        }

        $form_fields = automatorwp_firebox_get_form_fields_values($values);

        automatorwp_trigger_event(
            array(
                'trigger'     => $this->trigger,
                'post_id'     => $post_id,
                'form_fields' => $form_fields,
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
     * Anonymous deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if anonymous deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                         True if anonymous deserves trigger, false otherwise
     */
    public function anonymous_deserves_trigger($deserves_trigger, $trigger, $event, $trigger_options, $automation)
    {

        if (!isset($event['post_id'])) {
            return false;
        }

        if ($trigger_options['form'] !== 'any' && absint($event['post_id']) !== absint($trigger_options['form'])) {
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

        add_filter('automatorwp_anonymous_completed_trigger_log_meta', array($this, 'log_meta'), 10, 5);
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta($log_meta, $trigger, $event, $trigger_options, $automation)
    {

        if ($trigger->type !== $this->trigger) {
            return $log_meta;
        }

        $log_meta['post_id']     = isset($event['post_id']) ? $event['post_id'] : 0;
        $log_meta['form_fields'] = isset($event['form_fields']) ? $event['form_fields'] : array();

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

new AutomatorWP_FireBox_Anonymous_Successful_Form_Submission();
