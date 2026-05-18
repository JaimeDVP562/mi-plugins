<?php
/**
 * Payment Retry Status
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Payment_Retry_Status
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Payment_Retry_Status extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_payment_retry_status';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User\'s subscription payment retry status changes', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User\'s subscription <strong>payment retry status</strong> changes', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Order status. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User\'s subscription payment retry status changes to %1$s %2$s time(s)', 'automatorwp-woocommerce' ), '{status}', '{times}' ),
            /* translators: %1$s: Order status. */
            'log_label'         => sprintf( __( 'User\'s subscription payment retry status changes to %1$s', 'automatorwp-woocommerce' ), '{status}' ),
            'action'            => 'woocommerce_subscriptions_retry_status_updated',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'status' => array(
                    'from' => 'status',
                    'fields' => array(
                        'status' => array(
                            'name' => __( 'Status:', 'automatorwp-woocommerce' ),
                            'type' => 'select',
                            'options' => array(
                                'pending'    => __( 'Pending', 'automatorwp-woocommerce' ),
                                'processing' => __( 'Processing', 'automatorwp-woocommerce' ),
                                'failed'     => __( 'Failed', 'automatorwp-woocommerce' ),
                                'cancelled'  => __( 'Cancelled', 'automatorwp-woocommerce' ),
                                'complete'  => __( 'Completed', 'automatorwp-woocommerce' ),
                            ),
                            'default' => 'pending'
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
     * @param WCS_Retry $retry
     * @param string $status_from
     * @param string $status_to
     */
    public function listener( $retry, $status_from, $status_to ) {

        // Bail if status hasn't been changed
        if( $status_from === $status_to ) {
            return;
        }

        $order_id = $retry->get_order_id();

        $order = wc_get_order( $order_id );

        // Bail if no order assigned
        if( ! $order ) {
            return;
        }

        $user_id = $order->get_user_id();

        // Trigger the payment retry status change
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'order_id'      => $order_id,
            'status'        => $status_to,
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

        // Don't deserve if status is not received
        if( ! isset( $event['status'] ) ) {
            return false;
        }

        // Don't deserve if status doesn't matches with the trigger option
        if( ! automatorwp_woocommerce_order_status_matches( $event['status'], $trigger_options['status'] ) ) {
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

new AutomatorWP_WooCommerce_Payment_Retry_Status();