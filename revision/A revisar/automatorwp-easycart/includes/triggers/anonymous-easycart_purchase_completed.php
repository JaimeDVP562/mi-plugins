<?php
/**
 * Anonymous EasyCart – Purchase Completed Trigger
 *
 * Fires when a guest user (not logged in) completes a purchase.
 *
 * @package     AutomatorWP\EasyCart\Triggers\Anonymous_Purchase_Completed
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_EasyCart_Anonymous_Purchase_Completed_Trigger extends AutomatorWP_Integration_Trigger {

    public $integration = 'easycart';
    public $trigger     = 'easycart_anonymous_purchase_completed';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'anonymous'     => true,
            'label'         => __( 'Any EasyCart Purchase by Guest', 'automatorwp-easycart' ),
            'select_option' => __( 'Guest makes any EasyCart purchase', 'automatorwp-easycart' ),
            'edit_label'    => __( 'Guest makes any EasyCart purchase', 'automatorwp-easycart' ),
            'log_label'     => __( 'Guest makes any EasyCart purchase', 'automatorwp-easycart' ),
            'action'        => 'easycart_purchase_completed',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 3,
            'options'       => array(),
            'tags'          => array(),
            'types'         => array( 'user' ),
        ) );
    }
    public function listener( $order_id, $order_data, $order_items ) {
        if ( empty( $order_id ) ) {
            return;
        }

        $user_id = get_current_user_id();
        if ( $user_id === 0 && ! empty( $order_data->user_id ) ) {
            $user_id = absint( $order_data->user_id );
        }

        if ( $user_id !== 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'  => $this->trigger,
            'user_id'  => 0,
            'order_id' => $order_id,
        ) );
    }
    public function log_meta( $order_id, $user_id, $trigger_options ) {
        return array(
            'order_id' => $order_id,
        );
    }

    public function log_fields() {
        return array(
            'order_id' => array(
                'name' => __( 'Order ID', 'automatorwp-easycart' ),
                'type' => 'text',
            ),
        );
    }
    public function times() {
        return 1;
    }

}

new AutomatorWP_EasyCart_Anonymous_Purchase_Completed_Trigger();
