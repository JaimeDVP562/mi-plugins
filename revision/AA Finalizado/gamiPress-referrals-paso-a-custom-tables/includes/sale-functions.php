<?php
/**
 * Sale Functions
 *
 * @package GamiPress\Referrals\Sale_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Referral sale commission box
 *
 * @since 1.0.0
 *
 * @param string $name          Integration name.
 * @param string $integration   Integration code.
 */
function gamipress_referrals_sale_commission_box( $name, $integration ) {

    gamipress_add_meta_box(
        "gamipress-referrals-{$integration}-sale-commission",
        sprintf( __( 'Referrals - %s Sale Commission', 'gamipress-referrals' ), $name ),
        'points-type',
        array(
            "_gamipress_referrals_{$integration}_sale_commission" => array(
                'name' => __( 'Commission', 'gamipress-referrals' ),
                'desc' => __( 'Set the commission\'s percent to award to the affiliate for referred sales.', 'gamipress-referrals' )
                    . '<br>' . __( 'A 100% will award the same purchase total as commission (e.g. $40 = 40 points).', 'gamipress-referrals' )
                    . '<br>' . __( 'A 200% will award the double of the purchase total as commission (e.g. $40 = 80 points).', 'gamipress-referrals' )
                    . '<br>' . __( 'A 50% will award the half of the purchase total as commission (e.g. $40 = 20 points).', 'gamipress-referrals' )
                    . '<br>' . __( 'Set it to 0 to disable sale commissions.', 'gamipress-referrals' ),
                'type' => 'text',
                'attributes' => array(
                    'type' => 'number',
                    'min' => '0',
                ),
                'default' => '0',
            ),
        ),
        array( 'context' => 'side' )
    );

}

/**
 * Applies the sale commission
 *
 * @since 1.0.0
 *
 * @param int       $affiliate_id   Affiliate ID
 * @param int       $referral_id    Referral ID
 * @param int       $sale_id        Sale ID
 * @param string    $integration    Integration
 */
function gamipress_referrals_award_sale_commission( $affiliate_id, $referral_id, $sale_id, $integration ) {

    $awarded = get_post_meta( $sale_id, '_gamipress_referrals_commission_awarded', true );

    // Bail if already awarded the commission
    if( (bool) $awarded ) {
        return;
    }

    // Get the sale total
    $sale_total = gamipress_referrals_get_sale_total( $affiliate_id, $referral_id, $sale_id, $integration );

    if( $sale_total <= 0 ) {
        return;
    }

    // Get the sale total formatted
    $sale_total_formatted = gamipress_referrals_get_sale_total_formatted( $sale_total, $affiliate_id, $referral_id, $sale_id, $integration );

    foreach( gamipress_get_points_types() as $points_type => $data ) {

        // Get the points to award
        $points_to_award = gamipress_referrals_get_sale_commission_points_to_award( $affiliate_id, $referral_id, $sale_id, $sale_total, $points_type, $integration );

        // Skip if there is not points to award
        if( $points_to_award <= 0 ) {
            continue;
        }

        // Award the points to the user
        gamipress_award_points_to_user( $affiliate_id, $points_to_award, $points_type );

        // Insert the custom user earning for the manual balance adjustment
        gamipress_insert_user_earning( $affiliate_id, array(
            'title'	        => sprintf(
                __( '%s as commission for refer a purchase of %s', 'gamipress-referrals' ),
                gamipress_format_points( $points_to_award, $points_type ),
                $sale_total_formatted
            ),
            'user_id'	    => $affiliate_id,
            'post_id'	    => $data['ID'],
            'post_type' 	=> 'points-type',
            'points'	    => $points_to_award,
            'points_type'	=> $points_type,
            'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
        ) );
    }

    // Set a post meta to meet that commission has been awarded
    update_post_meta( $sale_id, '_gamipress_referrals_commission_awarded', '1' );

}

/**
 * Revokes the sale commission
 *
 * @since 1.0.0
 *
 * @param int       $affiliate_id   Affiliate ID
 * @param int       $referral_id    Referral ID
 * @param int       $sale_id        Sale ID
 * @param string    $integration    Integration
 * @param WP_User   $affiliate      Affiliate object
 */
