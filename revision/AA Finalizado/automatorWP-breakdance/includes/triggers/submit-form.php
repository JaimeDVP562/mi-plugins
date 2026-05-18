<?php
/**
 * Submit Form
 *
 * @package     AutomatorWP\Integrations\Breakdance\Triggers\Submit_Form
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class AutomatorWP_Breakdance_Submit_Form extends AutomatorWP_Integration_Trigger {

    public $integration = 'breakdance';
    public $trigger = 'breakdance_submit_form';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User submits a form', 'automatorwp' ),
            'select_option'     => __( 'User submits <strong>a form</strong>', 'automatorwp' ),
            /* translators: %1$s: Form name. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User submits %1$s %2$s time(s)', 'automatorwp' ), '{form_name}', '{times}' ),
            /* translators: %1$s: Form name. */
            'log_label'         => sprintf( __( 'User submits %1$s', 'automatorwp' ), '{form_name}' ),
            'action'            => 'breakdance_form_end',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'form_name' => array(
                    'from' => 'form_name',
                    'default' => __( 'any form', 'automatorwp' ),
                    'fields' => array(
                        'form_name' => array(
                            'name' => __( 'Form name:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        )
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'form_field:FIELD_ID' => array(
                        'label'     => __( 'Form field value', 'automatorwp' ),
                        'type'      => 'text',
                        'preview'   => __( 'Form field value, replace "FIELD_ID" by the field id', 'automatorwp' ),
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
     * @param array $form
     */
    public function listener( $form ) {

        // Verify nonce if present (security check)
        if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( $_POST['_wpnonce'], 'breakdance_form' ) ) {
            return;
        }

        $form_name = isset( $form['settings']['form']['name'] ) ? $form['settings']['form']['name'] : '';
        $user_id = get_current_user_id();

        // For anonymous forms, allow guest submissions
        if ( $user_id === 0 ) {
            // Check if anonymous trigger exists
            $anonymous_trigger = 'breakdance_anonymous_submit_form';
            if ( automatorwp_get_trigger( $anonymous_trigger ) ) {
                $form_fields = automatorwp_breakdance_get_form_fields_values( $_POST );
                automatorwp_trigger_event( array(
                    'trigger'       => $anonymous_trigger,
                    'user_id'       => $user_id,
                    'form_name'     => $form_name,
                    'form_fields'   => $form_fields,
                ) );
            }
            return;
        }

        $form_fields = automatorwp_breakdance_get_form_fields_values( $_POST );

        // Trigger submit form event
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'form_name'     => $form_name,
            'form_fields'   => $form_fields,
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

        // Don't deserve if form name doesn't match with the trigger option
        if( ! empty( $trigger_options['form_name'] ) && ( ! isset( $event['form_name'] ) || $event['form_name'] !== $trigger_options['form_name'] ) ) {
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

        $log_meta['form_fields'] = ( isset( $event['form_fields'] ) ? $event['form_fields'] : array() );

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
            'desc' => __( 'Information about the fields values sent on this form submission.', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_Breakdance_Submit_Form();