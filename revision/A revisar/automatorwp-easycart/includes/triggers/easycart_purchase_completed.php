<?php
/**
 * EasyCart – Purchase Completed Trigger
 *
 * Fires when a logged-in user completes a purchase.
 *
 * @package AutomatorWP\EasyCart\Triggers\Purchase_Completed
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_EasyCart_Purchase_Completed extends AutomatorWP_Integration_Trigger {

    public $integration = 'easycart';
    public $trigger     = 'easycart_purchase_completed';

    /**
     * Registra el trigger en AutomatorWP.
     */
    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User purchases a product', 'automatorwp-easycart' ),
            'select_option' => __( 'User purchases <strong>a product</strong>', 'automatorwp-easycart' ),
            'edit_label'    => sprintf( __( 'User purchases %1$s', 'automatorwp-easycart' ), '{post}' ),
            'log_label'     => sprintf( __( 'User purchases %1$s', 'automatorwp-easycart' ), '{post}' ),
            'action'        => 'easycart_purchase_completed',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 3,
            'options'       => array(
                'purchase_type' => array(
                    'type'    => 'radio',
                    'name'    => __( 'Purchase Type:', 'automatorwp-easycart' ),
                    'options' => array(
                        'any'      => __( 'Any product', 'automatorwp-easycart' ),
                        'specific' => __( 'Specific product', 'automatorwp-easycart' ),
                    ),
                    'default' => 'any'
                ),
                'post' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'post',
                    'name'              => __( 'Product:', 'automatorwp-easycart' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any product', 'automatorwp-easycart' ),
                    'action_cb'         => 'automatorwp_easycart_get_products',
                    'options_cb'        => 'automatorwp_easycart_options_cb_product',
                    'default'           => 'any',
                    'dependency'        => array(
                        'field' => 'purchase_type',
                        'value' => 'specific'
                    )
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'form_field:FIELD_NAME' => array(
                        'label'   => __( 'Form field value', 'automatorwp-easycart' ),
                        'type'    => 'text',
                        'preview' => __( 'Form field value, replace "FIELD_NAME" by the field name', 'automatorwp-easycart' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
            'types' => array( 'user' ),
        ) );
    }

    /**
     * Listener del trigger.
     *
     * Valida el pedido y dispara el evento solo si la configuración se cumple:
     * - Si se eligió "Any product", se dispara siempre.
     * - Si se eligió "Specific product", se verifica que el pedido contenga el producto seleccionado.
     *
     * @param int   $order_id    ID del pedido.
     * @param mixed $order_data  Objeto con la data del pedido.
     * @param array $order_items Array de ítems del pedido.
     */
    public function listener( $order_id, $order_data, $order_items ) {
        if ( empty( $order_id ) ) {
            return;
        }

        $user_id = get_current_user_id();
        if ( $user_id === 0 && ! empty( $order_data->user_id ) ) {
            $user_id = absint( $order_data->user_id );
        }
        if ( $user_id === 0 ) {
            return;
        }

        $purchase_type    = automatorwp_get_trigger_option( $this->trigger, 'purchase_type' );
        $selected_product = automatorwp_get_trigger_option( $this->trigger, 'post' );

        // Si se eligió "Specific product" se valida que el producto seleccionado esté en el pedido.
        if ( 'specific' === $purchase_type ) {
            if ( 'any' === $selected_product || ! $this->order_contains_product( $order_items, $selected_product ) ) {
                return;
            }
        }

        automatorwp_trigger_event( array(
            'trigger'  => $this->trigger,
            'user_id'  => $user_id,
            'order_id' => $order_id,
        ) );
    }

    /**
     * Verifica si los ítems del pedido incluyen el producto seleccionado.
     *
     * @param array $order_items    Lista de ítems del pedido.
     * @param mixed $target_product ID del producto configurado.
     *
     * @return bool True si se encuentra, false en caso contrario.
     */
    private function order_contains_product( $order_items, $target_product ) {
        if ( ! is_array( $order_items ) ) {
            return false;
        }
        foreach ( $order_items as $item ) {
            if ( isset( $item->product_id ) && absint( $item->product_id ) === absint( $target_product ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Registra meta información para el log (en este caso, el ID del pedido).
     *
     * @param int   $order_id        ID del pedido.
     * @param int   $user_id         ID del usuario.
     * @param array $trigger_options Opciones configuradas.
     *
     * @return array
     */
    public function log_meta( $order_id, $user_id, $trigger_options ) {
        return array(
            'order_id' => $order_id,
        );
    }

    /**
     * Define los campos que aparecerán en el log.
     *
     * @return array
     */
    public function log_fields() {
        return array(
            'order_id' => array(
                'name' => __( 'Order ID', 'automatorwp-easycart' ),
                'type' => 'text',
            ),
        );
    }

    /**
     * Cuántas veces se registra el trigger.
     *
     * @return int
     */
    public function times() {
        return 1;
    }
}

new AutomatorWP_EasyCart_Purchase_Completed();
