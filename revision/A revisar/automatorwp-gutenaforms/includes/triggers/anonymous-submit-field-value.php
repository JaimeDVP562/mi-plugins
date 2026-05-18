<?php
/**
 * Anonymous Submit Field Value
 *
 * @package     AutomatorWP\Integrations\GutenaForms\Triggers\Anonymous_Submit_Field_Value
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_GutenaForms_Anonymous_Submit_Field_Value extends AutomatorWP_Integration_Trigger {

    public $integration = 'gutenaforms';
    public $trigger = 'gutenaforms_anonymous_submit_field_value';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Guest submits a specific field value on a form', 'automatorwp-gutenaforms' ),
            'select_option'     => __( 'Guest submits <strong>a specific field value</strong> on a form', 'automatorwp-gutenaforms' ),
            /* translators: %1$s: Field name. %2$s: Field value. %3$s: Post title. */
            'edit_label'        => sprintf( __( 'Guest submits the %1$s field with the value %2$s on %3$s', 'automatorwp-gutenaforms' ), '{field_name}', '{field_value}', '{post}' ),
            /* translators: %1$s: Field name. %2$s: Field value. %3$s: Post title. */
            'log_label'         => sprintf( __( 'Guest submits the %1$s field with the value %2$s on %3$s', 'automatorwp-gutenaforms' ), '{field_name}', '{field_value}', '{post}' ),
            'action'            => 'gutena_forms_submission',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'field_name' => array(
                    'from' => 'field_name',
                    'default' => __( 'field name', 'automatorwp-gutenaforms' ),
                    'fields' => array(
                        'field_name' => array(
                            'name' => __( 'Field name:', 'automatorwp-gutenaforms' ),
                            'type' => 'text',
                            'default' => ''
                        )
                    )
                ),
                'field_value' => array(
                    'from' => 'field_value',
                    'default' => __( 'field value', 'automatorwp-gutenaforms' ),
                    'fields' => array(
                        'field_value' => array(
                            'name' => __( 'Field value:', 'automatorwp-gutenaforms' ),
                            'type' => 'text',
                            'default' => ''
                        )
                    )
                ),
                'post' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'post',
                    'name'              => __( 'Form:', 'automatorwp-gutenaforms' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any form', 'automatorwp-gutenaforms' ),
                    'action_cb'         => 'automatorwp_gutenaforms_get_forms',
                    'options_cb'        => 'automatorwp_gutenaforms_options_cb_form',
                    'default'           => 'any'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'form_field:FIELD_ID' => array(
                        'label'     => __( 'Form field value', 'automatorwp-gutenaforms' ),
                        'type'      => 'text',
                        'preview'   => __( 'Form field value, replace "FIELD_ID" by the field ID', 'automatorwp-gutenaforms' ),
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
     * @param array $form_args
     * @param array $fields
     * @param string $form_id
     * @param string $block_form_id
     * @param int $post_id
     */
    public function listener( $form_submit_data, $formSchema ) {

        //$user_id = get_current_user_id();

        $formID = $form_submit_data['formID'];
        $form_id = strtolower($formID); 

        $user_id = get_current_user_id();

        $form_fields = $form_submit_data['submit_data'];

        // Bail if user is logged in
        if ( $user_id !== 0 ) {
            return;
        }

        // Loop all fields to trigger events per field value
        foreach ( $form_fields as $field_name => $field_value ) {

            // Used for hook
            $field = array( $field_name => $field_value );

            /**
             * Excluded fields event by filter
             *
             * @since 1.0.0
             *
             * @param bool      $exclude        Whatever to exclude or not, by default false
             * @param string    $field_name     Field name
             * @param mixed     $field_value    Field value
             * @param array     $field          Field setup array
             */
            if( apply_filters( 'automatorwp_gutenaforms_exclude_field', false, $field_name, $field_value, $field ) ) {
                continue;
            }

            // Trigger submit form field value event
            automatorwp_trigger_event( array(
                'trigger'       => $this->trigger,
                'form_id'       => $form_id,
                'field_name'    => $field_name,               
                'field_value'   => $field_value,
                'form_fields'   => $form_fields,
            ) );
        }

    }

    /**
     * Anonymous deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if anonymous deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                         True if anonymous deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $event, $trigger_options, $automation ) {
    	
        // Don't deserve if post, field name and value are not received
        if( ! isset( $event['form_id'] ) && ! isset( $event['field_name'] ) && ! isset( $event['field_value'] ) ) {
            return false;
        }

        // Bail if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $event['form_id'] ) !== absint( $trigger_options['post'] ) ) {
            return false;
        }

        // Don't deserve if field name doesn't match with the trigger option
        if( $event['field_name'] !== $trigger_options['field_name'] ) {
            return false;
        }

        // Check if field value matches the required one (with support for arrays)
        if( is_array( $event['field_value'] ) ) {

            if( ! in_array( $trigger_options['field_value'], $event['field_value'] ) ) {
                return false;
            }

        } else {

            if( $event['field_value'] !== $trigger_options['field_value'] ) {
                return false;
            }

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
            'name' => __( 'Fields Submitted', 'automatorwp-gutenaforms' ),
            'desc' => __( 'Information about the fields values sent on this form submission.', 'automatorwp-gutenaforms' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_GutenaForms_Anonymous_Submit_Field_Value();