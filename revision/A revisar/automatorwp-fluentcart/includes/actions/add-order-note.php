<?php
/**
 * Action: Add a note to a FluentCart order.
 *
 * Uses Order->addLog() confirmed from Order.php line 698.
 * Notes appear in the Activity section of the order detail page.
 *
 * @package     AutomatorWP\Integrations\FluentCart\Actions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Action_Add_Order_Note extends AutomatorWP_Integration_Action {

    public $integration = 'fluentcart';
    public $action      = 'fluentcart_add_order_note';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add a note to an order', 'automatorwp-fluentcart' ),
            'select_option' => __( 'Add a <strong>note</strong> to a FluentCart order', 'automatorwp-fluentcart' ),
            'edit_label'    => __( 'Add note to order {order_id}: {note} ({note_type})', 'automatorwp-fluentcart' ),
            'log_label'     => __( 'Add note to order {order_id}', 'automatorwp-fluentcart' ),
            'tags'          => automatorwp_fluentcart_get_add_order_note_response_tags(),
            'options'       => array(
                'order_id' => array(
                    'from'   => 'order_id',
                    'fields' => array(
                        'order_id' => array(
                            'name'        => __( 'Order ID', 'automatorwp-fluentcart' ),
                            'type'        => 'text',
                            'placeholder' => __( 'Use {order_id} tag from trigger', 'automatorwp-fluentcart' ),
                            'required'    => true,
                            'default'     => '',
                        ),
                    ),
                ),
                'note' => array(
                    'from'   => 'note',
                    'fields' => array(
                        'note' => array(
                            'name'        => __( 'Note', 'automatorwp-fluentcart' ),
                            'type'        => 'textarea',
                            'placeholder' => __( 'Note content — supports tags like {user:display_name}', 'automatorwp-fluentcart' ),
                            'required'    => true,
                            'default'     => '',
                        ),
                    ),
                ),
                'note_type' => array(
                    'from'   => 'note_type',
                    'fields' => array(
                        'note_type' => array(
                            'name'    => __( 'Note Type', 'automatorwp-fluentcart' ),
                            'type'    => 'select',
                            'options' => array(
                                'info'    => __( 'Info', 'automatorwp-fluentcart' ),
                                'success' => __( 'Success', 'automatorwp-fluentcart' ),
                                'warning' => __( 'Warning', 'automatorwp-fluentcart' ),
                                'error'   => __( 'Error', 'automatorwp-fluentcart' ),
                            ),
                            'default' => 'info',
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        if ( ! class_exists( '\FluentCart\App\Models\Order' ) ) {
            automatorwp_fluentcart_log_error( 'add_order_note: Order model class not found.' );
            return;
        }
        // Read order_id directly — no tag parsing for static numeric values.
        $order_id  = absint( $action_options['order_id'] ?? 0 );
        $event     = array();
        $note      = sanitize_text_field( automatorwp_fluentcart_parse_and_sanitize( $action_options['note'] ?? '', $action, $user_id, $event ) );
        $note_type = sanitize_text_field( $action_options['note_type'] ?? 'info' );
        if ( $order_id === 0 || empty( $note ) ) {
            automatorwp_fluentcart_log_error( 'add_order_note: order_id or note is empty.', array( 'order_id' => $order_id ) );
            return;
        }
        $order = \FluentCart\App\Models\Order::find( $order_id );
        if ( ! $order ) {
            automatorwp_fluentcart_log_error( 'add_order_note: order not found.', array( 'order_id' => $order_id ) );
            return;
        }
        // Use native FluentCart addLog() — confirmed from Order.php line 698.
        $order->addLog(
            __( 'Note added by AutomatorWP', 'automatorwp-fluentcart' ),
            $note,
            $note_type,
            'automatorwp'
        );
        do_action( 'automatorwp_fluentcart_order_note_added', $order_id, $note, $note_type );
    }
}

new AutomatorWP_FluentCart_Action_Add_Order_Note();