<?php
/**
 * Make Purchase
 *
 * @package     AutomatorWP\Integrations\FluentCart\Triggers\Make_Purchase
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Complete_Purchase extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentcart';
    public $trigger = 'fluentcart_complete_purchase';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes a purchase', 'automatorwp' ),
            'select_option'     => __( 'User completes a <strong>purchase</strong>', 'automatorwp' ),
            /* translators: %1$s: Product title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User completes a purchase %1$s time(s)', 'automatorwp' ), '{times}' ),
            'log_label'         => __( 'User completes a purchase', 'automatorwp' ),
            'action'            => 'fluent_cart/order_status_changed_to_completed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_fluentcart_get_order_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array     $data
     * 
     */
    public function listener( $data ) {
    
        $user_id = get_current_user_id();

        // Bail if user is not logged
        if ($user_id === 0) {
            return;
        }

        $order = $data['order'];
        $order_id = $order->id;
        
        // Trigger user product purchased
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'order_id'      => $order_id,
            'order_data'    => $order,
        ) );
       
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

new AutomatorWP_FluentCart_Complete_Purchase();