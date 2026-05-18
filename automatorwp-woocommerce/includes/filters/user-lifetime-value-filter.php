<?php
/**
 * User Lifetime Value Filter
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Filters\User_Lifetime_Value_Filter
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_User_Lifetime_Value_Filter_Filter extends AutomatorWP_Integration_Filter {

    public $integration = 'woocommerce';
    public $filter = 'woocommerce_user_lifetime_value_filter';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_filter( $this->filter, array(
            'integration'       => $this->integration,
            'label'             => __( 'User lifetime value', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User <strong>lifetime value</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Condition. %2$s: Value. */
            'edit_label'        => sprintf( __( '%1$s %2$s', 'automatorwp-woocommerce' ), '{condition}', '{value}' ),
            /* translators: %1$s: Condition. %2$s: Value. */
            'log_label'         => sprintf( __( '%1$s %2$s', 'automatorwp-woocommerce' ), '{condition}', '{value}' ),
            'options'           => array(
                'condition' => automatorwp_utilities_number_condition_option(),
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
            ),
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_filter    True if user deserves filter, false otherwise
     * @param stdClass  $filter             The filter object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $filter_options     The filter's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_filter( $deserves_filter, $filter, $user_id, $event, $filter_options, $automation ) {

        // Shorthand
        $condition = $filter_options['condition'];
        $value = (float) $filter_options['value'];

        $lifetime_value = wc_get_customer_total_spent( $user_id );

        // Don't deserve if order total doesn't match with the trigger option
        if( ! automatorwp_number_condition_matches( $lifetime_value, $value, $condition ) ) {
            /* translators: %1$s: Field value. %2$s: Condition. %3$s: Field value. */
            $this->result = sprintf( __( 'Filter not passed. User lifetime value is "%1$s" and does not meets the condition %2$s "%3$s".', 'automatorwp-woocommerce' ),
                $lifetime_value,
                automatorwp_utilities_get_condition_label( $condition ),
                $value
            );
            return false;
        }

        /* translators: %1$s: Field value. %2$s: Condition. %3$s: Field value. */
        $this->result = sprintf( __( 'Filter passed. User lifetime value is "%1$s" and meets the condition %2$s "%3$s".', 'automatorwp-woocommerce' ),
            $lifetime_value,
            automatorwp_utilities_get_condition_label( $condition ),
            $value
        );

        return $deserves_filter;

    }

}

new AutomatorWP_WooCommerce_User_Lifetime_Value_Filter_Filter();