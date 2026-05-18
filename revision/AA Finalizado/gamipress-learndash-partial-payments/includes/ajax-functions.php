<?php
if( !defined( 'ABSPATH' ) ) exit;

function gamipress_ld_apply_discount() {
    
    check_ajax_referer( 'gamipress-ld-partial-payments-nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'gamipress-wc-partial-payments' ) ) );
    }

    $points     = isset( $_POST['points'] ) ? intval( $_POST['points'] ) : 0;
    $course_id  = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
    $point_type = isset( $_POST['point_type'] ) ? sanitize_text_field( $_POST['point_type'] ) : 'points';
    $user_id    = get_current_user_id();

    if ( $points <= 0 || $course_id <= 0 ) {
        wp_send_json_error( array( 'message' => __( 'Invalid data provided.', 'gamipress-wc-partial-payments' ) ) );
    }

    $course_price = learndash_get_course_meta_setting( $course_id, 'course_price' );
    
    if ( empty( $course_price ) ) {
        wp_send_json_error( array( 'message' => __( 'This course is free or has no price.', 'gamipress-wc-partial-payments' ) ) );
    }

    $conversion_rate = (float) get_option( 'gamipress_ld_conversion_rate', '1' );
    $max_pct         = (float) get_option( 'gamipress_ld_max_discount_pct', '100' );

    $max_discount_in_money = (float) $course_price * ( $max_pct / 100 );
    $max_points_allowed    = floor( $max_discount_in_money / $conversion_rate );

    if ( $points > $max_points_allowed ) {
        wp_send_json_error( array( 'message' => sprintf( __( 'You can only apply a maximum of %d points for this course.', 'gamipress-wc-partial-payments' ), $max_points_allowed ) ) );
    }

    $discount_in_money = $points * $conversion_rate; 

    update_user_meta( $user_id, '_gamipress_ld_pending_discount_' . $course_id, $discount_in_money );
    update_user_meta( $user_id, '_gamipress_ld_pending_points_' . $course_id, $points );
    update_user_meta( $user_id, '_gamipress_ld_pending_point_type_' . $course_id, $point_type );

    wp_send_json_success( array( 'message' => __( 'Discount applied successfully! Reloading...', 'gamipress-wc-partial-payments' ) ) );
}
add_action( 'wp_ajax_gamipress_ld_apply_discount', 'gamipress_ld_apply_discount' );

function gamipress_ld_remove_discount() {
    
    check_ajax_referer( 'gamipress-ld-partial-payments-nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'gamipress-wc-partial-payments' ) ) );
    }

    $course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
    $user_id   = get_current_user_id();

    if ( $course_id <= 0 ) {
        wp_send_json_error( array( 'message' => __( 'Invalid data provided.', 'gamipress-wc-partial-payments' ) ) );
    }

    delete_user_meta( $user_id, '_gamipress_ld_pending_discount_' . $course_id );
    delete_user_meta( $user_id, '_gamipress_ld_pending_points_' . $course_id );
    delete_user_meta( $user_id, '_gamipress_ld_pending_point_type_' . $course_id );

    wp_send_json_success( array( 'message' => __( 'Discount removed successfully! Reloading...', 'gamipress-wc-partial-payments' ) ) );
}
add_action( 'wp_ajax_gamipress_ld_remove_discount', 'gamipress_ld_remove_discount' );