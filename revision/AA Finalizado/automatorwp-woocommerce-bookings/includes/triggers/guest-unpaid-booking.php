<?php
/**
 * Guest Unpaid Booking
 *
 * @package     AutomatorWP\Integrations\WooCommerceBookings\Triggers\Guest-Unpaid-Booking
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerceBookings_Guest_Unpaid_Booking extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommercebookings';
    public $trigger = 'woocommercebookings_guest_unpaid_booking';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Guest unpays booking', 'automatorwp-woocommercebookings' ),
            'select_option'     => __( 'Guest unpays <strong>booking</strong>', 'automatorwp-woocommercebookings' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'Guest unpays %1$s %2$s time(s)', 'automatorwp-woocommercebookings' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'Guest unpays %1$s', 'automatorwp-woocommercebookings' ), '{post}' ),
            'action'            => 'woocommerce_booking_unpaid',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'        => __( 'Booking Product:', 'automatorwp-woocommercebookings' ),
                    'option_none' => __( 'any booking', 'automatorwp-woocommercebookings' ),
                    'post_type'   => 'product',
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
        ) );

    }

    /**
     * Catch the action and trigger the event
     *
     * @since 1.0.0
     *
     * @param int $booking_id The booking ID
     */
    public function listener( $booking_id ) {

        $booking = new WC_Booking( $booking_id );

        automatorwp_trigger_event( array(
            'trigger'    => $this->trigger,
            'booking_id' => $booking_id,
            'order_id'   => $booking->get_order_id(),
            'product_id' => $booking->get_product_id(),
        ) );

    }

    /**
     * Check if anonymous deserves the trigger
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool
     */
    public function anonymous_deserves_trigger( $deserves_trigger, $trigger, $event, $trigger_options, $automation ) {

        if( ! isset( $event['booking_id'] ) ) {
            return false;
        }

        $product_id = isset( $event['product_id'] ) ? $event['product_id'] : 0;

        if( ! automatorwp_posts_matches( $product_id, $trigger_options['post'] ) ) {
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

        add_filter( 'automatorwp_anonymous_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $event, $trigger_options, $automation ) {

        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['booking_id'] = ( isset( $event['booking_id'] ) ? $event['booking_id'] : 0 );
        $log_meta['order_id']   = ( isset( $event['order_id'] ) ? $event['order_id'] : 0 );
        $log_meta['post_id']    = ( isset( $event['product_id'] ) ? $event['product_id'] : 0 );

        return $log_meta;

    }

}