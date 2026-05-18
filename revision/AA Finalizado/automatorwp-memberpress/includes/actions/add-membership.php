<?php
/**
 * Add Membership
 *
 * @package     AutomatorWP\Integrations\MemberPress\Actions\Add_Membership
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Add_Membership extends AutomatorWP_Integration_Action {

    public $integration = 'memberpress';
    public $action = 'memberpress_add_membership';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add membership to user', 'automatorwp-memberpress' ),
            'select_option'     => __( 'Add <strong>membership</strong> to user', 'automatorwp-memberpress' ),
            /* translators: %1$s: Membership. */
            'edit_label'        => sprintf( __( 'Add %1$s to %2$s', 'automatorwp-memberpress' ), '{membership}', '{user}' ),
            /* translators: %1$s: Membership. */
            'log_label'         => sprintf( __( 'Add %1$s to %2$s', 'automatorwp-memberpress' ), '{membership}', '{user}' ),
            'options'           => array(
                'membership' => array(
                    'default' => __( 'membership', 'automatorwp-memberpress' ),
                    'fields' => array(
                        'post' => automatorwp_utilities_post_field( array(
                            'name' => __( 'Membership:', 'automatorwp-memberpress' ),
                            'option_none' => false,
                            'option_custom'         => true,
                            'option_custom_desc'    => __( 'Membership ID', 'automatorwp-memberpress' ),
                            'placeholder' => __( 'Select a membership', 'automatorwp-memberpress' ),
                            'post_type' => 'memberpressproduct'
                        ) ),
                        'post_custom' => automatorwp_utilities_custom_field( array(
                            'option_custom_desc'    => __( 'Membership ID', 'automatorwp-memberpress' ),
                        ) ),
                        'subtotal' => array(
                            'name' => __( 'Subtotal:', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'attributes' => array(
                                'type' => 'number'
                            ),
                            'default' => '0.00'
                        ),
                        'tax_amount' => array(
                            'name' => __( 'Tax amount:', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'attributes' => array(
                                'type' => 'number'
                            ),
                            'default' => '0.00'
                        ),
                        'tax_rate' => array(
                            'name' => __( 'Tax rate:', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'attributes' => array(
                                'type' => 'number'
                            ),
                            'default' => '0'
                        ),
                        'status' => array(
                            'name' => __( 'Status:', 'automatorwp-memberpress' ),
                            'type' => 'select',
                            'options' => array(
                                MeprTransaction::$complete_str => __( 'Complete', 'automatorwp-memberpress' ),
                                MeprTransaction::$pending_str  => __( 'Pending', 'automatorwp-memberpress' ),
                                MeprTransaction::$failed_str   => __( 'Failed', 'automatorwp-memberpress' ),
                                MeprTransaction::$refunded_str => __( 'Refunded', 'automatorwp-memberpress' ),
                            ),
                            'default' => MeprTransaction::$complete_str
                        ),
                        'gateway' => array(
                            'name' => __( 'Gateway:', 'automatorwp-memberpress' ),
                            'type' => 'select',
                            'options_cb' => array( $this, 'gateways_options_cb' ),
                            'default' => 'manual'
                        ),
                        'expiration' => array(
                            'name' => __( 'Expiration Date:', 'automatorwp-memberpress' ),
                            'desc' => __( 'Enter the membership expiration date in format YYYY-MM-DD. Leave empty for lifetime.', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
                'user' => array(
                    'from' => 'user',
                    'default' => __( 'user', 'automatorwp-memberpress' ),
                    'fields' => array(
                        'user' => array(
                            'name' => __( 'User ID:', 'automatorwp-memberpress' ),
                            'desc' => __( 'User ID that will get added to the membership. Leave blank to add the membership to the user that completes the automation.', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
            ),
        ) );

    }

    /**
     * Get all available payment gateways
     *
     * @return array
     */
    public function gateways_options_cb() {

        $options = array(
            'manual' => __( 'Manual', 'automatorwp-memberpress' )
        );

        $memberpress_options = MeprOptions::fetch();
        $payment_methods = array_keys( $memberpress_options->integrations );

        foreach ( $payment_methods as $payment_method_id ) {

            $payment_method = $memberpress_options->payment_method( $payment_method_id );

            if ( $payment_method instanceof MeprBaseRealGateway ) {
                $options[ $payment_method->id ] = sprintf( '%1$s (%2$s)', $payment_method->label, $payment_method->name );
            }

        }

        return $options;

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $post_id    = $action_options['post'];
        $subtotal   = $action_options['subtotal'];
        $tax_amount = $action_options['tax_amount'];
        $tax_rate   = $action_options['tax_rate'];
        $status     = $action_options['status'];
        $gateway    = $action_options['gateway'];
        $expiration = $action_options['expiration'];
        $user_id_to_apply = absint( $action_options['user'] );

        if( $user_id_to_apply === 0 ) {
            $user_id_to_apply = $user_id;
        }

        $post = get_post( $post_id );

        // Bail if product doesn't exists
        if( ! $post ) {
            return;
        }

        // Get the user
        $user = new MeprUser();
        $user->load_user_data_by_id( $user_id_to_apply );

        // Setup the transaction
        $transaction = new MeprTransaction();
        $transaction->trans_num  = uniqid( 'automatorwp-' );
        $transaction->user_id    = $user->ID;
        $transaction->product_id = sanitize_key( $post_id );
        $transaction->amount     = (float) $subtotal;
        $transaction->tax_amount = (float) $tax_amount;
        $transaction->total      = ( (float) $subtotal + (float) $tax_amount );
        $transaction->tax_rate   = (float) $tax_rate;
        $transaction->status     = sanitize_text_field( $status );
        $transaction->gateway    = sanitize_text_field( $gateway );
        $transaction->created_at = MeprUtils::ts_to_mysql_date( time() );

        // Setup transaction expiration
        if( empty( $expiration ) ) {
            $transaction->expires_at = MeprUtils::db_lifetime();
        } else {
            $transaction->expires_at = MeprUtils::ts_to_mysql_date( strtotime( $expiration ), 'Y-m-d 23:59:59');
        }

        // Save the transaction
        $transaction->store();

        // If transaction has the status completed
        if ( $transaction->status == MeprTransaction::$complete_str ) {

            // Record transaction completed
            MeprEvent::record( 'transaction-completed', $transaction );

            $subscription = $transaction->subscription();

            if ( $subscription->txn_count > 1 ) {
                // Record recurring payment
                MeprEvent::record( 'recurring-transaction-completed', $transaction );
            } elseif ( ! $subscription ) {
                // Record one-time payment
                MeprEvent::record( 'non-recurring-transaction-completed', $transaction );
            }

        }

    }

}

new AutomatorWP_MemberPress_Add_Membership();