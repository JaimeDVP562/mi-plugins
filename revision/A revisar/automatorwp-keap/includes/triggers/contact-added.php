<?php
/**
 * Contact Added Trigger
 *
 * @package     AutomatorWP\Integrations\Keap\Triggers\Contact-Added
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.1.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Keap_Contact_Added extends AutomatorWP_Integration_Trigger {

    public $integration = 'keap';
    public $trigger = 'keap_contact_added';

    /**
     * Register the trigger
     *
     * @since 1.1.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'Contact added', 'automatorwp-keap' ),
            'select_option'     => __( 'A new <strong>contact</strong> is added', 'automatorwp-keap' ),
            'edit_label'        => __( 'A new contact is added with email {email}', 'automatorwp-keap' ),
            'log_label'         => __( 'New contact added with email {email}', 'automatorwp-keap' ),
            'options'           => array(
                'email' => array(
                    'from'      => 'email',
                    'default'   => __( 'contact email', 'automatorwp-keap' ),
                    'fields'    => array(
                        'email' => array(
                            'name'          => __( 'Email:', 'automatorwp-keap' ),
                            'type'          => 'text',
                            'placeholder'   => __( 'user@example.com', 'automatorwp-keap' ),
                            'default'       => '',
                            'required'      => false,
                            'description'   => __( 'Leave empty to trigger for any new contact', 'automatorwp-keap' )
                        )
                    )
                )
            ),
        ) );

    }

    /**
     * Trigger check on contact added
     *
     * @since 1.1.0
     *
     * @param array $contact Contact data from Keap
     */
    public function trigger_check( $contact = array() ) {

        automatorwp_trigger_event( array(
            'trigger'   => $this->trigger,
            'user_id'   => 0, // Can be mapped to user via email
            'post_id'   => 0,
            'meta'      => $contact
        ) );

    }

    /**
     * Register required hooks
     *
     * @since 1.1.0
     */
    public function hooks() {

        // Display trigger configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Configuration notice for trigger
     *
     * @since 1.1.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {

        // Return if not a trigger
        if( $item_type !== 'trigger' ) {
            return;
        }

        // Return if not our trigger
        if( $object->post_type !== $this->trigger ) {
            return;
        }

        // Show notice
        echo automatorwp_admin_notice(
            __( 'This trigger will fire when a new contact is added to Keap (usually via webhook or Keap API).', 'automatorwp-keap' ),
            'info'
        );

    }

    /**
     * Trigger log meta
     *
     * @since 1.1.0
     */
    public function log_meta( $log_meta, $trigger, $user_id, $trigger_options, $automation ) {

        // Bail if trigger is not ours
        if( $trigger->post_type !== $this->trigger ) {
            return $log_meta;
        }

        // Store contact information
        $log_meta['contact_email'] = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        return $log_meta;

    }

    /**
     * Trigger log fields
     *
     * @since 1.1.0
     */
    public function log_fields( $log_fields, $log, $object_type, $object_subtype, $trigger_type ) {

        // Bail if log is not assigned to this trigger
        if( $trigger_type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['contact_email'] = array(
            'name'     => __( 'Contact Email', 'automatorwp-keap' ),
            'type'     => 'text',
            'value'    => isset( $log['meta']['contact_email'] ) ? $log['meta']['contact_email'] : '',
        );

        return $log_fields;

    }

}

new AutomatorWP_Keap_Contact_Added();
