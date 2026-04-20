<?php
/**
 * Submissions Eraser
 *
 * @package     BBForms\Privacy\Erasers\Submissions
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register eraser for submissions.
 *
 * @since 1.0.0
 *
 * @param array $erasers
 *
 * @return array
 */
function bbforms_privacy_register_submissions_erasers( $erasers ) {

    $erasers[] = array(
        'eraser_friendly_name'    => __( 'BBForms Submissions', 'bbforms' ),
        'callback'                  => 'bbforms_privacy_submissions_eraser',
    );

    return $erasers;

}
add_filter( 'wp_privacy_personal_data_erasers', 'bbforms_privacy_register_submissions_erasers' );

/**
 * Eraser for user user earnings.
 *
 * @since 1.0.0
 *
 * @param string    $email_address
 * @param int       $page
 *
 * @return array
 */
function bbforms_privacy_submissions_eraser( $email_address, $page = 1 ) {

    global $wpdb;

    // Setup query vars
    $request_id = ( isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0 );
    $anonymize = false;

    if( $request_id !== 0 ) {
        $anonymize = ( get_post_meta( $request_id, 'bbforms_anonymize', true ) === '1' );
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Setup vars
    $response = array(
        'items_removed'  => ! $anonymize,
        'items_retained' => $anonymize,
        'messages'       => array(),
        'done'           => true
    );

    $user = get_user_by( 'email', $email_address );

    if ( $user && $user->ID ) {

        if( $anonymize ) {
            // Update Submissions
            $updated = $wpdb->query( $wpdb->prepare( "UPDATE {$ct_table->db->table_name} SET user_id = 0  WHERE user_id = %d", $user->ID ) );

            if( $updated ) {
                // translators: %d: Number
                $response['messages'][] = sprintf( __( '%d submissions anonymized.', 'bbforms' ), $updated );
            }
        } else {
            // Erase Submissions
            $submissions_ids = $wpdb->get_col( $wpdb->prepare( "SELECT s.id FROM {$ct_table->db->table_name} AS s WHERE s.user_id = %d", $user->ID ) );

            // Delete submissions (parsing hooks for extra removals)
            bbforms_submissions_delete( $submissions_ids );

            $erased = count( $submissions_ids );

            if( $erased ) {
                // translators: %d: Number
                $response['messages'][] = sprintf( __( 'Removed %d submissions.', 'bbforms' ), $erased );
            }
        }

        // Check remaining items
        $items_count = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$ct_table->db->table_name} WHERE user_id = %d", $user->ID ) ) );

        // Process done!
        $response['done'] = (bool) ( $items_count === 0 );

    }

    // Return response
    return $response;

}