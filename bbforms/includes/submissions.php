<?php
/**
 * Submissions
 *
 * @package     BBForms\Submissions
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Submissions excluded fields (prevent to render them in the Submissions screen)
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_submissions_get_excluded_fields() {
    $excluded_fields = array(
        'honeypot',
        'submit',
        'reset',
    );

    return apply_filters( 'bbforms_submissions_excluded_fields', $excluded_fields );
}

/**
 * Submissions excluded to save fields (prevent to save them in the Submissions screen)
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_submissions_get_excluded_to_save_fields() {
    $excluded_fields = array(
        'hidden',
        'honeypot',
        'submit',
        'reset',
    );

    return apply_filters( 'bbforms_submissions_excluded_to_save_fields', $excluded_fields );
}

/**
 * Insert submission
 *
 * @since 1.0.0
 *
 * @param array $data
 *
 * @return int|bool
 */
function bbforms_insert_submission( $data ) {

    global $wpdb;

    $default_data = array(
        'number'                => 0,
        'form_id'               => 0,
        'user_id'               => 0,
        'post_id'               => 0,
        'fields'	            => '',
        'created_at'	        => gmdate( 'Y-m-d H:i:s' ),
    );

    $data = wp_parse_args( $data, $default_data );

    $submission_data = array();
    $submission_meta = array();

    foreach( $default_data as $key => $value ) {
        $submission_data[$key] = $data[$key];

        unset( $data[$key] );
    }

    if( count( $data ) ) {
        $submission_meta = $data;
    }

    // Sanitize data
    $submission_data['number']              = absint( $submission_data['number'] );
    $submission_data['form_id']             = absint( $submission_data['form_id'] );
    $submission_data['user_id']             = absint( $submission_data['user_id'] );
    $submission_data['post_id']             = absint( $submission_data['post_id'] );
    $submission_data['fields']              = wp_kses_post( $submission_data['fields'] );
    $submission_data['created_at']          = sanitize_text_field( $submission_data['created_at'] );

    if( $submission_data['number'] === 0 ) {
        $submission_data['number'] = bbforms_get_last_submission_number( $submission_data['form_id'] ) + 1;
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    $submission_id = $ct_table->db->insert( $submission_data );

    // Store log meta data
    if ( $submission_id && ! empty( $submission_meta ) ) {

        $metas = array();

        foreach ( (array) $submission_meta as $key => $value ) {
            // Sanitize vars
            $meta_key = sanitize_key( $key );
            $meta_key = wp_unslash( $meta_key );
            $meta_value = wp_unslash( $value );
            $meta_value = esc_sql( $meta_value );
            $meta_value = sanitize_meta( $meta_key, $meta_value, $ct_table->name );
            $meta_value = maybe_serialize( $meta_value );

            // Setup the insert value
            $metas[] = $wpdb->prepare( '%d, %s, %s', array( $submission_id, $meta_key, $meta_value ) );
        }

        $submissions_meta = $ct_table->meta->db->table_name;
        $metas = implode( '), (', $metas );

        // Since the log is recently inserted, is faster to run a single query to insert all metas instead of insert them one-by-one
        $wpdb->query( "INSERT INTO {$submissions_meta} (id, meta_key, meta_value) VALUES ({$metas})" );

    }

    // Hook to add custom data
    do_action( 'bbforms_insert_submission', $submission_id, $submission_data, $submission_meta );

    ct_reset_setup_table();

    return $submission_id;

}

/**
 * Get last submission number
 *
 * @since 1.0.0
 *
 * @param int $form_id The form id
 *
 * @return int
 */
function bbforms_get_last_submission_number( $form_id ) {

    global $wpdb;

    $form_id = absint( $form_id );

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Query search
    $last_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT s.number
        FROM {$ct_table->db->table_name} AS s
        WHERE s.form_id = %d
        ORDER BY s.number DESC
        LIMIT 1",
        $form_id,
    ) );

    ct_reset_setup_table();

    return absint( $last_id );

}

/**
 * Get form submissions count
 *
 * @since 1.0.0
 *
 * @param int $form_id The form id
 *
 * @return int
 */
