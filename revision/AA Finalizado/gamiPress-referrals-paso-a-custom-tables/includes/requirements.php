<?php
/**
 * Requirements
 *
 * @package GamiPress\Referrals\Requirements
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add the count field to the requirement object
 *
 * @param $requirement
 * @param $requirement_id
 *
 * @return array
 */
function gamipress_referrals_requirement_object( $requirement, $requirement_id ) {

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_referrals_referral_visits'
            || $requirement['trigger_type'] === 'gamipress_referrals_referral_signups'
            || $requirement['trigger_type'] === 'gamipress_referrals_woocommerce_referral_sales'
            || $requirement['trigger_type'] === 'gamipress_referrals_woocommerce_referral_sales_refunds'
            || $requirement['trigger_type'] === 'gamipress_referrals_easy_digital_downloads_referral_sales'
            || $requirement['trigger_type'] === 'gamipress_referrals_easy_digital_downloads_referral_sales_refunds' ) ) {

        // Count
        $requirement['referrals_count'] = get_post_meta( $requirement_id, '_gamipress_referrals_count', true );

    }

    return $requirement;
}
add_filter( 'gamipress_requirement_object', 'gamipress_referrals_requirement_object', 10, 2 );

/**
 * Category field on requirements UI
 *
 * @param $requirement_id
 * @param $post_id
 */
function gamipress_referrals_requirement_ui_fields( $requirement_id, $post_id ) {

    $count = absint( get_post_meta( $requirement_id, '_gamipress_referrals_count', true ) );
    ?>

    <span class="referrals-count"><input type="text" value="<?php echo $count; ?>" size="3" placeholder="0" /></span>

    <?php
}
add_action( 'gamipress_requirement_ui_html_after_achievement_post', 'gamipress_referrals_requirement_ui_fields', 10, 2 );

/**
 * Custom handler to save the count on requirements UI
 *
 * @param $requirement_id
 * @param $requirement
 */
function gamipress_referrals_ajax_update_requirement( $requirement_id, $requirement ) {

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_referrals_referral_visits'
            || $requirement['trigger_type'] === 'gamipress_referrals_referral_signups'
            || $requirement['trigger_type'] === 'gamipress_referrals_woocommerce_referral_sales'
            || $requirement['trigger_type'] === 'gamipress_referrals_woocommerce_referral_sales_refunds'
            || $requirement['trigger_type'] === 'gamipress_referrals_easy_digital_downloads_referral_sales'
            || $requirement['trigger_type'] === 'gamipress_referrals_easy_digital_downloads_referral_sales_refunds' ) ) {

        // Save the count field
        update_post_meta( $requirement_id, '_gamipress_referrals_count', $requirement['referrals_count'] );

    }
}
add_action( 'gamipress_ajax_update_requirement', 'gamipress_referrals_ajax_update_requirement', 10, 2 );