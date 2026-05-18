<?php
/**
 * Wholesale Order Completed Trigger
 *
 * @package     AutomatorWP\Integrations\Wholesale Suite\Triggers\Wholesale_Order_Completed
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Wholesale_Suite_Wholesale_Order_Completed extends AutomatorWP_Integration_Trigger{
    public $integration = 'wholesale_suite';
    public $trigger = 'wholesale_suite_order_completed';

    public function __construct()
    {
        add_action('wwp_add_order_meta', array( $this, 'catch_the_order'), 10, 2);
        parent::__construct();
    }


    public function register () {
        automatorwp_register_trigger( $this->trigger, array(
            'integration' => $this->integration,
            'label' => __( 'A wholesale customer completes an order', 'automatorwp-wholesale-suite'),
            'select_option' => __( 'A wholesale customer completes an order', 'automatorwp-wholesale-suite'),
            'priority' => 10,
            'accepted_args' => 2,
            'options' => array(),
        ) );
    }


public function catch_the_order( $order, $user_wholesale_role) {

    // comprobar que sea un pedido válido
    if ( ! $order instanceof WC_Order ) {
        return;
    }

    // comprobar el rol del usuario para no hacerlo a un usuario cualquiera
    if ( empty( $user_wholesale_role ) ) {
        return;
    }

    $order_id = $order->get_id();
    $user_id = $order->get_customer_id();

    if ( empty( $user_id) ) {
        return;
    }

    // comprobar que el pedido esté completado
    if ( $order->get_status() !== 'completed' ) {
        return;
    }
    
    automatorwp_trigger_event(array(
        'trigger' => $this->trigger,
        'user_id' => $user_id,
        'post_id' => $order_id,
    ));

}
}

new AutomatorWP_Wholesale_Suite_Wholesale_Order_Completed();