<?php
/**
 * Change Status Order
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Change_Status_Order
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Change_Status_Order extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_change_status_order';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Set status to order', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Set status to <strong>order</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Status. %2$s: Order */
            'edit_label'        => sprintf( __( 'Set %1$s status to %2$s', 'automatorwp-woocommerce' ), '{status}', '{post}' ),
            /* translators: %1$s: Status. %2$s: Order */
            'log_label'         => sprintf( __( 'Set %1$s status to %2$s', 'automatorwp-woocommerce' ), '{status}', '{post}' ),
            'options'           => array(
                'status' => array(
                    'from' => 'status',
                    'fields' => array(
                        'status' => array(
                            'name'          => __( 'Status:', 'automatorwp-woocommerce' ),
                            'type'          => 'select',
                            'options_cb'    => 'wc_get_order_statuses',
                            'default'       => 'wc-pending'
                        ),
                    )
                ),
                'post' => array(
                    'default' => __( 'an order', 'automatorwp-woocommerce' ),
                    'from' => 'post_id',
                    'fields' => array(
                        'post_id' => array(
                            'name' => __( 'Order ID:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'The order ID.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'required' => true,
                        ),
            ),
        ) ) ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $order_id = $action_options['post_id'];
        $status = $action_options['status'];

        // Bail if no order
        if ( empty ( $order_id ) ) {
            return;
        }

        $order = wc_get_order( $order_id );
		
		// Bail if no order
        if ( empty ( $order ) ) {
            return;
        }
		
        $order->update_status( $status );

    }

}

new AutomatorWP_WooCommerce_Change_Status_Order();