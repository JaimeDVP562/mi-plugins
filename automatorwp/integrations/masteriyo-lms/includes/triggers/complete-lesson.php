<?php
/**
 * Complete Lesson
 *
 * @package     AutomatorWP\Integrations\Masteriyo_LMS\Triggers\Complete_Lesson
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Masteriyo_LMS_Complete_Lesson extends AutomatorWP_Integration_Trigger {

    public $integration = 'masteriyo_lms';
    public $trigger = 'masteriyo_lms_complete_lesson';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a lesson', 'automatorwp' ),
            'select_option'     => __( 'User completes <strong>a lesson</strong>', 'automatorwp' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User completes %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User completes %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'masteriyo_new_course_progress_item',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Lesson:', 'automatorwp' ),
                    'option_none_label' => __( 'any lesson', 'automatorwp' ),
                    'post_type' => 'mto-lesson',
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param integer $progress_id The new course progress item ID.
	 * @param \Masteriyo\Models\CourseProgressItem $object The new course progress item object.
     */
    public function listener( $progress_id, $progress_item ) {

        if ( 'lesson' !== $progress_item->get_item_type() || ! $progress_item->get_completed() ) {
			return;
		}

		$lesson = masteriyo_get_lesson( $progress_item->get_item_id() );

		if ( is_null( $lesson ) ) {
			return;
		}

		$user_id      = $progress_item->get_user_id();
		$lesson_id    = $lesson->get_id();
		$course_id    = $progress_item->get_course_id();

        automatorwp_trigger_event( array(
            'trigger'   => $this->trigger,
            'user_id'   => $user_id,
            'post_id'   => $lesson_id,
            'course_id' => $course_id,
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

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_Masteriyo_LMS_Complete_Lesson();