<?php
/**
 * Anonymous Manual Launch
 *
 * @package     AutomatorWP\Integrations\Manual_Triggers\Triggers\Anonymous_Manual_Launch
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_Manual_Triggers_Anonymous_Manual_Launch extends AutomatorWP_Integration_Trigger
{

    public $integration = 'manual_triggers';
    public $trigger = 'manual_triggers_anonymous_manual_launch';
    public $anonymous = true;

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_trigger($this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Manual launch (anonymous)', 'automatorwp-manual-triggers' ),
            'select_option'     => __( 'Guest triggers a <strong>manual launch</strong>', 'automatorwp-manual-triggers' ),
            'edit_label'        => sprintf( __( 'Guest triggers a manual launch %1$s time(s)', 'automatorwp-manual-triggers' ), '(times)' ),
            'log_label'         => __( 'Guest triggers a manual launch', 'automatorwp-manual-triggers' ),
            'action'            => 'automatorwp_manual_triggers_anonymous_manual_launch',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_times_tag()
            )
        ));

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $trigger_id   The trigger post ID
     */
    public function listener( $trigger_id )
    {

        // Trigger event (no user ID for anonymous)
        automatorwp_trigger_event(
            array(
                'trigger'    => $this->trigger,
                'trigger_id' => $trigger_id,
            )
        );

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
        add_filter('automatorwp_anonymous_completed_trigger_log_meta', array($this, 'log_meta'), 10, 5);

        // Log fields
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();
    }

}

new AutomatorWP_Manual_Triggers_Anonymous_Manual_Launch();
