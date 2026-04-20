<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\BBForms\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting forms
 *
 * @since 1.0.0
 */
function automatorwp_bbforms_ajax_get_forms() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $results = array();

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_forms' );

    $forms = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, title
        FROM {$ct_table->db->table_name}"
    ) );

    ct_reset_setup_table();
    
    foreach( $forms as $form ) {

        if( $form->title === '' ) $form->title = '(no title)';

        $results[] = array(
            'id' => $form->id,
            'text' => $form->title,
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_bbforms_get_forms', 'automatorwp_bbforms_ajax_get_forms', 5 );
