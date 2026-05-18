<?php
/**
 * Import From Plugin Tool
 *
 * @package     ShortLinksPro\Admin\Settings\Import_From_Plugin_Tool
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin/plugin-importer.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin/affiliate-links-importer.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin/betterlinks-importer.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin/easy-affiliate-links-importer.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin/prettylinks-importer.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin/thirstyaffiliates-importer.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/tools/import-from-plugin/url-shortify-importer.php';




/**
 * General Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function shortlinkspro_tools_import_from_plugin_meta_boxes( $meta_boxes ) {

    $plugins = apply_filters( 'shortlinkspro_import_from_plugin_plugins', array() );

    $options = array(
        '' => 'none'
    );
    $attr = array();

    foreach( $plugins as $plugin => $args ) {
        $options[$plugin] = $args['label'];
        $attr['data-' . $plugin] = implode( ',', $args['supports'] );
    }

    $meta_boxes['import_from_plugin_tools'] = array(
        'title' => shortlinkspro_dashicon( 'database' ) . __( 'Migrate From Plugin', 'shortlinkspro' ),
        'fields' => apply_filters( 'shortlinkspro_import_from_plugin_tools_fields', array(
            'import_from_plugin' => array(
                'name'      => __( 'Import From', 'shortlinkspro' ),
                'desc'      => __( 'It is not necessary for the plugin to be active, ShortLinks Pro will import the data from the database directly.', 'shortlinkspro' ),
                'type'      => 'radio',
                'classes'   => 'cmb2-switch',
                'options'   => $options,
                'tooltip'   => __( 'Choose the plugin you want to import from. ', 'shortlinkspro' ),
                'label_cb'  => 'cmb_tooltip_label_cb',
                'default'   => '',
                'attributes' => $attr,
            ),
            'import_from_plugin_data' => array(
                'name'      => __( 'Data To Import', 'shortlinkspro' ),
                'type'      => 'multicheck',
                'options'   => array(
                    'links'             => __( 'Links', 'shortlinkspro' ),
                    'link_categories'   => __( 'Categories', 'shortlinkspro' ),
                    'link_tags'         => __( 'Tags', 'shortlinkspro' ),
                    'clicks'            => __( 'Clicks', 'shortlinkspro' ),
                ),
                'tooltip'   => __( 'Check the data you want to import. ', 'shortlinkspro' ),
                'label_cb'  => 'cmb_tooltip_label_cb',
                'classes' => 'cmb2-switch',
                'select_all_button' => false,
                'default' => array( 'links', 'clicks', 'link_categories', 'link_tags' ),
            ),
            'import_from_plugin_button' => array(
                'name'      => __( 'Start Import', 'shortlinkspro' ),
                'desc'      => __( 'You can run this tool as many times as you wish, it will always update the already imported data and will not create duplicates.', 'shortlinkspro' ),
                'type'      => 'title',
                'render_row_cb' => 'shortlinkspro_import_from_plugin_button',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'shortlinkspro_tools_general_meta_boxes', 'shortlinkspro_tools_import_from_plugin_meta_boxes' );

/**
 * Helper function to render the clicks cleanup button
 *
 * @param $field_args
 * @param $field
 */
function shortlinkspro_import_from_plugin_button( $field_args, $field ) {
    $id          = $field->args( 'id' );
    $label       = $field->args( 'name' );
    $desc        = $field->args( 'desc' );

    echo '<div class="cmb-row shortlinkspro-import-from-plugin-row">'
        . '<p>' . esc_html( $desc ) . '</p>'
        . '<button id="' . esc_attr( $id ) . '" type="button" class="button button-primary shortlinkspro-import-from-plugin-button">' . esc_html( $label ) . '</button>'
        . '<span class="shortlinkspro-tool-response shortlinkspro-import-from-plugin-error shortlinkspro-error-message" style="display: none;"></span>'
        . '<span class="spinner"></span>'
        . '<p class="shortlinkspro-import-from-plugin-response"></p>'
        . '</div>';
}

/**
 * Clicks cleanup through ajax
 *
 * @since   1.0.0
 */
function shortlinkspro_ajax_import_from_plugin() {
    global $wpdb;

    // Security check, forces to die if not security passed
    check_ajax_referer( 'shortlinkspro_admin', 'nonce' );

    // Permissions check
    if( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'shortlinkspro' ) );
    }

    // Sanitize parameters
    $plugin = sanitize_text_field( $_POST['plugin'] );
    $group = sanitize_text_field( $_POST['group'] );
    $loop = absint( $_POST['loop'] );

    $result = array(
        'success' => false,
        'run_again' => false,
        'message' => '',
    );

    /**
     * Filter to hook the import from the registered plugin
     *
     * @param array     $result
     * @param string    $plugin
     * @param string    $group
     * @param int       $loop
     *
     * @return array
     */
    $result = apply_filters( 'shortlinkspro_import_from_plugin_result', $result, $plugin, $group, $loop );

    if( $result['success'] === false ) {
        wp_send_json_error( __( 'Import failed.', 'shortlinkspro' ) );
    }


    wp_send_json_success( $result );
}
add_action( 'wp_ajax_shortlinkspro_import_from_plugin', 'shortlinkspro_ajax_import_from_plugin' );