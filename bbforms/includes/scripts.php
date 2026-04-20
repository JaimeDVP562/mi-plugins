<?php
/**
 * Scripts
 *
 * @package     BBForms\Scripts
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function bbforms_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'bbforms-css', BBFORMS_URL . 'assets/css/bbforms' . $suffix . '.css', array( ), BBFORMS_VER, 'all' );
    wp_register_style( 'bbforms-admin-bar-css', BBFORMS_URL . 'assets/css/bbforms-admin-bar' . $suffix . '.css', array( ), BBFORMS_VER, 'all' );

    // Scripts
    wp_register_script( 'bbforms-js', BBFORMS_URL . 'assets/js/bbforms' . $suffix . '.js', array( 'jquery' ), BBFORMS_VER, true );

}
add_action( 'init', 'bbforms_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function bbforms_enqueue_scripts( $hook = null ) {

    global $bbforms_scripts_enqueued;

    if( $bbforms_scripts_enqueued === true ) return;

    // Stylesheets
    wp_enqueue_style( 'bbforms-css' );

    // Scripts
    wp_localize_script( 'bbforms-js', 'bbforms', array(
        'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'                 => bbforms_get_nonce(),
        'error_messages'        => bbforms_get_error_messages_from_settings(),
        'form_messages'         => bbforms_get_form_messages_from_settings(),
    ) );

    wp_enqueue_script( 'bbforms-js' );

    do_action( 'bbforms_enqueue_scripts' );

    $bbforms_scripts_enqueued = true;

}
// Enqueued through shortcode
//add_action( 'wp_enqueue_scripts', 'bbforms_enqueue_scripts' );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function bbforms_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'bbforms-editor-css', BBFORMS_URL . 'assets/css/bbforms-editor' . $suffix . '.css', array( ), BBFORMS_VER, 'all' );
    wp_register_style( 'bbforms-admin-css', BBFORMS_URL . 'assets/css/bbforms-admin' . $suffix . '.css', array( ), BBFORMS_VER, 'all' );
    wp_register_style( 'bbforms-admin-rtl-css', BBFORMS_URL . 'assets/css/bbforms-admin-rtl' . $suffix . '.css', array( ), BBFORMS_VER, 'all' );
    wp_register_style( 'bbforms-flags-css', BBFORMS_URL . 'assets/lib/flags/flags.min.css', array( ), BBFORMS_VER, 'all' );

    // Scripts
    wp_register_script( 'bbforms-code-js', BBFORMS_URL . 'assets/js/bbforms-code' . $suffix . '.js', array( 'jquery' ), BBFORMS_VER, true );
    wp_register_script( 'bbforms-codemixed-js', BBFORMS_URL . 'assets/js/bbforms-codemixed' . $suffix . '.js', array( 'jquery' ), BBFORMS_VER, true );
    wp_register_script( 'bbforms-editor-js', BBFORMS_URL . 'assets/js/bbforms-editor' . $suffix . '.js', array( 'jquery', 'jquery-ui-dialog' ), BBFORMS_VER, true );
    wp_register_script( 'bbforms-admin-js', BBFORMS_URL . 'assets/js/bbforms-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-dialog' ), BBFORMS_VER, true );
    wp_register_script( 'bbforms-admin-notices-js', BBFORMS_URL . 'assets/js/bbforms-admin-notices' . $suffix . '.js', array( 'jquery' ), BBFORMS_VER, true );

}
add_action( 'admin_init', 'bbforms_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 *
 * @param string $hook
 *
 * @return      void
 */
function bbforms_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'bbforms-admin-css' );
    wp_enqueue_style( 'bbforms-admin-rtl-css' );
    wp_enqueue_style( 'bbforms-flags-css' );

    // Localize admin script
    wp_localize_script( 'bbforms-admin-notices-js', 'bbforms_admin_notices', array(
        'ajaxurl'   => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'     => bbforms_get_admin_nonce(),
    ) );

    // Scripts
    wp_enqueue_script( 'bbforms-admin-notices-js' );

    // Admin script
    wp_localize_script( 'bbforms-admin-js', 'bbforms_admin', array(
        'ajaxurl'                           => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'                             => bbforms_get_admin_nonce(),
        'show_text'                         => __( 'Show', 'bbforms' ),
        'hide_text'                         => __( 'Hide', 'bbforms' ),
        'show_attrs_text'                   => __( 'Show Attributes', 'bbforms' ),
        'hide_attrs_text'                   => __( 'Hide Attributes', 'bbforms' ),
        'show_examples_text'                   => __( 'Show Examples', 'bbforms' ),
        'hide_examples_text'                   => __( 'Hide Examples', 'bbforms' ),
        'copy_text'                         => __( 'Copy to clipboard', 'bbforms' ),
        'copied_text'                       => __( 'Copied!', 'bbforms' ),
        'import_form_text'                  => __( 'Import Form', 'bbforms' ),
        'importing_text'                    => __( 'Importing...', 'bbforms' ),
        'import_done_text'                  => __( 'Done! Redirecting...', 'bbforms' ),
        'export_as_csv_text'              => __( 'Export as CSV', 'bbforms' ),
        'exporting_text'                  => __( 'Exporting...', 'bbforms' ),
        'export_as_csv_done_text'         => __( 'Done!', 'bbforms' ),
        'export_as_csv_no_results_text'   => __( 'No submissions found.', 'bbforms' ),
    ) );

    wp_enqueue_script( 'bbforms-admin-js' );

    // Editor only in BBForms list & edit form screen
    if( in_array( $hook, array( 'bbforms_page_bbforms_forms', 'admin_page_edit_bbforms_forms' ) ) ) {
        bbforms_enqueue_editor();
    }

}
add_action( 'admin_enqueue_scripts', 'bbforms_admin_enqueue_scripts' );

