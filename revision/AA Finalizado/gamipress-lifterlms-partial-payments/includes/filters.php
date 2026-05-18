<?php
// Archivo: includes/filters.php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Función principal: muestra el formulario de puntos en el checkout de LifterLMS
 */
function gamipress_llms_pp_form() {

    // Solo para usuarios logueados
    if ( ! is_user_logged_in() ) return;

    $user_id          = get_current_user_id();
    $partial_payments = gamipress_llms_pp_get_partial_payments();
    $prefix           = '_gamipress_llms_pp_';

    $amount_type = gamipress_llms_pp_get_option( 'amount_type', 'input' );
    $amount_step = absint( gamipress_llms_pp_get_option( 'amount_step', '1' ) );
    if ( $amount_step === 0 ) $amount_step = 1;

    $points_types = array();

    foreach ( gamipress_get_points_types() as $points_type => $data ) {

        if ( isset( $partial_payments[ $points_type ] ) ) continue;

        if ( ! (bool) gamipress_get_post_meta( $data['ID'], $prefix . 'enable' ) ) continue;

        $data['user_points'] = gamipress_get_user_points( $user_id, $points_type );
        if ( $data['user_points'] === 0 ) continue;

        $data['conversion'] = gamipress_get_post_meta( $data['ID'], $prefix . 'conversion' );
        if ( empty( $data['conversion'] ) ) continue;

        $data['initial_amount'] = absint( gamipress_get_post_meta( $data['ID'], $prefix . 'initial_amount' ) );
        $data['max_amount']     = absint( gamipress_get_post_meta( $data['ID'], $prefix . 'max_amount' ) );

        switch ( $amount_type ) {
            case 'fixed':  $field_type = 'hidden'; break;
            case 'slider': $field_type = 'range';  break;
            default:       $field_type = 'number'; break;
        }

        $data['field_type']        = $field_type;
        $data['field_placeholder'] = 0;
        $data['field_step']        = $amount_step;
        $data['field_min']         = 0;
        $data['field_max']         = ( $data['max_amount'] === 0 ? $data['user_points'] : $data['max_amount'] );
        $data['field_value']       = $data['initial_amount'];

        $points_types[ $points_type ] = $data;
    }

    if ( empty( $points_types ) ) return;

    $initial_points_type      = array_keys( $points_types )[0];
    $initial_points_type_data = $points_types[ $initial_points_type ];

    $points_preview = '<span class="gamipress-llms-pp-preview-points">'
        . gamipress_format_amount( $initial_points_type_data['initial_amount'], $initial_points_type )
        . '</span> '
        . '<span class="gamipress-llms-pp-preview-points-type">'
        . $initial_points_type_data['plural_name']
        . '</span>';

    $preview_money = gamipress_llms_pp_convert_to_money(
        $initial_points_type_data['initial_amount'],
        $initial_points_type
    );
    $money_preview = '<span class="gamipress-llms-pp-preview-money">'
        . llms_price( $preview_money )
        . '</span>';

    global $gamipress_llms_pp_template_args;
    $gamipress_llms_pp_template_args = array(
        'amount_type'              => $amount_type,
        'amount_step'              => $amount_step,
        'points_types'             => $points_types,
        'initial_points_type'      => $initial_points_type,
        'initial_points_type_data' => $initial_points_type_data,
        'points_preview'           => $points_preview,
        'money_preview'            => $money_preview,
    );

    // CORRECCIÓN 1: usar include directo en lugar de gamipress_get_template_part()
    // porque esa función busca en una ruta específica que no existe en nuestro plugin
    include GAMIPRESS_LLMS_PP_DIR . 'templates/llms-partial-payments-checkout.php';
}

// CORRECCIÓN 2: usar el hook correcto de LifterLMS para la versión 9.x
// 'lifterlms_checkout_before_form' no existe en LifterLMS 9.x
// El hook correcto es 'lifterlms_before_checkout_form'
add_action( 'lifterlms_before_checkout_form', 'gamipress_llms_pp_form', 11 );


/**
 * Aplica el descuento al precio mostrado en el resumen del pedido
 */
function gamipress_llms_pp_apply_discount_to_plan( $price_html, $plan, $args ) {
    $partial_payments = gamipress_llms_pp_get_partial_payments();

    if ( empty( $partial_payments ) ) return $price_html;

    $total_discount = gamipress_llms_pp_get_total_discount();
    $original_price = floatval( $plan->get_price( 'price' ) );
    $final_price    = max( 0, $original_price - $total_discount );

    return llms_price( $final_price );
}
add_filter( 'llms_plan_get_price_html', 'gamipress_llms_pp_apply_discount_to_plan', 10, 3 );


/**
 * Deduce los puntos cuando el pedido se completa
 */
function gamipress_llms_pp_deduct_points_on_complete( $order ) {
    $prefix = '_gamipress_llms_pp_';

    if ( $order->get( $prefix . 'points_deducted' ) ) return;

    $partial_payments = get_user_meta( $order->get( 'user_id' ), 'gamipress_llms_partial_payments', true );

    if ( ! is_array( $partial_payments ) ) return;

    foreach ( $partial_payments as $points_type => $data ) {
        // Los puntos YA se dedujeron vía AJAX al aplicar el descuento (en ajax-functions.php)
        // No los deducimos de nuevo aquí para evitar el error de doble cobro.
        /* if ( gamipress_get_points_type( $points_type ) ) {
            gamipress_deduct_points_to_user( $order->get( 'user_id' ), $data['points'], $points_type );
        } */
    }

    $order->set( $prefix . 'points_deducted', 1 );
    $order->save();

    delete_user_meta( $order->get( 'user_id' ), 'gamipress_llms_partial_payments' );
}
add_action( 'lifterlms_order_status_completed', 'gamipress_llms_pp_deduct_points_on_complete', 10, 1 );
