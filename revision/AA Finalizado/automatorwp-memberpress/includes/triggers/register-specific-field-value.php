<?php
/**
 * Register Specific Field Value
 *
 * @package     AutomatorWP\Integrations\MemberPress\Triggers\Register_Specific_Field_Value
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Register_Specific_Field_Value extends AutomatorWP_Integration_Trigger {

    public $integration = 'memberpress';
    public $trigger = 'memberpress_register_specific_field_value';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User registers with a specific field value', 'automatorwp-memberpress' ),
            'select_option'     => __( 'User registers with a <strong>specific field value</strong>', 'automatorwp-memberpress' ),
            /* translators: %1$s: Field value. %2$s: Field ID or name. %3$s: Number of times. */
            'edit_label'        => sprintf( __( 'User registers with the %1$s on the field %2$s %3$s time(s)', 'automatorwp-memberpress' ), '{field_value}', '{field_id}', '{times}' ),
            /* translators: %1$s: Field value. %2$s: Field ID or name. */
            'log_label'         => sprintf( __( 'User registers with the %1$s on the field %2$s', 'automatorwp-memberpress' ), '{field_value}', '{field_id}' ),
            'action'            => 'mepr-signup',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'field_value' => array(
                    'from' => 'field_value',
                    'default' => __( 'field value', 'automatorwp-memberpress' ),
                    'fields' => array(
                        'field_value' => array(
                            'name' => __( 'Field value:', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'default' => ''
                        )
                    )
                ),
                'field_id' => array(
                    'from' => 'field_id',
                    'default' => __( 'field name', 'automatorwp-memberpress' ),
                    'fields' => array(
                        'field_id' => array(
                            'name' => __( 'Field slug:', 'automatorwp-memberpress' ),
                            'desc' => __( 'The field slug. Field slug can be found on the MemberPress settings, under the "Fields" tab.', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'default' => ''
                        )
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param MeprTransaction $txn
     */
    public function listener( $txn ) {

        $user_id = $txn->user_id;

        $mepr_options = MeprOptions::fetch();

        $custom_fields = $mepr_options->custom_fields;

        // Loop all updated fields
        foreach( $custom_fields as $field ) {

            $field_value = get_user_meta( $user_id, $field->field_key, true );

            if( empty( $field_value ) ) {
                continue;
            }

            // Trigger the register with field value
            automatorwp_trigger_event( array(
                'trigger'       => $this->trigger,
                'user_id'       => $user_id,
                'field_id'      => $field->field_key,
                'field_value'   => $field_value,
            ) );

        }

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

        // Don't deserve if field id and value are not received
        if( ! isset( $event['field_id'] ) && ! isset( $event['field_value'] ) ) {
            return false;
        }

        // Don't deserve if field name doesn't match with the trigger option
        if( $event['field_id'] !== $trigger_options['field_id'] ) {
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

        $log_meta['field_id'] = ( isset( $event['field_id'] ) ? $event['field_id'] : '' );
        $log_meta['field_value'] = ( isset( $event['field_value'] ) ? $event['field_value'] : '' );

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

        $log_fields['field_id'] = array(
            'name' => __( 'Field slug', 'automatorwp-memberpress' ),
            'desc' => __( 'The field slug handled on registration.', 'automatorwp-memberpress' ),
            'type' => 'text',
        );

        $log_fields['field_value'] = array(
            'name' => __( 'Field value', 'automatorwp-memberpress' ),
            'desc' => __( 'The field value handled on registration.', 'automatorwp-memberpress' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_MemberPress_Register_Specific_Field_Value();