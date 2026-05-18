<?php
/**
 * Ajax Functions
 *
 * @package     GamiPress\Notifications\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function to check live user notifications
 *
 * @since   1.0.0
 * @updated 1.1.3 Functionality moved to gamipress_notifications_get_user_notifications()
 */
function gamipress_notifications_ajax_get_notices() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_notifications', 'nonce' );

    // Bail if live checks has been disabled
    if( (bool) gamipress_notifications_get_option( 'disable_live_checks', false ) ) {
        wp_send_json_success( array( 'notices' => array(), 'last_check' => 0 ) );
    }

    ignore_user_abort( false );

    define( 'GAMIPRESS_NOTIFICATIONS_AJAX', true );

    // Setup vars
    $user_id = get_current_user_id();
    $user_points = ( isset( $_REQUEST['user_points'] ) && (bool) $_REQUEST['user_points'] );

    // Get user notices
    $response = gamipress_notifications_get_user_notifications( $user_id, $user_points );

    // Return user notices in format: array( 'notices' => array(), 'last_check' => 0 )
    wp_send_json_success( $response );

}
add_action( 'wp_ajax_gamipress_notifications_get_notices', 'gamipress_notifications_ajax_get_notices' );
add_action( 'wp_ajax_nopriv_gamipress_notifications_get_notices', 'gamipress_notifications_ajax_get_notices' );

/**
 * Page load version of ajax get notices
 *
 * @since   1.1.3
 */
function gamipress_notifications_page_load_get_notices() {

    // Bail if live checks are enabled
    if( ! (bool) gamipress_notifications_get_option( 'disable_live_checks', false ) ) {
        return;
    }

    // Just run on frontend
    if( is_admin() ) {
        return;
    }

    // Don't auto-mark as read on the notifications page itself
    $page_id = get_option( 'gamipress_notifications_page_id' );
    if ( $page_id && is_page( $page_id ) ) {
        return;
    }

    // Don't auto-mark on the fallback notifications page
    if ( isset( $_GET['gamipress_notifications'] ) && is_front_page() ) {
        return;
    }

    $user_id = get_current_user_id();
    
    // Get user notices
    $response = gamipress_notifications_get_user_notifications( $user_id );
    
    if (is_array($response['notices'])) : ?>

        <div class="gamipress-notifications-user-notices" style="display: none;">

            <?php foreach( $response['notices'] as $notice ) :
                echo $notice;
            endforeach; ?>

        </div>

        <?php if( $response['last_check'] !== 0 ) : ?>
            <div id="gamipress-notifications-user-last-check" data-value="<?php echo $response['last_check']; ?>"></div>
        <?php endif;

    endif;

}
add_action( 'wp_footer', 'gamipress_notifications_page_load_get_notices' );

/**
 * Ajax function to notify to the server last time user has check the notifications
 *
 * @since   1.0.0
 */
function gamipress_notifications_ajax_last_check() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_notifications', 'nonce' );

    ignore_user_abort( false );

    $user_id = get_current_user_id();

    // Just continue if user is logged in
    if( $user_id === 0 ) {
        wp_send_json_success();
    }

    $last_check = isset( $_REQUEST['last_check'] ) ? absint( $_REQUEST['last_check'] ) : current_time( 'timestamp' );

    gamipress_notifications_set_user_last_check( $user_id, $last_check );

    /**
     * Action to meet when last time user has being updated through ajax
     *
     * @since 1.2.1
     *
     * @param int       $user_id    The given user's ID
     * @param int       $last_check Last check timestamp
     */
    do_action( 'gamipress_notifications_ajax_last_check_updated', $user_id, $last_check );

    wp_send_json_success();
}
add_action( 'wp_ajax_gamipress_notifications_last_check', 'gamipress_notifications_ajax_last_check' );
add_action( 'wp_ajax_nopriv_gamipress_notifications_last_check', 'gamipress_notifications_ajax_last_check' );

/**
 * Ajax function to mark all user notifications as read
 *
 * @since 1.6.0
 */
function gamipress_notifications_ajax_mark_all_read() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_notifications', 'nonce' );

    ignore_user_abort( false );

    $user_id = get_current_user_id();

    // Log the action
    error_log( 'GamiPress Notifications: Mark all as read called for user ' . $user_id );

    // Just continue if user is logged in
    if ( $user_id === 0 ) {
        error_log( 'GamiPress Notifications: User not logged in' );
        wp_send_json_error( __( 'User not logged in.', 'gamipress-notifications' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gamipress_notifications';

    $read_column = 'read';
    if ( $wpdb->get_var( "SHOW COLUMNS FROM {$table_name} LIKE 'is_read'" ) ) {
        $read_column = 'is_read';
    }

    error_log( 'GamiPress Notifications: Using column ' . $read_column );

    $result = $wpdb->update(
        $table_name,
        array( $read_column => 1 ),
        array( 'user_id' => $user_id, $read_column => 0 ),
        array( '%d' ),
        array( '%d', '%d' )
    );

    error_log( 'GamiPress Notifications: Update result = ' . $result );

    if ( $result !== false ) {
        error_log( 'GamiPress Notifications: Successfully marked ' . $result . ' notifications as read' );
        wp_send_json_success( array( 'marked_count' => $result ) );
    } else {
        error_log( 'GamiPress Notifications: Update failed - ' . $wpdb->last_error );
        wp_send_json_error( __( 'Failed to mark notifications as read.', 'gamipress-notifications' ) );
    }
}
add_action( 'wp_ajax_gamipress_notifications_mark_all_read', 'gamipress_notifications_ajax_mark_all_read' );
add_action( 'wp_ajax_nopriv_gamipress_notifications_mark_all_read', 'gamipress_notifications_ajax_mark_all_read' );