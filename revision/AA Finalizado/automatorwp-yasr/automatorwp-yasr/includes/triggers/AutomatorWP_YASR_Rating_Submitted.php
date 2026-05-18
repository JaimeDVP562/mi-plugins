<?php
/**
 * Trigger for YASR (Yet Another Stars Rating), sends an email to the user
 * after submitting a rating.
 * 
 * @package     AutomatorWP\Integrations\YASR
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_YASR_Rating_Submitted extends AutomatorWP_Integration_Trigger {

    public $integration = 'yasr';
    public $trigger = 'yasr_rating_submitted';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger($this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __('User submits a rating in a post', 'automatorwp-yasr'),
            'select_option' => __('User submits a rating in a post', 'automatorwp-yasr'),
            /* translators: %1$s: Post title. */
            'edit_label'        => sprintf( __( 'Guest submits %1$s', 'automatorwp-yasr' ), '{post}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'Guest submits %1$s', 'automatorwp-yasr' ), '{post}' ),
            'edit_label2'        => sprintf( __( 'Minimun rating %1$s', 'automatorwp-yasr' ), '{min_rating}' ),
            'log_label2'         => sprintf( __( 'Minimum rating %1$s', 'automatorwp-yasr' ), '{min_rating}' ),
            'action'        => 'yasr_action_on_overall_rating',
            'function'      => array($this, 'listener'),
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(
                'post'=>  automatorwp_utilities_post_option(),
                'min_rating' => array(
                    'from'     => 'min_rating',
                    'default'  => 0,
                    'fields'   => array(
                        'min_rating' => array(
                            'name'    => __('Minimum rating:', 'automatorwp-yasr'),
                            'type'    => 'number',
                            'default' => 0
                        )
                    )
                        ),
             
            )
        ));
    }

    /**
     * Listener for the trigger
     *
     * @since 1.0.0
     *
     * @param int    $comment_id    Comment ID
     * @param object $comment       Comment object
     */
    public function listener($comment_id, $comment) {
        $user_id = $comment->user_id;

        // If the user is not logged in, exit
        if (!$user_id) {
            return;
        }

        //get rating
        $rating = get_comment_meta($comment_id, 'yasr_overall_rating', true);

        // If the rating is not greater than or equal to the required minimum, exit

        
        // Get the trigger options
        error_log("puntuación: " . $rating);

        // Trigger the event
        automatorwp_trigger_event(array(
            'trigger'     => $this->trigger,
            'user_id'     => $user_id,
            'rating'      => $rating,
            'comment_id'  => $comment_id
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
    // check if the user has the required minimum rating
    if( ! isset( $event['rating'] ) ) {
        return false;
    }

    // Get and verify the minimum rating
    $min_rating = $trigger_options['min_rating'];

    if( $event['rating'] < $min_rating ) {
       
        return false;
    }


    return $deserves_trigger;
}

}

new AutomatorWP_YASR_Rating_Submitted();
