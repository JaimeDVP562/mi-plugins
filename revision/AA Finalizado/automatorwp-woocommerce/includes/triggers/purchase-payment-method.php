<?php
/**
 * Purchase Payment Method
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Purchase_Payment_Method
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Purchase_Payment_Method extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_purchase_payment_method';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a purchase with a payment method', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User completes a purchase with a <strong>payment method</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Payment Method. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User completes a purchase with %1$s %2$s time(s)', 'automatorwp-woocommerce' ), '{payment_method}', '{times}' ),
            /* translators: %1$s: Payment Method. */
            'log_label'         => sprintf( __( 'User completes a purchase with %1$s', 'automatorwp-woocommerce' ), '{payment_method}' ),
            'action'            => 'woocommerce_order_status_completed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'payment_method' => array(
                    'from' => 'payment_method',
                    'fields' => array(
                        'payment_method' => array(
                            'name' => __( 'Payment Method:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'The payment method required.', 'automatorwp-woocommerce' ),
                            'type' => 'select',
                            'options_cb' => array( $this, 'payment_methods_options_cb' ),
                            'classes' => 'automatorwp-selector',
                            'default' => 'any'
                        )
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_woocommerce_order_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Payment methods options callback
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function payment_methods_options_cb() {

        $options = array(
            'any' => __( 'any payment method', 'automatorwp-woocommerce' ),
        );

        $payment_methods = WC()->payment_gateways->payment_gateways();

        foreach( $payment_methods as $payment_method ) {
            $options[$payment_method->id] = ( ! empty( $payment_method->title ) ? $payment_method->title : sprintf( __( 'Payment method #%s', 'automatorwp-woocommerce' ), $payment_method->id ) );
        }

        return $options;

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $order_id The order ID
     */
    public function listener( $order_id ) {

        $order = wc_get_order( $order_id );

        // Bail if not a valid order
        if( ! $order ) {
            return;
        }

        // Bail if order is not marked as completed
        if ( $order->get_status() !== 'completed' ) {
            return;
        }

        $order_total = $order->get_total();
        $user_id = $order->get_user_id();
        $payment_method = $order->get_payment_method();

        // Bail if not payment method provided
        if ( empty( $payment_method ) ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'           => $this->trigger,
            'user_id'           => $user_id,
            'order_id'          => $order_id,
            'payment_method'    => $payment_method,
            'order_total'       => $order_total,
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

        // Don't deserve if payment method is not received
        if( ! isset( $event['payment_method'] ) ) {
            return false;
        }

        // Don't deserve if payment method doesn't match with the trigger option
        if( $trigger_options['payment_method'] !== 'any' && $event['payment_method'] !== $trigger_options['payment_method'] ) {
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

        $log_meta['order_id'] = ( isset( $event['order_id'] ) ? $event['order_id'] : 0 );
        $log_meta['payment_method'] = ( isset( $event['payment_method'] ) ? $event['payment_method'] : '' );

        return $log_meta;

    }

}

new AutomatorWP_WooCommerce_Purchase_Payment_Method();