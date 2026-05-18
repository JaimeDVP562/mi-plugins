<?php
if( !defined( 'ABSPATH' ) ) exit;

function gamipress_ld_partial_payments_checkout_form_shortcode() {
    
    if ( ! is_user_logged_in() ) {
        return '';
    }

    $course_id = get_the_ID();
    $user_id   = get_current_user_id();
    $pending_discount = get_user_meta( $user_id, '_gamipress_ld_pending_discount_' . $course_id, true );

    $admin_point_type = get_option( 'gamipress_ld_point_type', 'points' );
    $point_types      = function_exists( 'gamipress_get_points_types' ) ? gamipress_get_points_types() : array();

    ob_start();
    ?>
    <div class="gamipress-ld-partial-payments-container" style="margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fff;">
        <h4 style="margin-top: 0; text-align: center;"><?php esc_html_e( 'Apply Points', 'gamipress-wc-partial-payments' ); ?></h4>
        
        <input type="hidden" id="gamipress_ld_course_id" value="<?php echo esc_attr( $course_id ); ?>">
        <div id="gamipress_ld_partial_payments_message" style="margin-bottom: 10px; font-weight: bold; text-align: center; display: none;"></div>

        <?php if ( ! empty( $pending_discount ) && $pending_discount > 0 ) : ?>
            <p style="color: green; font-weight: bold; text-align: center; margin-bottom: 5px;">
                <?php esc_html_e( 'Discount applied!', 'gamipress-wc-partial-payments' ); ?>
            </p>
            <p style="text-align: center; font-size: 0.9em; margin-bottom: 15px;">
                <?php printf( esc_html__( 'Pending discount: %s', 'gamipress-wc-partial-payments' ), number_format( $pending_discount, 2 ) ); ?>
            </p>
            <button type="button" id="gamipress_ld_remove_points_btn" class="button" style="width: 100%; background-color: #dc3232; color: white; border-color: #dc3232;">
                <?php esc_html_e( 'Remove', 'gamipress-wc-partial-payments' ); ?>
            </button>
        <?php else : ?>
            <p style="text-align: center; font-size: 0.9em; margin-bottom: 15px;"><?php esc_html_e( 'Enter the amount to use.', 'gamipress-wc-partial-payments' ); ?></p>
            
            <div class="gamipress-ld-partial-payments-input-wrapper" style="display: flex; flex-direction: column; gap: 10px;">
                
                <input type="number" id="gamipress_ld_points_to_apply" name="gamipress_ld_points_to_apply" min="0" step="1" placeholder="0" style="padding: 8px; width: 100%; box-sizing: border-box;">
                
                <?php if ( $admin_point_type === 'allow_user_choice' && ! empty( $point_types ) ) : ?>
                    <select id="gamipress_ld_selected_point_type" style="padding: 8px; width: 100%; box-sizing: border-box;">
                        <?php 
                        foreach ( $point_types as $key => $type ) {
                            $slug  = '';
                            $title = '';
                            
                            if ( is_object( $type ) ) {
                                $slug  = isset( $type->post_name ) ? $type->post_name : ( isset( $type->name ) ? $type->name : $key );
                                $title = isset( $type->post_title ) ? $type->post_title : ( isset( $type->label ) ? $type->label : $key );
                            } elseif ( is_array( $type ) ) {
                                $slug  = isset( $type['post_name'] ) ? $type['post_name'] : ( isset( $type['slug'] ) ? $type['slug'] : $key );
                                $title = isset( $type['post_title'] ) ? $type['post_title'] : ( isset( $type['plural_name'] ) ? $type['plural_name'] : ( isset( $type['title'] ) ? $type['title'] : $key ) );
                            } else {
                                $slug  = $key;
                                $title = $type;
                            }
                            
                            if ( empty( $slug ) ) continue;
                            
                            echo '<option value="' . esc_attr( $slug ) . '">' . esc_html( $title ) . '</option>';
                        }
                        ?>
                    </select>
                <?php else : ?>
                    <input type="hidden" id="gamipress_ld_selected_point_type" value="<?php echo esc_attr( $admin_point_type ); ?>">
                <?php endif; ?>

                <button type="button" id="gamipress_ld_apply_points_btn" class="button" style="width: 100%;">
                    <?php esc_html_e( 'Apply', 'gamipress-wc-partial-payments' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'apply_points_box', 'gamipress_ld_partial_payments_checkout_form_shortcode' );

function gamipress_ld_auto_inject_payment_box( $button, $custom_args ) {
    if ( ! is_user_logged_in() ) return $button;
    $box_html = do_shortcode( '[apply_points_box]' );
    return $box_html . $button;
}
add_filter( 'learndash_payment_button', 'gamipress_ld_auto_inject_payment_box', 10, 2 );