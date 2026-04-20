<?php
/**
 * Custom Tables
 *
 * @package     ShortLinksPro\Custom_Tables
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Custom Tables
require_once SHORTLINKSPRO_DIR . 'includes/custom-tables/links.php';
require_once SHORTLINKSPRO_DIR . 'includes/custom-tables/link-categories.php';
require_once SHORTLINKSPRO_DIR . 'includes/custom-tables/link-tags.php';
require_once SHORTLINKSPRO_DIR . 'includes/custom-tables/clicks.php';

/**
 * Register all custom database Tables
 *
 * @since   1.0.0
 *
 * @return void
 */
function shortlinkspro_register_custom_tables() {

    // Links
    ct_register_table( 'shortlinkspro_links', array(
        'show_ui' => true,
        'show_in_rest' => true,
        'rest_base' => 'shortlinkspro-links',
        'version' => 1,
        'capability' => shortlinkspro_get_manager_capability(),
        'supports' => array( 'meta' ),
        'views' => array(
            'list' => array(
                'parent_slug' => 'shortlinkspro',
                'priority' => 10,
            ),
            'add' => array(
                'parent_slug' => 'shortlinkspro',
                'priority' => 10,
            ),
        ),
        'schema' => array(
            'id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'title' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'url' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'slug' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'redirect_type' => array(
                'type' => 'text',
                'length' => '50',
            ),
            'nofollow' => array(
                'type' => 'tinyint',
                'length' => '1',
            ),
            'sponsored' => array(
                'type' => 'tinyint',
                'length' => '1',
            ),
            'parameter_forwarding' => array(
                'type' => 'tinyint',
                'length' => '1',
            ),
            'tracking' => array(
                'type' => 'tinyint',
                'length' => '1',
            ),
            'author_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'created_at' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
            'updated_at' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
        ),
    ) );

    // Clicks
    ct_register_table( 'shortlinkspro_clicks', array(
        'show_ui' => true,
        'show_in_rest' => true,
        'rest_base' => 'shortlinkspro-clicks',
        'version' => 1,
        'capability' => shortlinkspro_get_manager_capability(),
        'supports' => array( 'meta' ),
        'views' => array(
            'list' => array(
                'parent_slug' => 'shortlinkspro',
                'priority' => 10,
            ),
            'add' => false,
        ),
        'schema' => array(
            'id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'link_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'ip' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'browser' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'browser_version' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'browser_type' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'os' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'os_version' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'device' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'bot' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'country' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'user_agent' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'referrer' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'uri' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'url' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'redirect_type' => array(
                'type' => 'text',
                'length' => '255',
                'key' => true,
            ),
            'parameters' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'visitor_id' => array(
                'type' => 'text',
                'length' => '20',
                'key' => true,
            ),
            'first_click' => array(
                'type' => 'tinyint',
                'length' => '1',
                'key' => true,
            ),
            'created_at' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
        ),
    ) );

    // Categories
    ct_register_taxonomy_table( 'shortlinkspro_link_categories', 'shortlinkspro_links', array(
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rest_base' => 'shortlinkspro-link-categories',
        'version' => 1,
        'capability' => shortlinkspro_get_manager_capability(),
        'supports' => array( 'meta' ),
        'views' => array(
            'list' => array(
                'parent_slug' => 'shortlinkspro',
                'priority' => 10,
                'add_form' => true,
            ),
            'add' => array(
                'show_in_menu' => false,
            ),
        ),
        'schema' => array(
            'id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'name' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'slug' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'description' => array(
                'type' => 'text',
                'length' => '255',
            ),
        ),
        'relationship' => array(
            'type' => 'single',
            'term_id' => 'link_category_id',
            'object_id' => 'link_id',
        ),
    ) );

    // Tags
    ct_register_taxonomy_table( 'shortlinkspro_link_tags', 'shortlinkspro_links', array(
        'hierarchical' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'rest_base' => 'shortlinkspro-link-tags',
        'version' => 1,
        'capability' => shortlinkspro_get_manager_capability(),
        'supports' => array( 'meta' ),
        'views' => array(
            'list' => array(
                'parent_slug' => 'shortlinkspro',
                'priority' => 10,
                'add_form' => true,
            ),
            'add' => array(
                'show_in_menu' => false,
            ),
        ),
        'schema' => array(
            'id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'name' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'slug' => array(
                'type' => 'text',
                'length' => '255',
            ),
            'description' => array(
                'type' => 'text',
                'length' => '255',
            ),
        ),
        'relationship' => array(
            'type' => 'multiple',
            'term_id' => 'link_tag_id',
            'object_id' => 'link_id',
        ),
    ) );

}
add_action( 'ct_init', 'shortlinkspro_register_custom_tables' );

