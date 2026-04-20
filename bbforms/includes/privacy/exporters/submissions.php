<?php
/**
 * Submissions Exporter
 *
 * @package     BBForms\Privacy\Exporters\Submissions
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register exporter for user user earnings.
 *
 * @since 1.0.0
 *
 * @param array $exporters
 *
 * @return array
 */
function bbforms_privacy_register_submissions_exporters( $exporters ) {

    $exporters[] = array(
        'exporter_friendly_name'    => __( 'BBForms Submissions', 'bbforms' ),
        'callback'                  => 'bbforms_privacy_submissions_exporter',
    );

    return $exporters;

}
add_filter( 'wp_privacy_personal_data_exporters', 'bbforms_privacy_register_submissions_exporters' );

/**
 * Exporter for user submissions
 *
 * @since 1.0.0
 *
 * @param string    $email_address
 * @param int       $page
 *
 * @return array
 */
function bbforms_privacy_submissions_exporter( $email_address, $page = 1 ) {

    global $wpdb;

    // Setup query vars
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Setup vars
    $export_items   = array();
    $limit = 500;
    $offset = $limit * ( $page - 1 );
    $done = true;

    $user = get_user_by( 'email', $email_address );

    if ( $user && $user->ID ) {

        // Get submissions
        $submissions = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$ct_table->db->table_name} 
             WHERE user_id = %d 
             LIMIT %d, %d",
            $user->ID,
            $offset,
            $limit
        ) );

        if( is_array( $submissions ) ) {

            foreach( $submissions as $submission ) {

                // Add new items to the exported array
                $export_items[] = array(
                    'group_id'    => 'bbforms-submissions',
                    'group_label' => __( 'Submissions', 'bbforms' ),
                    'item_id'     => "bbforms-submissions-{$submission->id}",
                    'data'        => bbforms_privacy_get_submissions_data( $submission ),
                );

            }

        }

        // Check remaining items
        $exported_items_count = $limit * $page;
        $items_count = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$ct_table->db->table_name} WHERE user_id = %d", $user->ID ) ) );

        // Process done!
        $done = (bool) ( $exported_items_count >= $items_count );

    }

    // Return our exported items
    return array(
        'data' => $export_items,
        'done' => $done
    );

}

/**
 * Function to retrieve submission data.
 *
 * @since 1.0.0
 *
 * @param stdClass $submission
 *
 * @return array
 */
function bbforms_privacy_get_submissions_data( $submission ) {

    // Setup CT table
    ct_setup_table( 'bbforms_submissions' );

    $data = array();

    $data['id'] = array(
        'name' => __( 'Submission ID', 'bbforms' ),
        'value' => $submission->id,
    );

    $data['number'] = array(
        'name' => __( 'Number', 'bbforms' ),
        'value' => $submission->number,
    );

    $data['form_id'] = array(
        'name' => __( 'Form ID', 'bbforms' ),
        'value' => $submission->form_id,
    );

    // Since we are exporting the user data this field does not have so much sense
//    $data['user_id'] = array(
//        'name' => __( 'User ID', 'bbforms' ),
//        'value' => $submission->user_id,
//    );

    $data['post_id'] = array(
        'name' => __( 'Post ID', 'bbforms' ),
        'value' => $submission->post_id,
    );

    $data['fields'] = array(
        'name' => __( 'Fields Submitted', 'bbforms' ),
        'value' => $submission->fields,
    );

    $data['created_at'] = array(
        'name' => __( 'Date', 'bbforms' ),
        'value' => $submission->created_at,
    );

    /**
     * Submission to export
     *
     * @param array     $data           The data to export
     * @param int       $user_id        The user ID
     * @param stdClass  $submission     The submission object
     */
    return apply_filters( 'bbforms_privacy_get_submission_data', $data, $submission->user_id, $submission );

}