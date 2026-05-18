<?php
/**
 * Change Status Order
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Change_Status_Order
 * @author      Sergio Garcia
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Change_Status_Order extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_change_status_order';

    /**
     * Action result message
     * @var string $result
     */
    public $result = '';

    /**
     * Register the action
     * * Improved UI/UX: 
     * - Configured status selector with "all statuses" as the default tag label.
     * - Integrated dynamic order selector for better usability.
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Change status to order', 'automatorwp-woocommerce' ),
            'select_option' => __( 'Change <strong>status</strong> to <strong>order</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Status. %2$s: Order */
            'edit_label'    => sprintf( __( 'Change %1$s to %2$s', 'automatorwp-woocommerce' ), '{status}', '{post}' ),
            /* translators: %1$s: Status. %2$s: Order */
            'log_label'     => sprintf( __( 'Change %1$s to %2$s', 'automatorwp-woocommerce' ), '{status}', '{post}' ),
            'options'       => array(
                'status' => array(
                    'from'    => 'status',
                    'default' => __( 'Choose a status', 'automatorwp-woocommerce' ), 
                    'fields'  => array(
                        'status' => array(
                            'name'       => __( 'Status:', 'automatorwp-woocommerce' ),
                            'type'       => 'select',
                            'options_cb' => array( $this, 'get_status_options' ),
                            'default'    => 'any'
                        ),
                    )
                ),
                'post' => array(
                    'from'    => 'post',
                    'default' => __( 'Choose a order', 'automatorwp-woocommerce' ),
                    'fields'  => array(
                        'post' => array(
                            'name'       => __( 'Order:', 'automatorwp-woocommerce' ),
                            'type'       => 'select',
                            'options_cb' => array( $this, 'get_orders_options' ),
                            'default'    => 'any',
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Fetch WooCommerce order status
     * * @return array
     */
    public function get_status_options() {
        $statuses = array(
            'any' => __( 'Choose a status', 'automatorwp-woocommerce' )
        );

        if ( function_exists( 'wc_get_order_statuses' ) ) {
            $statuses = array_merge( $statuses, wc_get_order_statuses() );
        }

        return $statuses;
    }

    /**
     * Fetch available orders for the dropdown selector
     * * @return array
     */
    public function get_orders_options() {
        $options = array( 'any' => __( 'Choose a order', 'automatorwp-woocommerce' ) );

        if ( ! function_exists( 'wc_get_orders' ) ) return $options;

        $orders = wc_get_orders( array(
            'limit'  => 20,
            'status' => array_keys( wc_get_order_statuses() ),
        ) );

        foreach ( $orders as $order ) {
            $options[$order->get_id()] = sprintf( __( '#%1$s - %2$s', 'automatorwp-woocommerce' ), $order->get_id(), $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
        }

        return $options;
    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $status   = $action_options['status'];
        $order_id = $action_options['post'];

        if ( $order_id === 'any' || $order_id === 0 ) {
            $order_id = automatorwp_parse_automation_item_option( '{woocommerce_order_id}', array(), $user_id );
        }

        if ( $status === 'any' || empty( $order_id ) ) {
            return;
        }

        $order = wc_get_order( absint( $order_id ) );

        if ( ! $order ) {
            $this->result = sprintf( __( 'Action failed. Order ID %s not found.', 'automatorwp-woocommerce' ), $order_id );
            return;
        }

        try {
            $order->update_status( $status );
            $this->result = sprintf( __( 'Successfully updated Order #%1$s status to %2$s.', 'automatorwp-woocommerce' ), $order_id, $status );
        } catch ( Exception $e ) {
            $this->result = $e->getMessage();
        }
    }
}

new AutomatorWP_WooCommerce_Change_Status_Order();