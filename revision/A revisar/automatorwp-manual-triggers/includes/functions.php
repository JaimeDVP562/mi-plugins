<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Manual_Triggers\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Run a manual trigger by its automation trigger ID
 *
 * This function can be called from custom code to fire a manual trigger.
 * If $user_id is provided, the trigger will be run for that specific user (logged-in triggers).
 * If $user_id is omitted or 0, the trigger will be run for the current logged-in user (logged-in triggers)
 * or as an anonymous trigger.
 *
 * @since 1.0.0
 *
 * @param int $trigger_id   The automation trigger post ID
 * @param int $user_id      Optional. The user ID. Default 0 (current user or anonymous).
 */
function automatorwp_run_trigger( $trigger_id, $user_id = 0 ) {

    // Bail if AutomatorWP is not active
    if ( ! function_exists( 'automatorwp_trigger_event' ) ) {
        return;
    }

    $trigger_id = absint( $trigger_id );

    if ( $trigger_id === 0 ) {
        return;
    }

    // Try to determine if this is a logged-in or anonymous trigger
    // by checking the trigger type in the database
    $trigger = ct_get_object( $trigger_id );

    if ( ! $trigger ) {
        return;
    }

    $is_anonymous = ( $trigger->type === 'manual_triggers_anonymous_manual_launch' );

    if ( $is_anonymous ) {

        // Anonymous trigger: no user ID needed
        automatorwp_trigger_event( array(
            'trigger'    => 'manual_triggers_anonymous_manual_launch',
            'trigger_id' => $trigger_id,
        ) );

    } else {

        // Logged-in trigger: user ID is required
        if ( $user_id === 0 ) {
            $user_id = get_current_user_id();
        }

        // Bail if there is no user
        if ( $user_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'    => 'manual_triggers_manual_launch',
            'user_id'    => $user_id,
            'trigger_id' => $trigger_id,
        ) );

    }

}