function bbforms_enqueue_editor() {

    // Stylesheets
    wp_enqueue_style( 'bbforms-editor-css' );

    // Scripts
    $code_editor_settings = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

    $tags = array_keys( bbforms_get_all_tags() );

    foreach ( $tags as $i => $tag ) {
        $tags[$i] = '{' . $tag . '}';
    }

    $bbcodes = array_keys( bbforms_get_bbcodes() );

    // Sub-BBcodes
    $bbcodes[] = 'column';
    $bbcodes[] = 'tr';
    $bbcodes[] = 'td';

    $bbcodes = apply_filters( 'bbforms_editor_bbcodes', $bbcodes );

    // BBForms code
    $js_vars = array(
        'bbcodes'           => $bbcodes,
        'fields'            => array_keys( bbforms_get_fields() ),
        'actions'           => array_keys( bbforms_get_actions() ),
        'options'           => array_keys( bbforms_get_options() ),
        'tags'              => $tags,
        'editor_settings'   => $code_editor_settings,
    );

    wp_localize_script( 'bbforms-code-js', 'bbforms_code', $js_vars );
    wp_enqueue_script( 'bbforms-code-js' );

    wp_localize_script( 'bbforms-codemixed-js', 'bbforms_codemixed', $js_vars );
    wp_enqueue_script( 'bbforms-codemixed-js' );

    wp_localize_script( 'bbforms-editor-js', 'bbforms_editor', array(
        'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'                 => bbforms_get_admin_nonce(),
        'editor_settings'       => $code_editor_settings,
        'insert_text_here'      => __( 'Insert text here', 'bbforms' ),
        'preview'               => __( 'Preview', 'bbforms' ),
        // translators: %s: Field name
        'field_label_pattern'   => __( 'Field %s', 'bbforms' ),
        // translators: %s: Field name
        'field_value_pattern'   => __( 'Field %s value', 'bbforms' ),
        'show_preview'          => __( 'Show preview', 'bbforms' ),
        'hide_preview'          => __( 'Hide preview', 'bbforms' ),
        // translators: %d: Number
        'item_pattern'          => __( 'Item %d', 'bbforms' ),
        // translators: %d: Number
        'option_pattern'        => __( 'Option %d', 'bbforms' ),
        // translators: %d: Number
        'question_pattern'      => __( 'Question %d', 'bbforms' ),
        // translators: %d: Number
        'answer_pattern'        => __( 'Answer %d', 'bbforms' ),
        'submit_text'           => __( 'Submit', 'bbforms' ),
        'reset_text'            => __( 'Reset', 'bbforms' ),
        'data_request_pattern'  => __( 'Request registered successfully!', 'bbforms' ),
        'redirect_pattern'      => '{settings.submit_success_message} ' . __( 'Redirecting...', 'bbforms' ),
        'default_options'       => bbforms_get_options_code(),
    ) );

    wp_enqueue_script( 'bbforms-editor-js' );

    do_action( 'bbforms_enqueue_editor' );

}

/**
 * Register and enqueue admin bar scripts
 *
 * @since       1.3.2
 * @return      void
 */
function bbforms_enqueue_admin_bar_scripts() {

    wp_enqueue_style( 'bbforms-admin-bar-css' );

}
add_action( 'admin_bar_init', 'bbforms_enqueue_admin_bar_scripts' );

/**
 * Setup a global nonce for all frontend scripts
 *
 * @since       1.0.0
 *
 * @return      string
 */
function bbforms_get_nonce() {

    if( ! defined( 'BBFORMS_NONCE' ) )
        define( 'BBFORMS_NONCE', wp_create_nonce( 'bbforms' ) );

    return BBFORMS_NONCE;

}

/**
 * Setup a global nonce for all admin scripts
 *
 * @since       1.0.0
 *
 * @return      string
 */
function bbforms_get_admin_nonce() {

    if( ! defined( 'BBFORMS_ADMIN_NONCE' ) )
        define( 'BBFORMS_ADMIN_NONCE', wp_create_nonce( 'bbforms_admin' ) );

    return BBFORMS_ADMIN_NONCE;

}