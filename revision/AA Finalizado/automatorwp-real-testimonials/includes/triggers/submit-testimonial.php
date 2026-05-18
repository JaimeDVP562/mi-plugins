<?php
/**
 * Submit Testimonial
 *
 * @package     AutomatorWP\Integrations\RealTestimonial\Triggers\Submit_Testimonial
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_RealTestimonial_Submit_Testimonial extends AutomatorWP_Integration_Trigger {

    public $integration = 'RealTestimonials';
    public $trigger = 'realtestimonials_submit_testimonial';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User submits a testimonial', 'automatorwp' ),
            'select_option'     => __( 'User submits <strong>a testimonial</strong>', 'automatorwp' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User submits %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User submits %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'transition_post_status',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'post' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'post',
                    'name'              => __( 'Testimonial:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any testimonial', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_realtestimonials_get_testimonial',
                    'options_cb'        => 'automatorwp_realtestimonials_options_cb_testimonial',
                    'default'           => 'any'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'testimonial_field:FIELD_NAME' => array(
                        'label'     => __( 'Testimonial field value', 'automatorwp' ),
                        'type'      => 'text',
                        'preview'   => __( 'Testimonial field value, replace "FIELD_NAME" by the field name', 'automatorwp' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $submission_id
     * @param array $testimonial_data
     * @param \RealTestimonial\App\Modules\Testimonial\Testimonial $testimonial
     */
    public function listener( $new_status, $old_status, $post ) {

        if ( $post->post_type !== 'spt_testimonial' ) {
        return;
        }

        if ( $new_status !== 'publish' || $old_status === 'publish' ) {
            return;
        }

        $user_id = get_current_user_id();

        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        // Trigger submit testimonial event
        $fields = automatorwp_realtestimonials_get_testimonial_fields( $post_id );
        
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'testimonial_id'    => $post->ID,
            'testimonial_fields' => $fields,
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
        if( ! isset( $event['testimonial_id'] ) ) {
            return false;
        }

        $trigger_post = isset( $trigger_options['post'] ) ? $trigger_options['post'] : 'any';

        // Bail if post doesn't match with the trigger option
        if( $trigger_post !== 'any' && absint( $event['testimonial_id'] ) !== absint( $trigger_post ) ) {
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

        $log_meta['testimonial_fields'] = ( isset( $event['testimonial_fields'] ) ? $event['testimonial_fields'] : array() );

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

        $log_fields['form_fields'] = array(
            'name' => __( 'Fields Submitted', 'automatorwp' ),
            'desc' => __( 'Information about the fields values sent on this testimonial submission.', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_RealTestimonial_Submit_Testimonial();