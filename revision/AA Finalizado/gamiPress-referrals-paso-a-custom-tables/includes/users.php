<?php
/**
 * Users
 *
 * @package GamiPress\Referrals\Users
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Display referrals information for a user on their profile screen
 *
 * @since  1.0.0
 *
 * @param  object $user The current user's $user object
 *
 * @return void
 */
function gamipress_referrals_user_profile_data( $user = null ) {

    // Verify user meets minimum role to manage earned achievements
    if ( current_user_can( gamipress_get_manager_capability() ) ) :

        $referral_id = gamipress_referrals_get_affiliate_referral_id( $user );
        $url_parameter = gamipress_referrals_get_option( 'url_parameter', 'ref' );
        $affiliate = gamipress_referrals_get_user_affiliate( $user ); ?>

        <div class="gamipress-referrals-user-info">

            <h2><?php echo gamipress_dashicon( 'gamipress' ); ?> <?php _e( 'GamiPress - Referrals', 'gamipress-referrals' ); ?></h2>

            <table class="form-table">
                <?php if ( $affiliate ) : ?>
                    <tr>
                        <th>
                            <label><?php echo __( 'Referred by:', 'gamipress-referrals' ); ?></label>
                        </th>
                        <td>
                            <?php if( current_user_can( 'edit_users' ) ) { ?>
                                <a href="<?php echo get_edit_user_link( $affiliate->ID ); ?>"><?php echo $affiliate->display_name; ?></a>
                                <br>
                                <?php echo $affiliate->user_email; ?>
                            <?php } else {
                                echo $affiliate->display_name;
                            } ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>
                        <label><?php echo __( 'Affiliate URL:', 'gamipress-referrals' ); ?></label>
                    </th>
                    <td>
                        <?php echo add_query_arg( array( $url_parameter => $referral_id ), home_url() ); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php echo __( 'Visits:', 'gamipress-referrals' ); ?></label>
                    </th>
                    <td>
                        <?php echo gamipress_referrals_get_referrals_count( $user->ID, 'visits' ); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php echo __( 'Signups:', 'gamipress-referrals' ); ?></label>
                    </th>
                    <td>
                        <?php echo gamipress_referrals_get_referrals_count( $user->ID, 'signups' ); ?>
                    </td>
                </tr>
                <?php if ( gamipress_referrals_enable_sales() ) : ?>
                    <tr>
                        <th>
                            <label><?php echo __( 'Sales:', 'gamipress-referrals' ); ?></label>
                        </th>
                        <td>
                            <?php echo gamipress_referrals_get_referrals_count( $user->ID, 'sales' ); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php echo __( 'Sales Refunded:', 'gamipress-referrals' ); ?></label>
                        </th>
                        <td>
                            <?php echo gamipress_referrals_get_referrals_count( $user->ID, 'sales_refunded' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

        </div>

        <hr>

    <?php endif;

}
add_action( 'show_user_profile', 'gamipress_referrals_user_profile_data' );
add_action( 'edit_user_profile', 'gamipress_referrals_user_profile_data' );