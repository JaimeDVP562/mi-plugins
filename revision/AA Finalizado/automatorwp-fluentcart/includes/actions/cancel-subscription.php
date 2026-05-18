<?php
/**
 * Cancel Subscription
 *
 * @package     AutomatorWP\Integrations\FluentCart\Actions\Cancel_Subscription
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Cancel_Subscription extends AutomatorWP_Integration_Action {

    public $integration = 'fluentcart';
    public $action      = 'fluentcart_cancel_subscription';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Cancel a subscription', 'automatorwp-fluentcart' ),
            'select_option' => __( 'Cancel a <strong>subscription</strong> in FluentCart', 'automatorwp-fluentcart' ),
            /* translators: %1$s: Subscription ID. */
            'edit_label'    => sprintf( __( 'Cancel subscription %1$s in FluentCart', 'automatorwp-fluentcart' ), '{subscription_id}' ),
            /* translators: %1$s: Subscription ID. */
            'log_label'     => sprintf( __( 'Cancel subscription %1$s in FluentCart', 'automatorwp-fluentcart' ), '{subscription_id}' ),
            'options'       => array(
                'subscription_id' => array(
                    'from'    => 'subscription_id',
                    'default' => __( 'subscription ID', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'subscription_id' => array(
                            'name'    => __( 'Subscription ID:', 'automatorwp-fluentcart' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                    ),
                ),
                'cancellation_note' => array(
                    'from'    => 'cancellation_note',
                    'default' => __( 'cancellation note', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'cancellation_note' => array(
                            'name'    => __( 'Cancellation Note (optional):', 'automatorwp-fluentcart' ),
                            'type'    => 'textarea',
                            'default' => ''
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
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $subscription_id   = absint( $action_options['subscription_id'] );
        $cancellation_note = sanitize_text_field( $action_options['cancellation_note'] );

        $this->result = '';

        if( empty( $subscription_id ) ) {
            return;
        }

        if( ! class_exists( '\FluentCart\App\Models\Subscription' ) ) {
            return;
        }

        $subscription = \FluentCart\App\Models\Subscription::find( $subscription_id );

        if( ! $subscription ) {
            $this->result = __( 'Subscription not found.', 'automatorwp-fluentcart' );
            return;
        }

        if( isset( $subscription->status ) && $subscription->status === 'cancelled' ) {
            $this->result = __( 'Subscription is already cancelled.', 'automatorwp-fluentcart' );
            return;
        }

        $subscription->update( array( 'status' => 'cancelled' ) );

        if( ! empty( $cancellation_note ) ) {
            $subscription->updateMeta( 'cancellation_note', $cancellation_note );
        }

        $this->result = __( 'Subscription cancelled successfully.', 'automatorwp-fluentcart' );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields
     * @param stdClass  $log
     * @param stdClass  $object
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-fluentcart' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_FluentCart_Cancel_Subscription();