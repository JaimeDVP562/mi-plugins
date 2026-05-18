<?php
/**
 * Subscription Activated
 *
 * @package     AutomatorWP\Integrations\FluentCart\Triggers\Subscription_Activated
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Subscription_Activated extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentcart';
    public $trigger     = 'fluentcart_subscription_activated';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User activates a subscription', 'automatorwp-fluentcart' ),
            'select_option' => __( 'User <strong>activates a subscription</strong>', 'automatorwp-fluentcart' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'User activates a subscription %1$s time(s)', 'automatorwp-fluentcart' ), '{times}' ),
            'log_label'     => __( 'User activates a subscription', 'automatorwp-fluentcart' ),
            'action'        => 'fluent_cart/payments/subscription_active',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_times_tag(),
                array(
                    'order_id'            => array( 'label' => __( 'Order ID', 'automatorwp-fluentcart' ),            'type' => 'integer', 'preview' => '456' ),
                    'subscription_id'     => array( 'label' => __( 'Subscription ID', 'automatorwp-fluentcart' ),     'type' => 'integer', 'preview' => '7' ),
                    'subscription_status' => array( 'label' => __( 'Subscription Status', 'automatorwp-fluentcart' ), 'type' => 'text',    'preview' => 'active' ),
                    'customer_email'      => array( 'label' => __( 'Customer Email', 'automatorwp-fluentcart' ),      'type' => 'text',    'preview' => 'customer@example.com' ),
                    'customer_name'       => array( 'label' => __( 'Customer Name', 'automatorwp-fluentcart' ),       'type' => 'text',    'preview' => 'John Doe' ),
                )
            ),
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array $data
     */
    public function listener( $data ) {

        if( empty( $data['subscription'] ) || empty( $data['customer'] ) ) {
            return;
        }

        $subscription = $data['subscription'];
        $customer     = $data['customer'];
        $order        = isset( $data['order'] ) ? $data['order'] : null;

        $user_id = automatorwp_fluentcart_get_user_id_from_customer( $customer );

        if( $user_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'             => $this->trigger,
            'user_id'             => $user_id,
            'order_id'            => absint( isset( $order->id ) ? $order->id : 0 ),
            'subscription_id'     => absint( isset( $subscription->id ) ? $subscription->id : 0 ),
            'subscription_status' => sanitize_text_field( isset( $subscription->status ) ? $subscription->status : '' ),
            'customer_email'      => sanitize_email( isset( $customer->email ) ? $customer->email : '' ),
            'customer_name'       => automatorwp_fluentcart_get_customer_name( $customer ),
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger
     * @param stdClass  $trigger
     * @param int       $user_id
     * @param array     $event
     * @param array     $trigger_options
     * @param stdClass  $automation
     *
     * @return bool
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        if( ! isset( $event['subscription_id'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $trigger
     * @param int       $user_id
     * @param array     $event
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $trigger, $user_id, $event, $automation ) {

        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['order_id']            = isset( $event['order_id'] ) ? $event['order_id'] : 0;
        $log_meta['subscription_id']     = isset( $event['subscription_id'] ) ? $event['subscription_id'] : 0;
        $log_meta['subscription_status'] = isset( $event['subscription_status'] ) ? $event['subscription_status'] : '';
        $log_meta['customer_email']      = isset( $event['customer_email'] ) ? $event['customer_email'] : '';
        $log_meta['customer_name']       = isset( $event['customer_name'] ) ? $event['customer_name'] : '';

        return $log_meta;

    }

    /**
     * Trigger custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields
     * @param stdClass  $log
     * @param stdClass  $object
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['order_id']            = array( 'name' => __( 'Order ID:', 'automatorwp-fluentcart' ),            'type' => 'text' );
        $log_fields['subscription_id']     = array( 'name' => __( 'Subscription ID:', 'automatorwp-fluentcart' ),     'type' => 'text' );
        $log_fields['subscription_status'] = array( 'name' => __( 'Subscription Status:', 'automatorwp-fluentcart' ), 'type' => 'text' );
        $log_fields['customer_email']      = array( 'name' => __( 'Customer Email:', 'automatorwp-fluentcart' ),      'type' => 'text' );
        $log_fields['customer_name']       = array( 'name' => __( 'Customer Name:', 'automatorwp-fluentcart' ),       'type' => 'text' );

        return $log_fields;

    }

}

new AutomatorWP_FluentCart_Subscription_Activated();