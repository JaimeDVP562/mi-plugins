<?php
if( !defined( 'ABSPATH' ) ) exit;

function gamipress_ld_apply_discount_to_course_price( $price_args, $course_id = 0 ) {
    
    if ( ! is_user_logged_in() || empty( $price_args['price'] ) ) {
        return $price_args;
    }

    if ( empty( $course_id ) ) {
        if ( isset( $_POST['course_id'] ) ) {
            $course_id = intval( $_POST['course_id'] );
        } elseif ( isset( $_POST['post_id'] ) ) {
            $course_id = intval( $_POST['post_id'] );
        } else {
            $course_id = get_the_ID();
        }
    }

    if ( ! $course_id ) {
        return $price_args;
    }

    $user_id = get_current_user_id();
    $discount_amount = get_user_meta( $user_id, '_gamipress_ld_pending_discount_' . $course_id, true );

    if ( ! empty( $discount_amount ) && is_numeric( $discount_amount ) && $discount_amount > 0 ) {
        
        $current_price = floatval( $price_args['price'] );
        $new_price     = $current_price - floatval( $discount_amount );

        if ( $new_price < 0 ) {
            $new_price = 0;
        }

        $price_args['price'] = number_format( $new_price, 2, '.', '' );
    }

    return $price_args;
}
add_filter( 'learndash_get_course_price', 'gamipress_ld_apply_discount_to_course_price', 99, 2 );

function gamipress_ld_deduct_points_after_payment( $user_id, $course_id ) {
    
    $pending_points = get_user_meta( $user_id, '_gamipress_ld_pending_points_' . $course_id, true );
    
    if ( ! empty( $pending_points ) && $pending_points > 0 ) {
        
        if ( function_exists( 'gamipress_deduct_points_to_user' ) ) {
            $point_type = get_option( 'gamipress_ld_point_type', 'points' );
            gamipress_deduct_points_to_user( $user_id, $pending_points, $point_type );
        }
        
        delete_user_meta( $user_id, '_gamipress_ld_pending_discount_' . $course_id );
        delete_user_meta( $user_id, '_gamipress_ld_pending_points_' . $course_id );
    }
}
add_action( 'learndash_update_course_access', 'gamipress_ld_deduct_points_after_payment', 10, 2 );

function gamipress_ld_apply_discount_to_wc_cart( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }

    $total_discount = 0;

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $product_id = $cart_item['product_id'];
        
        $course_id = get_post_meta( $product_id, '_related_course', true ); 
        
        if ( empty( $course_id ) ) {
            $course_id = $product_id; 
        }

        $pending_discount = get_user_meta( $user_id, '_gamipress_ld_pending_discount_' . $course_id, true );

        if ( ! empty( $pending_discount ) && is_numeric( $pending_discount ) && $pending_discount > 0 ) {
            $total_discount += floatval( $pending_discount );
        }
    }

    if ( $total_discount > 0 ) {
        $cart->add_fee( __( 'GamiPress Points Discount', 'gamipress-wc-partial-payments' ), -$total_discount, false );
    }
}
add_action( 'woocommerce_cart_calculate_fees', 'gamipress_ld_apply_discount_to_wc_cart', 10, 1 );

function gamipress_ld_deduct_points_after_wc_payment( $order_id ) {
    $order   = wc_get_order( $order_id );
    $user_id = $order->get_user_id();

    if ( ! $user_id ) {
        return;
    }

    foreach ( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id();
        $course_id  = get_post_meta( $product_id, '_related_course', true );
        
        if ( empty( $course_id ) ) {
            $course_id = $product_id;
        }

        gamipress_ld_deduct_points_after_payment( $user_id, $course_id );
}
}
add_action( 'woocommerce_order_status_completed', 'gamipress_ld_deduct_points_after_wc_payment', 10, 1 );
