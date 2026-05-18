<?php
/**
 * User Lifetime Value
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\User_Lifetime_Value
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_User_Lifetime_Value extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_user_lifetime_value';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User lifetime value is equal, less or greater than a value', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User lifetime value is <strong>equal, less or greater than</strong> a value', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Condition. %2$s: Value. %3$s: Number of times. */
            'edit_label'        => sprintf( __( 'User lifetime value %1$s %2$s %3$s time(s)', 'automatorwp-woocommerce' ), '{condition}', '{value}', '{times}' ),
            /* translators: %1$s: Condition. %2$s: Value. */
            'log_label'         => sprintf( __( 'User lifetime value %1$s %2$s', 'automatorwp-woocommerce' ), '{condition}', '{value}' ),
            'action'            => 'woocommerce_order_status_completed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'condition' => automatorwp_utilities_condition_option(),
                'value' => array(
                    'from' => 'value',
                    'fields' => array(
                        'value' => array(
                            'name' => __( 'Value:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'The lifetime value required. Support decimals.', 'automatorwp-woocommerce' ),
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
                automatorwp_utilities_post_tags( __( 'Product', 'automatorwp-woocommerce' ) ),
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
        $user_id = $order->get_user_id();

        //Bail if not user
        if ( $user_id === 0 ){
            return;
        }

        $lifetime_value = wc_get_customer_total_spent( $user_id );

        // Trigger the lifetime value
        automatorwp_trigger_event( array(
            'trigger'           => $this->trigger,
            'user_id'           => $user_id,
            'lifetime_value'    => $lifetime_value,
            'order_id'          => $order_id,
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
        if( ! isset( $event['lifetime_value'] ) ) {
            return false;
        }

        $lifetime_value = $event['lifetime_value'];
        $required_lifetime_value =  (float) $trigger_options['value'];

        // Don't deserve if order total doesn't match with the trigger option
        if( ! automatorwp_number_condition_matches( $lifetime_value, $required_lifetime_value, $trigger_options['condition'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_WooCommerce_User_Lifetime_Value();