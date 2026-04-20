<?php
/**
 * Complete Course
 *
 * @package     AutomatorWP\Integrations\FluentCommunity\Triggers\Complete_Course
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCommunity_Complete_Course extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentcommunity';
    public $trigger = 'fluentcommunity_complete_course';
    
    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {
    
        automatorwp_register_trigger($this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a course', 'automatorwp' ),
            'select_option'     => __( 'User completes a <strong>course</strong>', 'automatorwp' ),
            /* translators: %1$s: Course title. %2$s: Number of times. */
            'edit_label'        => sprintf( __('User completes %1$s %2$s time(s)', 'automatorwp' ), '{course}','{times}' ),
            /* translators: %1$s: Course title. */
            'log_label'         => sprintf( __('User completes %1$s', 'automatorwp' ), '{course}' ),
            'action'            => 'fluent_community/course/completed',
            'function'          => array($this, 'listener'),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'course' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'course',
                    'name'              => __( 'Course:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any course', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_fluentcommunity_get_courses',
                    'options_cb'        => 'automatorwp_fluentcommunity_options_cb_course',
                    'default'           => 'any'
                ) ),
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
     * @param object    $course 
     * @param int       $userId 
     */
    public function listener( $course, $userId ) {
 
        $course_data = $course->getOriginal();

        // Trigger event when user completes a course
        automatorwp_trigger_event(array(
            'trigger'   => $this->trigger,
            'user_id'   => $userId,
            'course'    => $course_data['title'],
            'course_id' => $course_data['id'],
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

        // Don't deserve if post is not received
        if( ! isset( $event['course_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( $trigger_options['course'] !== 'any' &&  absint( $trigger_options['course'] ) !== absint( $event['course_id'] ) ) {
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
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['course'] = ( isset( $event['course'] ) ? $event['course'] : array() );

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an trigger
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        // Bail if trigger type don't match this trigger
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['course'] = array(
            'name' => __( 'Course completed', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_FluentCommunity_Complete_Course();