<?php
/**
 * Complete Lesson
 *
 * @package     AutomatorWP\Integrations\MemberPress\Triggers\Complete_Lesson
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Complete_Lesson extends AutomatorWP_Integration_Trigger {

    public $integration = 'memberpress';
    public $trigger = 'memberpress_complete_lesson';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a lesson', 'automatorwp-memberpress' ),
            'select_option'     => __( 'User completes <strong>a lesson</strong>', 'automatorwp-memberpress' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User completes %1$s of %2$s %3$s time(s)', 'automatorwp-memberpress' ), '{post}', '{course}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User completes %1$s of %2$s', 'automatorwp-memberpress' ), '{post}', '{course}' ),
            'action'            => 'mpcs_completed_lesson',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Lesson:', 'automatorwp-memberpress' ),
                    'option_none_label' => __( 'any lesson', 'automatorwp-memberpress' ),
                    'post_type' => 'mpcs-lesson'
                ) ),
                'course' => array(
                    'from' => 'course',
                    'default' => '',
                    'fields' => array(
                        'course' => automatorwp_utilities_post_field( array(
                            'name' => __( 'Course:', 'automatorwp-memberpress' ),
                            'option_none_label' => __( 'any course', 'automatorwp-memberpress' ),
                            'post_type' => 'mpcs-course'
                        ) )
                    )
                ),
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
     * @param memberpress\courses\models\UserProgress $user_progress
     */
    public function listener( $user_progress ) {

        $lesson_id = $user_progress->lesson_id;
        $course_id = $user_progress->course_id;
        $user_id = $user_progress->user_id;

        // Trigger the product purchase
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'post_id'       => $lesson_id,
            'course_id'     => $course_id,
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
        if( ! isset( $event['post_id'] ) && ! isset( $event['course_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        // Don't deserve if course doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['course_id'], $trigger_options['course'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_MemberPress_Complete_Lesson();