function gamipress_referrals_revoke_sale_commission( $affiliate_id, $referral_id, $sale_id, $integration ) {

    $awarded = get_post_meta( $sale_id, '_gamipress_referrals_commission_awarded', true );

    // Bail if commission not awarded
    if( ! (bool) $awarded ) {
        return;
    }

    // Get the sale total
    $sale_total = gamipress_referrals_get_sale_total( $affiliate_id, $referral_id, $sale_id, $integration );

    if( $sale_total <= 0 ) {
        return;
    }

    // Get the sale total formatted
    $sale_total_formatted = gamipress_referrals_get_sale_total_formatted( $sale_total, $affiliate_id, $referral_id, $sale_id, $integration );

    foreach( gamipress_get_points_types() as $points_type => $data ) {

        // Get the points to award
        $points_to_award = gamipress_referrals_get_sale_commission_points_to_award( $affiliate_id, $referral_id, $sale_id, $sale_total, $points_type, $integration );

        // Skip if there is not points to award
        if( $points_to_award <= 0 ) {
            continue;
        }

        // Deduct the points to the user
        gamipress_deduct_points_to_user( $affiliate_id, $points_to_award, $points_type );

        // Insert the custom user earning for the manual balance adjustment
        gamipress_insert_user_earning( $affiliate_id, array(
            'title'	        => sprintf(
                __( '-%s for the refund of a referral purchase of %s', 'gamipress-referrals' ),
                gamipress_format_points( $points_to_award, $points_type ),
                $sale_total_formatted
            ),
            'user_id'	    => $affiliate_id,
            'post_id'	    => $data['ID'],
            'post_type' 	=> 'points-type',
            'points'	    => $points_to_award,
            'points_type'	=> $points_type,
            'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
        ) );
    }

    // Set a post meta to meet that commission has been revoked
    update_post_meta( $sale_id, '_gamipress_referrals_commission_awarded', '0' );

}

/**
 * Get the sale total
 *
 * @since 1.0.0
 *
 * @param int       $affiliate_id   Affiliate ID
 * @param int       $referral_id    Referral ID
 * @param int       $sale_id        Sale ID
 * @param string    $integration    Integration
 *
 * @return float
 */
function gamipress_referrals_get_sale_total( $affiliate_id, $referral_id, $sale_id, $integration ) {

    $sale_total = 0;

    /**
     * Get the sale total
     *
     * @since 1.0.0
     *
     * @param float     $sale_total     The sale total
     * @param int       $affiliate_id   Affiliate ID
     * @param int       $referral_id    Referral ID
     * @param int       $sale_id        Sale ID
     *
     * @return float
     */
    return apply_filters( "gamipress_referrals_{$integration}_sale_total", $sale_total, $affiliate_id, $referral_id, $sale_id );

}

/**
 * Get the sale total formatted
 *
 * @since 1.0.0
 *
 * @param int       $affiliate_id   Affiliate ID
 * @param int       $referral_id    Referral ID
 * @param int       $sale_id        Sale ID
 * @param string    $integration    Integration
 *
 * @return string
 */
function gamipress_referrals_get_sale_total_formatted( $sale_total, $affiliate_id, $referral_id, $sale_id, $integration ) {

    $sale_total_formatted = $sale_total;

    /**
     * Get the sale total formatted
     *
     * @since 1.0.0
     *
     * @param string    $sale_total_formatted   The sale total formatted
     * @param float     $sale_total             The sale total
     * @param int       $affiliate_id           Affiliate ID
     * @param int       $referral_id            Referral ID
     * @param int       $sale_id                Sale ID
     *
     * @return string
     */
    return apply_filters( "gamipress_referrals_{$integration}_sale_total_formatted", $sale_total_formatted, $sale_total, $affiliate_id, $referral_id, $sale_id );

}

/**
 * Get the sale total formatted
 *
 * @since 1.0.0
 *
 * @param int       $affiliate_id   Affiliate ID
 * @param int       $referral_id    Referral ID
 * @param int       $sale_id        Sale ID
 * @param string    $integration    Integration
 *
 * @return int
 */
function gamipress_referrals_get_sale_commission_points_to_award( $affiliate_id, $referral_id, $sale_id, $sale_total, $points_type, $integration ) {

    $percent = (int) gamipress_get_post_meta( gamipress_get_points_type_id( $points_type ), "_gamipress_referrals_{$integration}_sale_commission" );

    // If commission percent is not higher than 0, bail
    if( $percent <= 0 ) {
        return 0;
    }

    // Setup the ratio value used to convert the amount spent into points
    $ratio = $percent / 100;

    $points_to_award = absint( $sale_total * $ratio );

    /**
     * Filter to allow override this amount at any time
     *
     * @since 1.0.0
     *
     * @param int       $points_to_award    Points amount that will be awarded
     * @param string    $points_type        Points type slug of the points amount
     * @param int       $percent            Percent setup on the points type
     * @param int       $affiliate_id       Affiliate ID
     * @param int       $referral_id        Referral ID
     * @param int       $sale_id            Sale ID
     *
     * @return int
     */
    return (int) apply_filters( "gamipress_referrals_{$integration}_points_to_award", $points_to_award, $points_type, $percent, $affiliate_id, $referral_id, $sale_id );


}