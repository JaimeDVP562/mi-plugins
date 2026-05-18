<?php
/**
 * User Register
 *
 * @package     AutomatorWP\Integrations\ti-woocommerce-wishlist\Triggers\wishlist_Add
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_ADD extends AutomatorWP_Integration_Trigger {

    public $integration = 'tiwoocommercewishlist';
    public $trigger = 'woocommerce_wishlist_add';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {
  automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User add a product', 'automatorwp-ti-woocommerce-wishlist' ),
            'select_option'     => __( 'User <strong> add a product</strong> to wishlist', 'automatorwp-ti-woocommerce-wishlist' ),
            /* translators  %1$s: Number of times.*/
            'edit_label'        => sprintf( __( ' User add a product through %1$s time(s)', 'automatorwp-ti-woocommerce-wishlist' ), '{times}' ),
            'log_label'         => __( 'User add a product through', 'automatorwp-ti-woocommerce-wishlist' ),
            'action'            => 'tinvwl_wishlist_addtowishlist_button',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                 automatorwp_user_wishlist_get_webhook_tags()
            )
        ) );

    }

    
    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array $product 
     * @param int $loop 
     */


     //do_action( 'tinvwl_wishlist_addtowishlist_button', $product, $loop )

    public function listener( $product, $loop ) {

        $usss_id = get_current_user_id();
        if ( $usss_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'product'       => $product,
            'loop'          => $loop,
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
        //Anotacion ->(Tengo que desarrollar esta parte para sacar toda la info del form_data, datos como nickname, email, etc.
        $log_meta['product'] = ( isset( $event['product'] ) ? $event['product'] : '' );
        $log_meta['loop'] = ( isset( $event['loop'] ) ? $event['loop'] : '' );

        return $log_meta;

    }

}

new AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_ADD();