<?php
/**
 * Trigger for YASR (Yet Another Stars Rating), changes the status of the 
 * entry after receiving a rating.
 *
 * @package     AutomatorWP\Integrations\YASR
 * @since       1.0.0
 */

class AutomatorWP_YASR_Update_Entry_Status extends AutomatorWP_Integration_Trigger {

public $integration = 'yasr';
public $trigger = 'yasr_update_entry_status';

/**
 * Register the trigger
 *
 * @since 1.0.0
 */
public function register() {

    automatorwp_register_trigger( $this->trigger, array(
        'integration'       => $this->integration,
        'label'             => __( 'Update entry status after receiving a rating', 'automatorwp-yasr' ),
        'select_option'     => __( 'Update entry status after receiving a rating', 'automatorwp-yasr' ),
        'edit_label'        => __( 'Update entry status after receiving a rating', 'automatorwp-yasr' ),
        'log_label'         => __( 'Update entry status after receiving a rating', 'automatorwp-yasr' ),
        'action'            => 'yasr_action_on_overall_rating',
        'function'          => array( $this, 'listener' ),
        'priority'          => 10,
        'accepted_args'     => 2,
        'options'           => array(
            'entry_id' => array(
                'from'     => 'entry_id',
                'default'  => 0,
                'fields'   => array(
                    'entry_id' => array(
                        'name'    => __( 'Entry ID:', 'automatorwp-yasr' ),
                        'type'    => 'text',
                        'default' => 0
                    )
                )
            ),
            'threshold' => array(
                'from'     => 'threshold',
                'default'  => 5, // Change this value according to your needs
                'fields'   => array(
                    'threshold' => array(
                        'name'    => __( 'Rating threshold:', 'automatorwp-yasr' ),
                        'type'    => 'number',
                        'default' => 3
                    )
                )
            )
        )
    ) );

}

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int    $comment_id    Comment ID
 * @param object $comment       Comment object
 */
public function listener( $comment_id, $comment ) {
    $entry_id = $this->options['entry_id'];
    $threshold = $this->options['threshold'];

    // Check if the entry has reached the rating threshold
    $ratings_count = yasr_get_post_total_rating( $entry_id );

    if ( $ratings_count >= $threshold ) {
        // Update entry status from "Draft" to "Published"
        wp_update_post( array(
            'ID'          => $entry_id,
            'post_status' => 'publish'
        ) );
    }

    // trigger the event
    automatorwp_trigger_event(array(
        'trigger'     => $this->trigger,
        'user_id'     => $comment->user_id,
        'meta'        => array(
            'comment_id' => $comment_id,
            'entry_id'   => $entry_id,
            'threshold'  => $threshold
        )
    ));

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
public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
 //check if the event has the entry_id
    if( ! isset( $event['entry_id'] ) ) {
        return false;
    }

    // check if the entry_id is the same as the one stored in the trigger options
    if( $event['entry_id'] !== (int) $trigger_options['entry_id'] ) {
        return false;
    }

    return $deserves_trigger;
}


}

new AutomatorWP_YASR_Update_Entry_Status();
