<?php
// Archivo: includes/ajax-functions.php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Acción AJAX: aplicar el descuento de puntos
 * Se ejecuta cuando el usuario hace clic en 'Aplicar descuento'
 */
function gamipress_llms_pp_apply_partial_payment() {

    // 1. Comprobación de seguridad (nonce): evita peticiones falsas externas
    check_ajax_referer( 'gamipress_llms_pp', 'nonce' );

    // 2. Solo usuarios logueados
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'No tienes permiso para hacer esto.' );
    }

    $user_id          = get_current_user_id();
    $partial_payments = gamipress_llms_pp_get_partial_payments();
    $prefix           = '_gamipress_llms_pp_';

    // 3. Obtenemos y validamos el tipo de puntos enviado
    $points_type     = sanitize_text_field( $_POST['points_type'] );
    $points_type_obj = gamipress_get_points_type( $points_type );
    if ( ! $points_type_obj ) wp_send_json_error( 'Tipo de puntos no válido.' );

    // 4. Comprobamos que no esté ya aplicado
    if ( isset( $partial_payments[ $points_type ] ) )
        wp_send_json_error( 'Ya tienes un descuento con este tipo de puntos.' );

    // 5. Comprobamos que esté habilitado para LifterLMS
    if ( ! (bool) gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'enable' ) )
        wp_send_json_error( 'Tipo de puntos no válido.' );

    // 6. Comprobamos que tenga tasa de conversión
    $conversion = gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'conversion' );
    if ( empty( $conversion ) ) wp_send_json_error( 'Tipo de puntos no válido.' );

    // 7. Obtenemos y validamos la cantidad de puntos
    $points = absint( $_POST[ $points_type . '_points' ] );
    if ( $points <= 0 ) wp_send_json_error( 'Cantidad de puntos no válida.' );

    // 8. Comprobamos límites mínimo y máximo
    $initial_amount = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'initial_amount' ) );
    $max_amount     = absint( gamipress_get_post_meta( $points_type_obj['ID'], $prefix . 'max_amount' ) );
    if ( $points < $initial_amount ) wp_send_json_error( 'Cantidad por debajo del mínimo.' );
    if ( $max_amount > 0 && $points > $max_amount ) wp_send_json_error( 'Cantidad por encima del máximo.' );

    // 9. Comprobamos que el usuario tiene suficientes puntos
    $user_points = gamipress_get_user_points( $user_id, $points_type );
    if ( $user_points < $points ) wp_send_json_error( 'No tienes suficientes puntos.' );

    // 10. Calculamos el descuento en dinero
    $money = gamipress_llms_pp_convert_to_money( $points, $points_type );
    if ( $money === 0 ) wp_send_json_error( 'El descuento calculado es cero.' );

    // 11. Guardamos el descuento aplicado en el meta del usuario
    $partial_payments[ $points_type ] = array(
        'points' => $points,
        'money'  => $money,
    );
    update_user_meta( $user_id, 'gamipress_llms_partial_payments', $partial_payments );

    // 12. Deducimos los puntos AHORA (antes del pago, para evitar fraudes)
    // Si el usuario no completa el pago, los puntos se devolverán al quitar el descuento
    gamipress_deduct_points_to_user( $user_id, $points, $points_type );

    wp_send_json_success( 'Descuento aplicado correctamente.' );
}
add_action( 'wp_ajax_gamipress_llms_pp_apply',         'gamipress_llms_pp_apply_partial_payment' );
add_action( 'wp_ajax_nopriv_gamipress_llms_pp_apply',  'gamipress_llms_pp_apply_partial_payment' );


/**
 * Acción AJAX: quitar el descuento de puntos
 * Se ejecuta cuando el usuario hace clic en '[Quitar]'
 */
function gamipress_llms_pp_remove_partial_payment() {
    check_ajax_referer( 'gamipress_llms_pp', 'nonce' );

    if ( ! is_user_logged_in() ) wp_send_json_error( 'No tienes permiso.' );

    $user_id          = get_current_user_id();
    $partial_payments = gamipress_llms_pp_get_partial_payments();
    $points_type      = sanitize_text_field( $_POST['points_type'] );
    $points_type_obj  = gamipress_get_points_type( $points_type );

    if ( ! $points_type_obj ) wp_send_json_error( 'Tipo de puntos no válido.' );
    if ( ! isset( $partial_payments[ $points_type ] ) ) wp_send_json_error( 'No tienes ese descuento aplicado.' );

    $points = $partial_payments[ $points_type ]['points'];

    // Devolvemos los puntos al usuario
    gamipress_award_points_to_user( $user_id, $points, $points_type );

    // Eliminamos el descuento del registro
    unset( $partial_payments[ $points_type ] );
    update_user_meta( $user_id, 'gamipress_llms_partial_payments', $partial_payments );

    wp_send_json_success( 'Descuento eliminado correctamente.' );
}
add_action( 'wp_ajax_gamipress_llms_pp_remove',        'gamipress_llms_pp_remove_partial_payment' );
add_action( 'wp_ajax_nopriv_gamipress_llms_pp_remove', 'gamipress_llms_pp_remove_partial_payment' );
