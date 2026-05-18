<?php
/**
 * Complete Purchase Number
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Complete_Purchase_Number
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Complete_Purchase_Number extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_complete_purchase_number';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a number of total purchases', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User completes a number of total <strong>purchases</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Number of purchases. */
            'edit_label'        => sprintf( __( 'User completes a total of %1$s purchase(s)', 'automatorwp-woocommerce' ), '{orders_completed}' ),
            'log_label'         => sprintf( __( 'User completes a total of %1$s purchase(s)', 'automatorwp-woocommerce' ), '{orders_completed}' ),
            'action'            => 'woocommerce_order_status_completed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'orders_completed' => array(
                    'from' => 'orders_completed',
                    'fields' => array(
                        'orders_completed' => array(
                            'name' => __( 'Number of orders:', 'automatorwp-woocommerce' ),
                            'desc' => __( '1 for first order, 2 for second order...', 'automatorwp' ),
                            'type' => 'select',
                            'attributes' => array(
                                'type'  => 'number',
                                'min'   => '1',
                            ),
                            'default' => 1
                        ),
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

        // Get total number of orders for customer
        $num_orders = wc_get_customer_order_count( $user_id );

        // Get completed orders for customer
        $args = array(
            'customer_id' => $user_id,
            'post_status' => 'wc-completed',
            'post_type' => 'shop_order',
            'return' => 'ids',
        );
        
        // Get total of completed orders
        $orders_completed = count( wc_get_orders( $args ) );

        // Trigger the complete purchase
        automatorwp_trigger_event( array(
            'trigger'           => $this->trigger,
            'user_id'           => $user_id,
            'order_id'          => $order_id,
            'order_total'       => $order_total,
            'orders_completed'  => $orders_completed,
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

        $orders_completed = absint( $event['orders_completed'] );
        $required_orders_completed =  absint( $trigger_options['orders_completed'] );

        // Don't deserve if completed orders total doesn't match with the trigger option
        if( $orders_completed !== $required_orders_completed ) {
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

        return $log_meta;

    }

}

new AutomatorWP_WooCommerce_Complete_Purchase_Number();