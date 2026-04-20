<?php
/**
 * Purchase Ticket
 *
 * @package     AutomatorWP\Integrations\Eventin\Triggers\Purchase_Ticket
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Eventin_Purchase_Ticket extends AutomatorWP_Integration_Trigger {

    public $integration = 'eventin';
    public $trigger = 'eventin_purchase_ticket';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User purchases a ticket for an event', 'automatorwp' ),
            'select_option'     => __( 'User purchases a <strong>ticket</strong> for an event', 'automatorwp' ),
            /* translators: %1$s: Event. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User purchases a ticket for %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            'log_label'         => sprintf( __( 'User purchases a ticket for %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'eventin_order_completed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Event:', 'automatorwp' ),
                    'option_none_label' => __( 'any event', 'automatorwp' ),
                    'post_type' => 'etn'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_eventin_get_order_tags(),
                automatorwp_utilities_post_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param OrderModel       $order    $order data
     */
    public function listener( $order ) {
    
        $user_id = get_current_user_id( );

        // Bail if user is not logged
        if ( $user_id === 0 ){
            return;
        }

        // Get the event related to order
        $event_id = get_post_meta( $order->id, 'event_id', true );

        // Trigger purchase ticket
        automatorwp_trigger_event( array(
            'trigger'   => $this->trigger,
            'user_id'   => $user_id,
            'post_id'   => $event_id,
            'order_id'  => $order->id,
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

        // Don't deserve if post is not received
        if( ! isset( $event['post_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $trigger_options['post'] ) !== absint( $event['post_id'] ) ) {
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

        $log_meta['order_id'] = ( isset( $event['order_id'] ) ? $event['order_id'] : '' );

        return $log_meta;

    }

}

new AutomatorWP_Eventin_Purchase_Ticket();