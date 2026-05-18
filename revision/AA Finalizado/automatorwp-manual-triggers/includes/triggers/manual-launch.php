<?php
/**
 * Manual Launch (Logged-in)
 *
 * @package     AutomatorWP\Integrations\Manual_Triggers\Triggers\Manual_Launch
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Manual_Triggers_Manual_Launch extends AutomatorWP_Integration_Trigger {

    public $integration = 'manual_triggers';
    public $trigger = 'manual_triggers_manual_launch';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'Manual launch (logged-in user)', 'automatorwp-manual-triggers' ),
            'select_option'     => __( 'User triggers a <strong>manual launch</strong>', 'automatorwp-manual-triggers' ),
            'edit_label'        => sprintf( __( 'User triggers a manual launch %1$s time(s)', 'automatorwp-manual-triggers' ), '{times}' ),
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
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $trigger_id   The trigger post ID
     * @param int $user_id      The user ID
     */
    public function listener( $trigger_id, $user_id ) {

        if ( $user_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'    => $this->trigger,
            'user_id'    => $user_id,
            'trigger_id' => $trigger_id,
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger
     * @param stdClass  $trigger
     * @param int       $user_id
     * @param array     $event
     * @param array     $trigger_options
     * @param stdClass  $automation
     *
     * @return bool
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        if( !isset( $event['trigger_id'] ) ) {
            return false;
        }

        if( absint( $event['trigger_id'] ) !== absint( $trigger->id ) ) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        parent::hooks();

    }

}

new AutomatorWP_Manual_Triggers_Manual_Launch();