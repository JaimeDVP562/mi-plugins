<?php
/**
 * Add Order Note
 *
 * @package     AutomatorWP\Integrations\FluentCart\Actions\Add_Order_Note
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Add_Order_Note extends AutomatorWP_Integration_Action {

    public $integration = 'fluentcart';
    public $action      = 'fluentcart_add_order_note';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add a note to an order', 'automatorwp-fluentcart' ),
            'select_option' => __( 'Add a <strong>note</strong> to a FluentCart order', 'automatorwp-fluentcart' ),
            /* translators: %1$s: Order ID. */
            'edit_label'    => sprintf( __( 'Add note to order %1$s in FluentCart', 'automatorwp-fluentcart' ), '{order_id}' ),
            /* translators: %1$s: Order ID. */
            'log_label'     => sprintf( __( 'Add note to order %1$s in FluentCart', 'automatorwp-fluentcart' ), '{order_id}' ),
            'options'       => array(
                'order_id' => array(
                    'from'    => 'order_id',
                    'default' => __( 'order ID', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'order_id' => array(
                            'name'    => __( 'Order ID:', 'automatorwp-fluentcart' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                    ),
                ),
                'note' => array(
                    'from'    => 'note',
                    'default' => __( 'note', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'note' => array(
                            'name'    => __( 'Note:', 'automatorwp-fluentcart' ),
                            'type'    => 'textarea',
                            'default' => ''
                        ),
                    ),
                ),
                'note_type' => array(
                    'from'    => 'note_type',
                    'default' => __( 'note type', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'note_type' => array(
                            'name'    => __( 'Note Type:', 'automatorwp-fluentcart' ),
                            'type'    => 'select',
                            'options' => array(
                                'info'    => __( 'Info', 'automatorwp-fluentcart' ),
                                'success' => __( 'Success', 'automatorwp-fluentcart' ),
                                'warning' => __( 'Warning', 'automatorwp-fluentcart' ),
                                'error'   => __( 'Error', 'automatorwp-fluentcart' ),
                            ),
                            'default' => 'info'
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

        $order_id  = absint( $action_options['order_id'] );
        $note      = sanitize_text_field( $action_options['note'] );
        $note_type = sanitize_text_field( $action_options['note_type'] );

        $this->result = '';

        if( empty( $order_id ) || empty( $note ) ) {
            return;
        }

        if( ! class_exists( '\FluentCart\App\Models\Order' ) ) {
            return;
        }

        $order = \FluentCart\App\Models\Order::find( $order_id );

        if( ! $order ) {
            $this->result = __( 'Order not found.', 'automatorwp-fluentcart' );
            return;
        }

        $order->addLog(
            __( 'Note added by AutomatorWP', 'automatorwp-fluentcart' ),
            $note,
            $note_type,
            'automatorwp'
        );

        $this->result = __( 'Note added successfully.', 'automatorwp-fluentcart' );

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

new AutomatorWP_FluentCart_Add_Order_Note();