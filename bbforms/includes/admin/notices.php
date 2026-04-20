<?php
/**
 * Admin Notices
 *
 * @package     BBForms\Admin\Notices
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin notices
 *
 * @since 1.0.0
 */
function bbforms_admin_notices() {

    // Bail if current user is not a site administrator
    if( ! current_user_can( 'update_plugins' ) ) {
        return;
    }

    // Check if user checked already hide the review notice
    $hide_review_notice = ( $exists = get_option( 'bbforms_hide_review_notice' ) ) ? $exists : '';

    if( $hide_review_notice !== 'yes' ) {

        // Get the installation date
        $bbforms_install_date = ( $exists = get_option( 'bbforms_install_date' ) ) ? $exists : gmdate( 'Y-m-d H:i:s' );

        $now = gmdate( 'Y-m-d h:i:s' );
        $datetime1 = new DateTime( $bbforms_install_date );
        $datetime2 = new DateTime( $now );

        // Difference in days between installation date and now
        $diff_interval = round( ( $datetime2->format( 'U' ) - $datetime1->format( 'U' ) ) / ( 60 * 60 * 24 ) );

        if( $diff_interval >= 7 ) {
            ?>

            <div class="notice bbforms-review-notice">
                <div class="bbforms-logo"></div>
                <p>

                    <?php // translators: %s Plugin name
                    printf( esc_html__( 'Awesome! You\'ve been using %s for a while.', 'bbforms' ), '<strong>BBForms</strong>' );
                    ?><br>
                    <?php // translators: %s 5-star rating
                    printf( esc_html__( 'May I ask you to give it a %s on WordPress?', 'bbforms' ), '<strong>' . esc_html__( '5-star rating', 'bbforms' ) . '</strong>' ); ?><br>
                    <?php esc_html_e( 'This will help to spread its popularity and to make this plugin a better one.', 'bbforms' ); ?><br>
                    <br>
                    <?php esc_html_e( 'Your help is much appreciated. Thank you very much,', 'bbforms' ); ?><br>
                    <span>~Ruben Garcia</span>
                </p>
                <ul>
                    <li><a href="https://wordpress.org/support/plugin/bbforms/reviews/?rate=5#new-post" class="button button-primary" target="_blank" title="<?php echo esc_attr( __( 'Yes, I want to rate it!', 'bbforms' ) ); ?>"><?php esc_html_e( 'Yes, I want to rate it!', 'bbforms' ); ?></a></li>
                    <li><a href="javascript:void(0);" class="bbforms-hide-review-notice button" title="<?php esc_html_e( 'I already did', 'bbforms' ); ?>"><?php esc_html_e( 'I already did', 'bbforms' ); ?></a></li>
                    <li><a href="javascript:void(0);" class="bbforms-hide-review-notice" title="<?php esc_html_e( 'No, I don\'t want to rate it', 'bbforms' ); ?>"><small><?php esc_html_e( 'No, I don\'t want to rate it', 'bbforms' ); ?></small></a></li>
                </ul>
            </div>

            <?php
        }

    }

}
add_action( 'admin_notices', 'bbforms_admin_notices' );

/**
 * Ajax handler to hide review notice action
 *
 * @since 1.0.0
 */
function bbforms_ajax_hide_review_notice() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'bbforms_admin', 'nonce' );

    update_option( 'bbforms_hide_review_notice', 'yes' );

    wp_send_json_success( array( 'success' ) );
    exit;
}

add_action( 'wp_ajax_bbforms_hide_review_notice', 'bbforms_ajax_hide_review_notice' );
