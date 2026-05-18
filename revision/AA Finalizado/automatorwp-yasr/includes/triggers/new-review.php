<?php
/**
 * New Review
 *
 * @package     AutomatorWP\Integrations\YASR\Triggers\New-Review
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_YASR_New_Review extends AutomatorWP_Integration_Trigger {

    public $integration = 'yasr';
    public $trigger = 'yasr_action_on_visitor_vote';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User voted a post', 'automatorwp-yasr' ),
            'select_option'     => __( 'User voted a <strong>post</strong>', 'automatorwp-yasr' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'User voted a post %1$s time(s)', 'automatorwp-yasr' ), '{times}' ),
            /* translators: . */
            'log_label'         => sprintf( __( 'User voted a post', 'automatorwp-yasr' ),  ),
            'action'            => 'yasr_action_on_visitor_vote',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param object $data The data received from the trigger
     */
    public function listener( $data ) {

      $user_id = get_current_user_id();

      // Login is required
      if ( $user_id === 0 ) {
          return;
      }

      // Bail if there's no post ID
      if ( ! isset( $data['post_id'] ) ) {
          return;
      }

      $post_id = $data['post_id'];
      $is_singular = $data['is_singular'];

      automatorwp_trigger_event( array(
          'trigger'       => $this->trigger,
          'user_id'       => $user_id,
          'post_id'       => $post_id,
          'is_singular'   => $is_singular,
      ) );
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

        // Don't deserve if post is not received
        if( ! isset( $event['post_id'] ) ) {
            return false;
        }

        //Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
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
        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $event, $trigger_options, $automation ) {
        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['post_id'] = ( isset( $event['post_id'] ) ? $event['post_id'] : 0 );

        return $log_meta;
    }

}

new AutomatorWP_YASR_New_Review();