/**
 * Helper function to generate a where from a CT Query
 *
 * @since 1.0.0
 *
 * @param array     $query_vars         The query vars
 * @param string    $field_id           The field id
 * @param string    $table_field        The table field key
 * @param string    $field_type         The field type (string|integer)
 * @param string    $single_operator    The single operator (=|!=)
 * @param string    $array_operator     The array operator (IN|NOT IN)
 *
 * @return string
 */
function shortlinkspro_custom_table_where( $query_vars, $field_id, $table_field = '', $field_type = 'string', $single_operator = '=', $array_operator = 'IN' ) {

    global $ct_table;

    $table_name = $ct_table->db->table_name;

    $where = '';

    // Backward compatibility for shortlinkspro_custom_table_where( $query_vars, $field_id, $field_type )
    if( in_array( $table_field, array( 'string', 'integer', 'text', 'int' ) ) ) {
        $table_field = $field_id;
    }

    // Shorthand
    $qv = $query_vars;

    // Type
    if( isset( $qv[$field_id] ) && ! empty( $qv[$field_id] ) ) {

        if( is_array( $qv[$field_id] ) ) {
            // Multiples values

            if( $field_type === 'string' || $field_type === 'text' ) {

                // Sanitize
                $value = array_map( 'esc_sql', $qv[$field_id] );

                // Join values by a comma-separated list of strings
                $value = "'" . implode( "', '", $value ) . "'";

                $where .= " AND {$table_name}.{$table_field} {$array_operator} ( {$value} )";

            } else if( $field_type === 'integer' || $field_type === 'int' ) {

                // Sanitize
                $value = array_map( 'absint', $qv[$field_id] );

                // Join values by a comma-separated list of integers
                $value = "'" . implode( ", ", $value ) . "'";

                $where .= " AND {$table_name}.{$table_field} {$array_operator} ( {$value} )";

            }
        } else {
            // Single value

            if( $field_type === 'string' || $field_type === 'text' ) {

                $value = esc_sql( $qv[$field_id] );

                $where .= " AND {$table_name}.{$table_field} {$single_operator} '{$value}'";

            } else if( $field_type === 'integer' || $field_type === 'int' ) {

                $value = absint( $qv[$field_id] );

                $where .= " AND {$table_name}.{$table_field} {$single_operator} {$value}";

            }
        }

    }

    return $where;

}

/**
 * Helper function to clone an item metas
 *
 * @since 1.0.0
 *
 * @param string    $table              The custom table name
 * @param int       $old_id             The item id to get the metas from
 * @param int       $new_id             The item id to add the metas
 * @param array    $excluded_meta_keys  Array with meta keys to exclude
 */
function shortlinkspro_clone_item_metas( $table, $old_id, $new_id, $excluded_meta_keys = array() ) {

    global $wpdb;

    $ct_table = ct_setup_table( $table );
    $metas = array();

    // Get all item metas
    $item_metas = $wpdb->get_results( "SELECT meta_key, meta_value FROM {$ct_table->meta->db->table_name} WHERE id = {$old_id}", ARRAY_A );

    foreach( $item_metas as $i => $item_meta ) {

        $meta_key = $item_metas[$i]['meta_key'];

        if( in_array( $meta_key, $excluded_meta_keys ) ) {
            continue;
        }

        /**
         * Filter to exclude a meta on clone this item
         * $table: table name
         * $meta_key: The meta key
         *
         * @since  1.0.0
         *
         * @param bool $exclude
         *
         * @return bool
         */
        $exclude = apply_filters( "shortlinkspro_clone_{$table}_meta_{$meta_key}_excluded", false );

        // Skip if meta gets excluded on clone
        if( $exclude ) {
            continue;
        }

        // Replace metas with old IDs with the new ones
        $meta_value = $item_metas[$i]['meta_value'];

        // Prepare for the upcoming insert
        $metas[] = $wpdb->prepare( '%d, %s, %s', array( $new_id, $meta_key, $meta_value ) );
    }

    if( count( $metas ) ) {
        $metas = implode( '), (', $metas );

        // Run a single query to insert all metas instead of insert them one-by-one
        $wpdb->query( "INSERT INTO {$ct_table->meta->db->table_name} (id, meta_key, meta_value) VALUES ({$metas})" );
    }

    ct_reset_setup_table();

}