function bbforms_get_submissions_count( $form_id ) {

    global $wpdb;

    $form_id = absint( $form_id );

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Query search
    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*)
        FROM {$ct_table->db->table_name} AS s
        WHERE s.form_id = %d",
        $form_id,
    ) );

    ct_reset_setup_table();

    return absint( $count );

}

/**
 * Get form submissions by field value
 *
 * @since 1.0.0
 *
 * @param int       $form_id        The form id
 * @param string    $field_name     The field name
 * @param string    $field_value    The field value
 *
 * @return int
 */
function bbforms_get_submissions_by_field( $form_id, $field_name, $field_value ) {

    global $wpdb;

    $form_id = absint( $form_id );

    // For array values, search each value individually
    if( is_array( $field_value ) ) {
        $count = 0;

        foreach( $field_value as $value ) {
            $count += bbforms_get_submissions_by_field( $form_id, $field_name, $value );
        }

        return $count;
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    $field_name = $wpdb->esc_like( $field_name );
    $field_value = $wpdb->esc_like( $field_value );

    // Query search
    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*)
        FROM {$ct_table->db->table_name} AS s
        WHERE s.form_id = %d
        AND ( s.fields LIKE %s OR s.fields LIKE %s )",
        $form_id,
        '%"' . $field_name . '":"' . $field_value . '"%',
        '%"' . $field_name . '":[%"' . $field_value . '"%]%'
    ) );

    ct_reset_setup_table();

    return absint( $count );

}

/**
 * Delete a single or list of submissions
 *
 * @since 1.0.0
 *
 * @param int|array $submissions_ids Single or array of submissions IDs to delete
 */
function bbforms_submissions_delete( $submissions_ids ) {

    global $ct_table;

    if( ! is_array( $submissions_ids ) ) {
        $submissions_ids = array( $submissions_ids );
    }

    if( count( $submissions_ids ) === 0 ) {
        return;
    }

    $table_changed = false;

    if( $ct_table->name !== 'bbforms_submissions' ) {
        $ct_table = ct_setup_table( 'bbforms_submissions' );
        $table_changed = true;
    }

    foreach( $submissions_ids as $submission_id ) {
        $submission_id = absint( $submission_id );

        if( $submission_id !== 0 ) {
            ct_delete_object( $submission_id, true );
        }
    }

    if( $table_changed ) {
        ct_reset_setup_table();
    }

}

/**
 * Reset form submission
 *
 * @since 1.0.0
 *
 * @param int $form_id The form id
 */
function bbforms_reset_submissions( $form_id ) {

    global $wpdb;

    $form_id = absint( $form_id );

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Query search
    $submissions_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT s.id
        FROM {$ct_table->db->table_name} AS s
        WHERE s.form_id = %d",
        $form_id,
    ) );

    // Delete submissions (parsing hooks for extra removals)
    bbforms_submissions_delete( $submissions_ids );

    ct_reset_setup_table();

}

/**
 * Helper function to get a submission meta
 *
 * @since 1.0.0
 *
 * @param int       $submission_id
 * @param string    $meta_key
 * @param bool      $single
 *
 * @return string
 */
function bbforms_get_submission_meta( $submission_id, $meta_key, $single = false ) {

    global $wpdb, $ct_table;

    $submission_id = absint( $submission_id );

    if( $submission_id === 0 ) {
        return '';
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Get the meta value
    $meta_value = ct_get_object_meta( $submission_id, $meta_key, $single );

    ct_reset_setup_table();

    return $meta_value;

}

/**
 * Helper function to update a submission meta
 *
 * @since 1.0.0
 *
 * @param int       $submission_id
 * @param string    $meta_key
 * @param mixed     $meta_value
 *
 * @return int|bool
 */
function bbforms_update_submission_meta( $submission_id, $meta_key, $meta_value ) {

    global $wpdb, $ct_table;

    $submission_id = absint( $submission_id );

    if( $submission_id === 0 ) {
        return false;
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Get the meta value
    $result = ct_update_object_meta( $submission_id, $meta_key, $meta_value );

    ct_reset_setup_table();

    return $result;

}