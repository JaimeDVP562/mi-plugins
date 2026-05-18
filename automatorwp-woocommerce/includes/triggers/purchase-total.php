<?php
/**
 * Purchase Total
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Purchase_Total
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Purchase_Total extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_purchase_total';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a purchase with a total greater than, less than or equal to a specific amount', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User completes a purchase with a total <strong>greater than, less than or equal</strong> to a specific amount', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Condition. %2$s: Amount. %3$s: Number of times. */
            'edit_label'        => sprintf( __( 'User completes a purchase with a total %1$s %2$s %3$s time(s)', 'automatorwp-woocommerce' ), '{condition}', '{amount}', '{times}' ),
            /* translators: %1$s: Condition. %2$s: Amount. */
            'log_label'         => sprintf( __( 'User completes a purchase with a total %1$s %2$s', 'automatorwp-woocommerce' ), '{condition}', '{amount}' ),
            'action'            => 'woocommerce_order_status_completed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'condition' => automatorwp_utilities_condition_option(),
                'amount' => array(
                    'from' => 'amount',
                    'fields' => array(
                        'amount' => array(
                            'name' => __( 'Amount:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'The order total required. Support decimals.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'type' => 'number',
                                'min' => '0',
                            ),
                            'default' => 0
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

        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'order_id'      => $order_id,
            'order_total'   => $order_total,
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

        // Don't deserve if order total is not received
        if( ! isset( $event['order_total'] ) ) {
            return false;
        }

        $total = $event['order_total'];
        $required_total =  (float) $trigger_options['amount'];

        // Don't deserve if order total doesn't match with the trigger option
        if( ! automatorwp_number_condition_matches( $total, $required_total, $trigger_options['condition'] ) ) {
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

new AutomatorWP_WooCommerce_Purchase_Total();