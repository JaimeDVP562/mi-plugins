<?php
/**
 * Admin Notices
 *
 * @package     ShortLinksPro\Admin\Notices
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin notices
 *
 * @since 1.0.0
 */
function shortlinkspro_admin_notices() {

    // Bail if current user is not a site administrator
    if( ! current_user_can( 'update_plugins' ) ) {
        return;
    }

    // Check if user checked already hide the review notice
    $hide_review_notice = ( $exists = get_option( 'shortlinkspro_hide_review_notice' ) ) ? $exists : '';

    if( $hide_review_notice !== 'yes' ) {

        // Get the installation date
        $shortlinkspro_install_date = ( $exists = get_option( 'shortlinkspro_install_date' ) ) ? $exists : gmdate( 'Y-m-d H:i:s' );

        $now = gmdate( 'Y-m-d h:i:s' );
        $datetime1 = new DateTime( $shortlinkspro_install_date );
        $datetime2 = new DateTime( $now );

        // Difference in days between installation date and now
        $diff_interval = round( ( $datetime2->format( 'U' ) - $datetime1->format( 'U' ) ) / ( 60 * 60 * 24 ) );

        if( $diff_interval >= 7 ) {
            ?>

            <div class="notice shortlinkspro-review-notice">
                <div class="shortlinkspro-logo-white"></div>
                <p>
                    <?php _e( 'Awesome! You\'ve been using <strong>ShortLinks Pro</strong> for a while.', 'shortlinkspro' ); ?><br>
                    <?php _e( 'May I ask you to give it a <strong>5-star rating</strong> on WordPress?', 'shortlinkspro' ); ?><br>
                    <?php esc_html_e( 'This will help to spread its popularity and to make this plugin a better one.', 'shortlinkspro' ); ?><br>
                    <br>
                    <?php esc_html_e( 'Your help is much appreciated. Thank you very much,', 'shortlinkspro' ); ?><br>
                    <span>~Ruben Garcia</span>
                </p>
                <ul>
                    <li><a href="https://wordpress.org/support/plugin/shortlinkspro/reviews/?rate=5#new-post" class="button button-primary" target="_blank" title="<?php echo esc_attr( __( 'Yes, I want to rate it!', 'shortlinkspro' ) ); ?>"><?php esc_html_e( 'Yes, I want to rate it!', 'shortlinkspro' ); ?></a></li>
                    <li><a href="javascript:void(0);" class="shortlinkspro-hide-review-notice button" title="<?php esc_html_e( 'I already did', 'shortlinkspro' ); ?>"><?php esc_html_e( 'I already did', 'shortlinkspro' ); ?></a></li>
                    <li><a href="javascript:void(0);" class="shortlinkspro-hide-review-notice" title="<?php esc_html_e( 'No, I don\'t want to rate it', 'shortlinkspro' ); ?>"><small><?php esc_html_e( 'No, I don\'t want to rate it', 'shortlinkspro' ); ?></small></a></li>
                </ul>
            </div>

            <?php
        }

    }

}
add_action( 'admin_notices', 'shortlinkspro_admin_notices' );

/**
 * Ajax handler to hide review notice action
 *
 * @since 1.0.0
 */
function shortlinkspro_ajax_hide_review_notice() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    update_option( 'shortlinkspro_hide_review_notice', 'yes' );

    wp_send_json_success( array( 'success' ) );
    exit;
}

add_action( 'wp_ajax_shortlinkspro_hide_review_notice', 'shortlinkspro_ajax_hide_review_notice' );
