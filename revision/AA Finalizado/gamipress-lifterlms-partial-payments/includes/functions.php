<?php
// Archivo: includes/functions.php
if ( ! defined( 'ABSPATH' ) ) exit;

// Obtiene la tasa de conversión configurada para un tipo de puntos
function gamipress_llms_pp_get_conversion( $points_type = '' ) {
    $points_types = gamipress_get_points_types();

    if ( ! isset( $points_types[ $points_type ] ) ) return false;

    $pt = $points_types[ $points_type ];
    $conversion = gamipress_get_post_meta( $pt['ID'], '_gamipress_llms_pp_conversion', true );

    if ( empty( $conversion ) ) return false;

    return $conversion; // Devuelve array: ['points' => 100, 'money' => 1]
}


// Convierte puntos a dinero
// Ejemplo: 500 puntos × (1€/100 puntos) = 5€
function gamipress_llms_pp_convert_to_money( $amount, $points_type = '' ) {
    $amount     = absint( $amount );  // absint() asegura que sea un entero positivo
    $conversion = gamipress_llms_pp_get_conversion( $points_type );

    if ( ! $conversion ) return 0;

    $tasa      = $conversion['money'] / $conversion['points'];
    $resultado = $amount * $tasa;

    return apply_filters( 'gamipress_llms_pp_convert_to_money', $resultado, $amount, $points_type, $conversion );
}


// Convierte dinero a puntos (para mostrar cuántos puntos necesita el usuario)
// Ejemplo: 5€ ÷ (1€/100 puntos) = 500 puntos
function gamipress_llms_pp_convert_to_points( $amount, $points_type = '' ) {
    $conversion = gamipress_llms_pp_get_conversion( $points_type );

    if ( ! $conversion ) return 0;

    $tasa      = $conversion['money'] / $conversion['points'];
    $resultado = ceil( $amount / $tasa );  // ceil() redondea hacia arriba

    return apply_filters( 'gamipress_llms_pp_convert_to_points', $resultado, $amount, $points_type, $conversion );
}


// Obtiene todos los descuentos de puntos que el usuario ya tiene aplicados
// Los guarda en user_meta para no perderlos entre peticiones
function gamipress_llms_pp_get_partial_payments() {
    if ( ! is_user_logged_in() ) return array();

    $user_id          = get_current_user_id();
    $partial_payments = get_user_meta( $user_id, 'gamipress_llms_partial_payments', true );

    if ( ! is_array( $partial_payments ) ) $partial_payments = array();

    return apply_filters( 'gamipress_llms_pp_get_partial_payments', $partial_payments );
}


// Suma el total de dinero que el usuario ya tiene descontado
function gamipress_llms_pp_get_total_discount() {
    $partial_payments = gamipress_llms_pp_get_partial_payments();
    $total = 0;

    foreach ( $partial_payments as $pt => $data ) {
        $total += floatval( $data['money'] );
    }

    return $total;
}
