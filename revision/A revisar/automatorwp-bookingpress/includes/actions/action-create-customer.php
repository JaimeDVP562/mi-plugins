<?php
/**
 * Create Customer
 *
 * @package     AutomatorWP\Integrations\BookingPress\Actions\Create_Customer
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_BookingPress_Create_Customer extends AutomatorWP_Integration_Action {

    public $integration = 'bookingpress';
    public $action = 'bookingpress_create_customer';

    /**
     * Register the action
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a customer', 'automatorwp-bookingpress' ),
            'select_option' => __( 'Create a <strong>customer</strong>', 'automatorwp-bookingpress' ),
            /* translators: %1$s: Customer Details. */
            'edit_label'    => sprintf( __( 'Create customer with details: %1$s', 'automatorwp-bookingpress' ), '{customer_details}' ),
            /* translators: %1$s: Customer Details. */
            'log_label'     => sprintf( __( 'Create customer with details: %1$s', 'automatorwp-bookingpress' ), '{customer_details}' ),
            'options'       => array(
                'customer_details' => array(
                    'default' => __( 'Email, Name, Phone...', 'automatorwp-bookingpress' ),
                    'fields' => array(
                        'email' => array(
                            'name'    => __( 'Email (Required):', 'automatorwp-bookingpress' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                        'first_name' => array(
                            'name'    => __( 'First Name:', 'automatorwp-bookingpress' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                        'last_name' => array(
                            'name'    => __( 'Last Name:', 'automatorwp-bookingpress' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                        'phone' => array(
                            'name'    => __( 'Phone:', 'automatorwp-bookingpress' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                    )
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        global $bookingpress_customers;

        $first_name = sanitize_text_field( $action_options['first_name'] );
        $last_name  = sanitize_text_field( $action_options['last_name'] );
        $email      = sanitize_email( $action_options['email'] );
        $phone      = sanitize_text_field( $action_options['phone'] );

        $this->result = '';

        if ( empty( $email ) ) {
            $this->result = __( 'Action failed: Email is required to create a customer.', 'automatorwp-bookingpress' );
            return;
        }

        if( ! class_exists('bookingpress_customers') || empty($bookingpress_customers) ) {
            $this->result = __( 'Action failed: BookingPress customer module is not active.', 'automatorwp-bookingpress' );
            return;
        }

        $bookingpress_customer_data = array(
            'bookingpress_customer_name'      => trim( $first_name . ' ' . $last_name ),
            'bookingpress_customer_firstname' => $first_name,
            'bookingpress_customer_lastname'  => $last_name,
            'bookingpress_customer_email'     => $email,
            'bookingpress_customer_phone'     => $phone,
            'bookingpress_username'           => '',
            'bookingpress_customer_country'   => '',
            'bookingpress_customer_phone_dial_code' => '',
        );

        $creation_result = $bookingpress_customers->bookingpress_create_customer( $bookingpress_customer_data, 0, 2, 1 );

        if ( is_array( $creation_result ) && !empty( $creation_result['bookingpress_customer_id'] ) ) {
            $this->result = __( 'Customer created successfully.', 'automatorwp-bookingpress' );
        } else {
            $this->result = __( 'The customer could not be created or already exists.', 'automatorwp-bookingpress' );
        }

    }

    /**
     * Register required hooks
     */
    public function hooks() {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-bookingpress' ),
            'type' => 'text',
        );
        return $log_fields;
    }

}

new AutomatorWP_BookingPress_Create_Customer();