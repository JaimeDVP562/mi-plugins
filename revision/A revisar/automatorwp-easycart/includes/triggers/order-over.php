<?php
/**
 * EasyCart – Order Over Trigger
 *
 * @package     AutomatorWP\EasyCart\Triggers\Order_Over
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_EasyCart_Order_Over extends AutomatorWP_Integration_Trigger {

    public $integration = 'easycart';
    public $trigger     = 'easycart_order_over';

    /**
     * Register the trigger with AutomatorWP
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User places an EasyCart order', 'automatorwp-easycart' ),
            'select_option' => __( 'User places an EasyCart order', 'automatorwp-easycart' ),
            'edit_label'    => sprintf( __( 'User places an EasyCart order over %1$s', 'automatorwp-easycart' ), '{amount}' ),
            'log_label'     => sprintf( __( 'User places an EasyCart order over %1$s', 'automatorwp-easycart' ), '{amount}' ),
            'action'        => 'wpeasycart_order_success_pre',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 3,
            'options'       => array(
                'amount' => array(
                    'from'    => 'amount',
                    'default' => __( 100, 'automatorwp-easycart' ),
                    'fields'  => array(
                        'amount' => array(
                            'name'    => __( 'Amount over', 'automatorwp-easycart' ),
                            'type'    => 'text',
                            'default' => 0,
                        ),
                    ),
                ),
            ),
            'tags' => array(
                'order_id' => array(
                    'label' => __( 'Order ID', 'automatorwp-easycart' ),
                    'type'  => 'number',
                ),
                'total' => array(
                    'label' => __( 'Order Total', 'automatorwp-easycart' ),
                    'type'  => 'text',
                ),
                'user_email' => array(
                    'label' => __( 'User Email', 'automatorwp-easycart' ),
                    'type'  => 'text',
                ),
            ),
            'types' => array( 'user' ),
        ) );

    }

    /**
     * Trigger listener for EasyCart
     *
     * @param int    $order_id    The order ID
     * @param object $order_data  The order object
     * @param array  $order_items The purchased items
     */
    public function listener( $order_id, $order_data, $order_items ) {

        // Get the user ID
        $user_id = get_current_user_id();

        if ( $user_id === 0 && ! empty( $order_data->user_id ) ) {
            $user_id = absint( $order_data->user_id );
        }

        if ( $user_id === 0 ) {
            return;
        }

        // Trigger the event
        automatorwp_trigger_event( array(
            'trigger'     => $this->trigger,
            'user_id'     => $user_id,
            'order_id'    => $order_id,
            'total'       => isset( $order_data->grand_total ) ? (float) $order_data->grand_total : 0,
            'user_email'  => isset( $order_data->user_email ) ? $order_data->user_email : '',
        ) );
    }

    /**
     * Check if user deserves the trigger
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

        $total  = isset( $event['total'] ) ? (float) $event['total'] : 0;
        $amount = isset( $trigger_options['amount'] ) ? (float) $trigger_options['amount'] : 0;

        if ( $total <= $amount ) {
            return false;
        }

        return $deserves_trigger;
    }

    /**
     * Replace tag values in logs/actions
     *
     * @param array $event
     * @return array
     */
    public function tags( $event ) {
        return array(
            'order_id'   => $event['order_id'],
            'total'      => $event['total'],
            'user_email' => $event['user_email'],
        );
    }

    /**
     * Save extra log meta data
     *
     * @param array $log_meta
     * @param \AutomatorWP\Log $log
     * @param array $trigger
     * @return array
     */
    public function log_meta( $log_meta, $log, $trigger ) {
        return $log_meta;
    }

    /**
     * Register log fields available for this trigger
     *
     * @param array $log_fields
     * @param array $trigger
     * @return array
     */
    public function log_fields( $log_fields, $trigger ) {
        return $log_fields;
    }
}

new AutomatorWP_EasyCart_Order_Over();
