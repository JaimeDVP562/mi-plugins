<?php
/**
 * Trigger: Complete a Course
 *
 * @package     AutomatorWP\Masteriyo_LMS\Triggers\Complete_Course
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Masteriyo_Complete_Course extends AutomatorWP_Integration_Trigger {

    public $integration = 'masteriyo_lms';
    public $trigger = 'masteriyo_lms_complete_course';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a course', 'automatorwp' ),
            'select_option'     => __( 'User completes a <strong>course</strong>', 'automatorwp' ),
            //* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User completes %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User completes %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'masteriyo_course_progress_status_completed', 
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Course:', 'automatorwp-tutor' ),
                    'option_none_label' => __( 'any course', 'automatorwp-tutor' ),
                    'post_type' => 'mto-course'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
        ) );
    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $course_progress_id The course progress ID.
     * @param \Masteriyo\Models\CourseProgress $course_progress The course progress object.
     */
    public function listener( $course_progress_id, $course_progress ) {

        $user_id = $course_progress->get_user_id();
        $course_id = $course_progress->get_course_id();

        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'post_id'       => $course_id,
            'progress_id'   => $course_progress_id,
        ) );
    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise.
     * @param stdClass  $trigger            The trigger object.
     * @param int       $user_id            The user ID.
     * @param array     $event              The event data.
     * @param array     $trigger_options    The trigger options.
     * @param stdClass  $automation         The trigger's automation object.
     *
     * @return bool True if the user deserves the trigger, false otherwise.
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

new AutomatorWP_Masteriyo_Complete_Course();
