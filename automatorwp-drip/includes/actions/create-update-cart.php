<?php
/**
 * Create or Update a Cart (Shopper Activity API)
 *
 * @package     AutomatorWP\Integrations\Drip\Actions\Create_Update_Cart
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Create_Update_Cart extends AutomatorWP_Integration_Action {

    public $integration = 'drip';
    public $action      = 'drip_create_update_cart';
    public $result      = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create or update a cart in Drip', 'automatorwp-drip' ),
            'select_option' => __( 'Create or update a <strong>cart</strong> in Drip', 'automatorwp-drip' ),
            /* translators: %1$s: cart ID. */
            'edit_label'    => sprintf( __( 'Create or update cart %1$s in Drip', 'automatorwp-drip' ), '{cart_id}' ),
            /* translators: %1$s: cart ID. */
            'log_label'     => sprintf( __( 'Create or update cart %1$s in Drip', 'automatorwp-drip' ), '{cart_id}' ),
            'options'       => array(
                'cart_id' => array(
                    'from'    => 'cart_id',
                    'default' => __( 'cart', 'automatorwp-drip' ),
                    'fields'  => array(
                        'email' => array(
                            'name'       => __( 'Email:', 'automatorwp-drip' ),
                            'desc'       => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array( 'placeholder' => __( 'sample@email.com', 'automatorwp-drip' ) ),
                            'default'    => '',
                        ),
                        'cart_id' => array(
                            'name'       => __( 'Cart ID:', 'automatorwp-drip' ),
                            'desc'       => __( 'A unique identifier for the shopping cart.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'required'   => true,
                            'attributes' => array( 'placeholder' => __( 'e.g. cart-123', 'automatorwp-drip' ) ),
                            'default'    => '',
                        ),
                        'cart_url' => array(
                            'name'       => __( 'Cart URL:', 'automatorwp-drip' ),
                            'desc'       => __( 'A URL to recover the cart (used in abandoned cart emails).', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array( 'placeholder' => __( 'https://yoursite.com/cart', 'automatorwp-drip' ) ),
                            'default'    => '',
                        ),
                        'amount' => array(
                            'name'       => __( 'Cart Total (cents):', 'automatorwp-drip' ),
                            'desc'       => __( 'The total cart value in cents (e.g. 4900 for $49.00).', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array( 'placeholder' => __( 'e.g. 4900', 'automatorwp-drip' ) ),
                            'default'    => '',
                        ),
                        'currency' => array(
                            'name'       => __( 'Currency:', 'automatorwp-drip' ),
                            'desc'       => __( 'ISO 4217 currency code (e.g. USD, EUR).', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array( 'placeholder' => __( 'USD', 'automatorwp-drip' ) ),
                            'default'    => 'USD',
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        if ( ! automatorwp_drip_get_api() ) {
            $this->result = __( 'Drip integration not configured.', 'automatorwp-drip' );
            return;
        }

        $email = isset( $action_options['email'] ) && ! empty( $action_options['email'] )
            ? sanitize_email( $action_options['email'] )
            : '';

        if ( empty( $email ) ) {
            $user  = get_user_by( 'ID', $user_id );
            $email = $user ? $user->user_email : '';
        }

        $cart_id  = isset( $action_options['cart_id'] ) ? sanitize_text_field( $action_options['cart_id'] ) : '';
        $cart_url = isset( $action_options['cart_url'] ) ? esc_url_raw( $action_options['cart_url'] ) : '';
        $amount   = isset( $action_options['amount'] ) && $action_options['amount'] !== '' ? absint( $action_options['amount'] ) : null;
        $currency = isset( $action_options['currency'] ) && ! empty( $action_options['currency'] )
            ? strtoupper( sanitize_text_field( $action_options['currency'] ) )
            : 'USD';

        if ( empty( $email ) || empty( $cart_id ) ) {
            $this->result = __( 'No email or cart ID provided.', 'automatorwp-drip' );
            return;
        }

        $cart_data = array(
            'provider'  => 'automatorwp',
            'email'     => $email,
            'action'    => 'created',
            'cart_id'   => $cart_id,
            'currency'  => $currency,
        );

        if ( ! empty( $cart_url ) ) {
            $cart_data['cart_url'] = $cart_url;
        }

        if ( ! is_null( $amount ) ) {
            $cart_data['grand_total'] = $amount;
        }

        $response = automatorwp_drip_create_update_cart( $cart_data );

        if ( $response['code'] === 204 || $response['code'] === 200 || $response['code'] === 202 ) {
            $this->result = sprintf( __( 'Cart %1$s created/updated in Drip for %2$s.', 'automatorwp-drip' ), $cart_id, $email );
        } else {
            $this->result = sprintf( __( 'Drip API error: HTTP %d', 'automatorwp-drip' ), $response['code'] );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object
     * @param string    $item_type
     */
    public function configuration_notice( $object, $item_type ) {

        if ( $item_type !== 'action' || $object->type !== $this->action ) return;

        if ( ! automatorwp_drip_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Drip settings</a> to get this action to work.', 'automatorwp-drip' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-drip'
                ); ?>
            </div>
        <?php endif;

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = (string) $this->result;
        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object ) {

        if ( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-drip' ), 'type' => 'text' );
        return $log_fields;

    }

}

new AutomatorWP_Drip_Create_Update_Cart();
