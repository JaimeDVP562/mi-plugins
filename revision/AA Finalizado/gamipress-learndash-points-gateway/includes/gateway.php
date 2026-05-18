<?php

if(! defined( 'ABSPATH')) {
    exit;
}

add_filter( 'learndash_payment_button', 'gamipress_learndash_custom_button', 10 ,2 );

function gamipress_learndash_custom_button($button_html, $custom_settings) {

if (! is_user_logged_in() ){
    return $button_html;
}

$current_user_id = get_current_user_id();
$course_id = get_the_ID();
$course_price = learndash_get_course_meta_setting($course_id, 'course_price');
$point_type = get_option('gamipress_ld_point_type', 'credits');
$current_points = gamipress_get_user_points($current_user_id, $point_type);
$exchange_rate = get_option('gamipress_ld_exchange_rate', 100);
$course_cost_in_points = $course_price * $exchange_rate;

$gamipress_button= '<div class="learndash_checkout_button" style="margin-top: 15px;">';

if ($current_points >= $course_cost_in_points){
    $buy_url = add_query_arg('gamipress_ld_buy', $course_id);

    $gamipress_button .= '<a href="' . esc_url( $buy_url ) . '" class="btn-join ld-button" id="btn-join-' . $course_id . '">';
    $gamipress_button .= 'Comprar con ' . $course_cost_in_points . ' ' . ucfirst($point_type);
    $gamipress_button .= '</a>';
} else {
    $gamipress_button .= '<span class="ld-text-color ld-error-message" style="color: #d94f4f; font-size: 0.9em; font-weight: bold;">';
    $gamipress_button .= 'Saldo insuficiente. Necesitas ' . $course_cost_in_points . ' ' . ucfirst($point_type) . '.';
    $gamipress_button .= '</span>';
}

$gamipress_button .= '</div>';

return $button_html . $gamipress_button;
}

add_action('template_redirect', 'gamipress_learndash_process_purchase');

function gamipress_learndash_process_purchase() {
    if ( isset($_GET['gamipress_ld_buy'])){
        if (! is_user_logged_in() ){
            return;
        }       
        
        $course_id = intval($_GET['gamipress_ld_buy']);
        $current_user_id = get_current_user_id();
        $course_price = learndash_get_course_meta_setting( $course_id, 'course_price');
        $point_type = get_option('gamipress_ld_point_type', 'credits');
        $current_points = gamipress_get_user_points($current_user_id, $point_type);
        $exchange_rate = get_option('gamipress_ld_exchange_rate', 100);
        $course_cost_in_points = $course_price * $exchange_rate;

        if ( $current_points >= $course_cost_in_points) {
            gamipress_deduct_points_to_user($current_user_id, $course_cost_in_points, $point_type);
            ld_update_course_access($current_user_id, $course_id);
            wp_redirect(get_permalink($course_id));
            exit;
        } else {
            wp_die('No tiene saldo suficiente para esta operación');
        }
    }
}