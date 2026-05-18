<?php
/**
 * Manual Launch (Logged-in)
 *
 * @package     AutomatorWP\Integrations\Manual_Triggers\Triggers\Manual_Launch
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_Manual_Triggers_Manual_Launch extends AutomatorWP_Integration_Trigger
{

    public $integration = 'manual_triggers';
    public $trigger = 'manual_triggers_manual_launch';

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
                'integration'       => $this->integration,
                'label'             => __( 'Manual launch (logged-in user)', 'automatorwp-manual-triggers' ),
                'select_option'     => __( 'User triggers a <strong>manual launch</strong>', 'automatorwp-manual-triggers' ),
                'edit_label'        => sprintf( __( 'User triggers a manual launch %1$s time(s)', 'automatorwp-manual-triggers' ), '(times)' ),
                'log_label'         => __( 'User triggers a manual launch', 'automatorwp-manual-triggers' ),
                'action'            => 'automatorwp_manual_triggers_manual_launch',
                'function'          => array( $this, 'listener' ),
                'priority'          => 10,
                'accepted_args'     => 2,
                'options'           => array(
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
     * @param int $trigger_id   The trigger post ID
     * @param int $user_id      The user ID
     */
    public function listener( $trigger_id, $user_id )
    {

        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        // Trigger event
        automatorwp_trigger_event(
            array(
                'trigger'    => $this->trigger,
                'user_id'    => $user_id,
                'trigger_id' => $trigger_id,
            )
        );

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

        // Don't deserve if trigger_id is not received
        if (!isset($event['trigger_id'])) {
            return false;
        }

        // Only fire for the specific trigger that was manually launched
        if ( absint( $event['trigger_id'] ) !== absint( $trigger->id ) ) {
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

        // Log meta data
        add_filter('automatorwp_user_completed_trigger_log_meta', array($this, 'log_meta'), 10, 6);

        // Log fields
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array $log_meta
     * @param stdClass $trigger
     * @param int $user_id
     * @param array $event
     * @param array $trigger_options
     * @param stdClass $automation
     *
     * @return array
     */
    function log_meta($log_meta, $trigger, $user_id, $event, $trigger_options, $automation)
    {

        // Bail if action type don't match this action
        if ($trigger->type !== $this->trigger) {
            return $log_meta;
        }

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array $log_fields
     * @param stdClass $log
     * @param stdClass $object
     *
     * @return array
     */
    public function log_fields($log_fields, $log, $object)
    {

        // Bail if log is not assigned to a trigger
        if ($log->type !== 'trigger') {
            return $log_fields;
        }

        // Bail if trigger type don't match this trigger
        if ($object->type !== $this->trigger) {
            return $log_fields;
        }

        return $log_fields;

    }

}

new AutomatorWP_Manual_Triggers_Manual_Launch();
