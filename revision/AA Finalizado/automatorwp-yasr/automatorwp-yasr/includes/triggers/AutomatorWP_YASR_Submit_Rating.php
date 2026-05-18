<?php
/**

 * @package     AutomatorWP\Integrations\YASR
 * @since       1.0.0
 */

 
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_YASR_Submit_Rating extends AutomatorWP_Integration_Trigger {

public $integration = 'yasr';
public $trigger = 'yasr_submit_rating';

/**
 * Register the trigger
 *
 * @since 1.0.0
 * @param int    $post_id    Comment ID
 * @param object $post      Comment object
 */

public function register() {

automatorwp_register_trigger($this->trigger, array(
    'integration'   => $this->integration,
    'label'         => __('User submits a rating in a post without email', 'automatorwp-yasr'),
    'select_option' => __('User submits a rating in a post without email', 'automatorwp-yasr'),
    /* translators: %1$s: Post title. */
    'edit_label'        => sprintf( __( 'Guest submits %1$s', 'automatorwp-yasr' ), '{post}' ),
    /* translators: %1$s: Post title. */
    'log_label'         => sprintf( __( 'Guest submits %1$s', 'automatorwp-yasr' ), '{post}' ),
    'action'        => 'yasr_action_on_overall_rating',
    'function'      => array($this, 'listener'),
    'priority'      => 10,
    'accepted_args' => 2,
    'options'       => array(
         'post' => array(
            'type' => 'post',
           'label' => __('Select a post', 'automatorwp-yasr'),
            'is_ajax' => true,
            'is_multiple' => false,
            'required' => true,
             'custom_value_description' => __('Post ID', 'automatorwp-yasr'),
         ),
        'tags' => array_merge(
            automatorwp_utilities_post_tags(),
            automatorwp_utilities_times_tag()
        )

    ),

));

}

/**
 * Fires on rating
 *
 * @param $rating
 * @param $post_id
 */

public function listener($rating, $post_id) {
    
        $post = get_post($post_id);
    
        if ( ! $post ) {
            return;
        }
    
        $post_url = get_permalink($post_id);
    
        wp_redirect($post_url);

        // Trigger the event
        automatorwp_trigger_event(array(
            'trigger'     => $this->trigger,
            'user_id'     => $post_id,
            'rating'      => $rating,
   
        ));


        exit;

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

    return true;

}



}