<?php
/**
 * Plugin Importer
 *
 * @package     ShortLinksPro\Classes\Plugin_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class ShortLinksPro_Plugin_Importer {

    // Override

    /**
     * Plugin
     *
     * @since 1.0.0
     *
     * @var string $plugin
     */
    public $plugin = '';

    /**
     * Args
     *
     * @since 1.0.0
     *
     * @var array $args
     */
    public $args = array(
        'label' => '',
        'supports' => array( 'links', 'link_categories', 'link_tags', 'clicks' ),
    );

    // Internal use

    /**
     * Tables
     *
     * @since 1.0.0
     *
     * @var array $tables
     */
    public $tables = array(
        'links' => 'shortlinkspro_links',
        'clicks' => 'shortlinkspro_clicks',
        'link_categories' => 'shortlinkspro_link_categories',
        'link_tags' => 'shortlinkspro_link_tags'
    );

    /**
     * Current Table
     *
     * @since 1.0.0
     *
     * @var string $table
     */
    public $table = '';

    /**
     * Labels
     *
     * @since 1.0.0
     *
     * @var array $labels
     */
    public $labels = array();

    /**
     * Cache
     *
     * @since 1.0.0
     *
     * @var array $cache
     */
    public $cache = array();

    public function __construct() {

        $this->hooks();

    }

    public function pre_init() {

        $this->labels = array(
            'links' => __( 'Links', 'shortlinkspro' ),
            'link_categories' => __( 'Categories', 'shortlinkspro' ),
            'link_tags' => __( 'Tags', 'shortlinkspro' ),
            'clicks' => __( 'Clicks', 'shortlinkspro' ),
        );

    }

    public function init() {
        // Override
    }

    public function hooks() {

        // Init
        add_filter( 'init', array( $this, 'pre_init' ) );
        add_filter( 'init', array( $this, 'init' ) );

        // Register Plugin
        add_filter( 'shortlinkspro_import_from_plugin_plugins', array( $this, 'register' ) );

        // Import filtering
        add_filter( 'shortlinkspro_import_from_plugin_result', array( $this, 'run_import' ), 10, 4 );

    }

    /**
     * Register the plugin importer
     *
     * @param array $plugins
     *
     * @return array
     */
    public function register( $plugins ) {

        $plugins[$this->plugin] = $this->args;

        return $plugins;

    }

    /**
     * Check if should run the import and runs it
     *
     * @param array $result
     * @param string $plugin
     * @param string $group
     * @param int $loop
     *
     * @return array
     */
    public function run_import( $result, $plugin, $group, $loop ) {

        if( $plugin !== $this->plugin ) {
            return $result;
        }

        // Success false is set by default
        if( ! isset( $this->args[$group] ) ) {
            return $result;
        }

        // Used for internal functions
        $this->table = $group;

        $success = true;
        $imported = $this->import( $group, $loop );
        $count = $this->get_count( $group );

        // Prevent importer higher than count
        if( $imported > $count ) {
            $imported = $count;
        }

        // Setup message
        if( $count === 0 ) {
            /* translators: %s: Label */
            $result['message'] = sprintf( __( 'No %s found.', 'shortlinkspro' ), $this->labels[$this->table] );
        } else if( $imported === $count ) {
            /* translators: %s: Label */
            $result['message'] = sprintf( __( '%s import done!', 'shortlinkspro' ), $this->labels[$this->table] );
        } else {
            /* translators: %1$s: Label. %2$d: Number of imported links. %3$d: Number of total links  */
            $result['message'] = sprintf( __( 'Importing %1$s: %2$d/%3$d', 'shortlinkspro' ), $this->labels[$this->table], $imported, $count );
        }

        // Run again flag
        if( $imported < $count ) {
            $result['run_again'] = true;
        }

        $result['success'] = $success;

        return $result;

    }

    public function get_sql( $group ) {

    }

    /**
     * Get the total count of entries to import
     *
     * @param string $group
     *
     * @return int
     */
    public function get_count( $group ) {

        global $wpdb;

        $args = $this->args[$group];
        $table = isset( $args['table'] ) ? $args['table'] : '';
        $join_table = isset( $args['join_table'] ) ? $args['join_table'] : '';
        $join_on = isset( $args['join_on'] ) ? $args['join_on'] : '';

        if( empty( $table ) ) {
            return 0;
        }

        $id_field = isset( $args['id_field'] ) ? $args['id_field'] : '';
        $where = isset( $args['where'] ) ? $args['where'] : '';

        if( empty( $id_field ) ) {
            $where = '*';
        }

        if( empty( $where ) ) {
            $where = '1=1';
        }

        if( ! empty( $join_table ) && ! empty( $join_on ) ) {
            // Query count + JOIN
            $sql = "SELECT COUNT({$table}.{$id_field}) 
                    FROM {$wpdb->prefix}{$table} AS {$table}
                    LEFT JOIN {$wpdb->prefix}{$join_table} AS {$join_table} ON ( {$join_on} )
                    WHERE {$where}";
        } else {
            // Query count
            $sql = "SELECT COUNT({$id_field}) FROM {$wpdb->prefix}{$table} WHERE {$where}";
        }

        $count = absint( $wpdb->get_var( $sql ) );

        return $count;

    }

    /**
     * Runs the import
     *
     * @param string $group
     * @param int $loop
     *
     * @return int
     */
    public function import( $group, $loop ) {
        global $wpdb, $current_entry;

        // Pagination args
        $limit = 50; // LIMIT
        $offset = $limit * $loop;

        $args = $this->args[$group];
        $table = isset( $args['table'] ) ? $args['table'] : '';
        $join_table = isset( $args['join_table'] ) ? $args['join_table'] : '';
        $join_on = isset( $args['join_on'] ) ? $args['join_on'] : '';

        // Bail if not defined the table from
        if( empty( $table ) ) {
            return 0;
        }

        // Bail if not defined the table to
        if( ! isset( $this->tables[$group] ) ) {
            return 0;
        }

        $to_table = $this->tables[$group];

        $id_field = isset( $args['id_field'] ) ? $args['id_field'] : '';
        $where = isset( $args['where'] ) ? $args['where'] : '';

        if( empty( $where ) ) {
            $where = '1=1';
        }

        if( ! empty( $join_table ) && ! empty( $join_on ) ) {
            // Query entries + JOIN
            $sql = "SELECT *
                    FROM {$wpdb->prefix}{$table} AS {$table}
                    LEFT JOIN {$wpdb->prefix}{$join_table} AS {$join_table} ON ( {$join_on} )
                    WHERE {$where}
                    LIMIT {$offset}, {$limit}";
        } else {
            // Query entries
            $sql = "SELECT * FROM {$wpdb->prefix}{$table} WHERE {$where} LIMIT {$offset}, {$limit}";
        }

        $entries = $wpdb->get_results( $sql, ARRAY_A );

        // Bail if no more entries found
        if( $entries === null ) {
            return $offset;
        }

        // Import entries
        foreach( $entries as $entry ) {
            $current_entry = $entry;
            $this->import_entry( $entry, $group, $args );
        }

        return $offset;

    }

    /**
     * Imports an entry
     *
     * @param array $entry
     * @param string $group
     * @param array $args
     *
     * @return void
     */
    function import_entry( $entry, $group, $args ) {

        global $wpdb;

        $id_field = isset( $args['id_field'] ) ? $args['id_field'] : '';
        $to_table = $this->tables[$group];

        // Parse fields
        $fields = isset( $args['fields'] ) ? $args['fields'] : array();

        if( $id_field !== '' ) {
            $fields['import_id'] = array(
                'from' => $id_field, // Used to meet from which ID comes from
            );
        }

        $fields['import_from'] = array(
            'default' => $this->plugin, // Used to meet from which ID comes from
        );

        // Setup table
        $ct_table = ct_setup_table( $to_table );
        $table_schema = $ct_table->db->schema->fields;

        // Prepare data
        $object_data = array(); // field => value
        $object_metas = array(); // key => value
        $unique_field = '';
        $unique_value = '';

        // Parse each field
        foreach( $fields as $field => $field_args ) {
            // Get the value
            $value = $this->get_field_value( $entry, $field, $field_args );

            $unique = isset( $field_args['unique'] ) ? (bool) $field_args['unique'] : false;

            if( $unique ) {
                $unique_field = $field;
                $unique_value = $value;
            }

            // Check if field is an object field or a meta field
            if( isset( $table_schema[$field] ) ) {
                $object_data[$field] = $value;
            } else {
                $object_metas[$field] = $value;
            }

        }

        // Get metas from meta_table
        if( ! empty( $args['meta_table'] )
            && ! empty( $args['meta_relationship_id_field'] )
            && ! empty( $args['metas'] )
            && ! empty( $id_field ) ) {

            $meta_table = $args['meta_table'];
            $relationship_id_field = $args['meta_relationship_id_field'];
            $meta_key_field = isset( $args['meta_key_field'] ) ? $args['meta_key_field'] : 'meta_key';
            $meta_value_field = isset( $args['meta_value_field'] ) ? $args['meta_value_field'] : 'meta_value';
            $meta_keys = array();

            // By default relate with the ID field
            $entry_id_field = $id_field;

            // Check if the relation needs to go to another field
            if( isset( $args['meta_relationship_entry_id_field'] )
                && ! empty( $args['meta_relationship_entry_id_field'] ) ) {
                $entry_id_field = $args['meta_relationship_entry_id_field'];
            }

            foreach( $args['metas'] as $field => $field_args ) {
                if( isset( $field_args['from'] ) ) {
                    $meta_keys[] = $field_args['from'];
                }
            }

            $sql = "
                SELECT * 
                FROM {$wpdb->prefix}{$meta_table} 
                WHERE {$relationship_id_field} = {$entry[$entry_id_field]}
                AND {$meta_key_field} IN( '" . implode( "','", $meta_keys ) . "' )
                ";
            $metas = $wpdb->get_results( $sql, ARRAY_A );

            if( $metas !== null ) {

                // Create a false entry object for function get_field_value()
                $meta_entry = array();

                foreach( $metas as $meta ) {

                    $key = $meta[$meta_key_field];
                    $value = $meta[$meta_value_field];

                    if( isset( $meta_entry[$key] ) ) {
                        // Multiple meta
                        if( ! is_array( $meta_entry[$key] ) ) {
                            $meta_entry[$key] = array(
                                $meta_entry[$key]
                            );
                        }

                        $meta_entry[$key][] = $value;
                    } else {
                        // Single meta
                        $meta_entry[$key] = $value;
                    }

                }

                foreach( $args['metas'] as $field => $field_args ) {
                    // Get the value
                    $value = $this->get_field_value( $meta_entry, $field, $field_args );

                    // Skip field to being saved
                    if( isset( $field_args['save'] ) && $field_args['save'] === false ) {
                        continue;
                    }

                    // Check if field is an object field or a meta field
                    if( isset( $table_schema[$field] ) ) {
                        $object_data[$field] = $value;
                    } else {
                        $object_metas[$field] = $value;
                    }
                }
            }

        }

        $object_id = 0;
        $update = false;

        // If entry already imported to update it
        if( $id_field !== '' ) {
            $found = $this->find_imported_object( $this->tables[$this->table], $entry[$id_field] );

            if( $found !== 0 ) {
                // Add the ID found to the object to update it
                $object_id = $found;
                $object_data[$ct_table->db->primary_key] = $found;
                $update = true;
            }
        }

        // If entry has a unique field, find if already exists
        if( ! $update && ! empty( $unique_field ) && ! empty( $unique_value ) ) {
            $found = $this->find_unique_object( $unique_field, $unique_value );

            if( $found !== 0 ) {
                // Add the ID found to the object to update it
                $object_id = $found;
                $object_data[$ct_table->db->primary_key] = $found;
                $update = true;
            }
        }

        if( $update ) {
            // UPDATE

            // Update the object
            ct_update_object( $object_data );

            // Delete specific object metas
            $this->delete_object_metas( $object_data, $object_metas, $ct_table );

            // Insert multiple object metas
            $this->insert_object_metas( $object_data, $object_metas, $ct_table );
        } else {

            // INSERT

            // Insert the object
            $object_id = ct_insert_object( $object_data );

            if( $object_id ) {
                $object_data[$ct_table->db->primary_key] = $object_id;

                // Insert multiple object metas
                $this->insert_object_metas( $object_data, $object_metas, $ct_table );
            }
        }

        // Update relationships
        if( $object_id
            && isset( $args['relationship'] ) && is_array( $args['relationship'] )
            && isset( $args['relationship']['table'] )
            && isset( $args['relationship']['term_field'] )
            && isset( $args['relationship']['object_field'] )
            && isset( $args['relationship']['object_table'] ) ) {

            // Get relationships
            $relationship_table = $args['relationship']['table'];
            $term_field = $args['relationship']['term_field'];
            $object_field = $args['relationship']['object_field'];
            $term_id = $object_id;

            $sql = "
                SELECT {$object_field}
                FROM {$wpdb->prefix}{$relationship_table} 
                WHERE {$term_field} = {$entry[$id_field]}
                ";
            $relationships = $wpdb->get_results( $sql, ARRAY_A );

            // Update them
            $object_table = $args['relationship']['object_table'];

            foreach( $relationships as $relationship ) {
                // Check if object already imported
                $found = $this->find_imported_object( $this->tables[$object_table], $relationship[$object_field] );

                if( $found ) {

                    // Update relationships
                    $ct_table = ct_setup_table( $this->tables[$this->table] . '_relationships' );

                    // Check if relationship already exists
                    $relationship_found = ct_get_relationship_id( $found, $term_id );

                    if( ! $relationship_found ) {
                        // Add relationship if not exists
                        ct_add_object_relationship( $found, $term_id );
                    }

                    ct_reset_setup_table();

                }

            }
        }

        ct_reset_setup_table();

    }

    /**
     * Get field value
     *
     * @param array $entry
     * @param string $field
     * @param array $field_args
     *
     * @return mixed
     */
    function get_field_value( $entry, $field, $field_args ) {

        // Setup field args
        $from = isset( $field_args['from'] ) ? $field_args['from'] : '';
        $relationship = isset( $field_args['relationship'] ) ? $field_args['relationship'] : '';

        // Get the value from the entry
        if( $from !== '' && isset( $entry[$from] ) && ! empty( $entry[$from] ) ) {
            $value = $entry[$from];
        } else {
            $value = $this->get_default( $entry, $field, $field_args );
        }

        $value = $this->sanitize( $value, $entry, $field, $field_args );

        // Get from relationship
        if( ! empty( $relationship ) && isset( $this->tables[$relationship] ) ) {
            $found = $this->find_imported_object( $this->tables[$relationship], $value );

            if( $found ) {
                $value = $found;
            }
        }

        return $value;

    }

    /**
     * Get field default value from default & default_cb
     *
     * @param array $entry
     * @param string $field
     * @param array $field_args
     *
     * @return mixed
     */
    function get_default( $entry, $field, $field_args ) {

        $default = '';

        if( isset( $field_args['default'] ) ) {
            $default = $field_args['default'];
        }

        if( isset( $field_args['default_cb'] ) && is_callable( $field_args['default_cb'] ) ) {
            $default = call_user_func( $field_args['default_cb'], $default, $entry, $field, $field_args );
        }

        return $default;

    }

    /**
     * Sanitize field from sanitize & sanitize_cb
     *
     * @param array $entry
     * @param string $field
     * @param array $field_args
     *
     * @return mixed
     */
    function sanitize( $value, $entry, $field, $field_args ) {

        $sanitized_value = $value;

        if( isset( $field_args['sanitize'] ) ) {
            switch( $field_args['sanitize'] ) {
                case 'sanitize_text_field':
                case 'text':
                    $sanitized_value = sanitize_text_field( $value );
                    break;
                case 'absint':
                case 'int':
                    $sanitized_value = absint( $value );
                    break;
            }
        }

        if( isset( $field_args['sanitize_cb'] ) && is_callable( $field_args['sanitize_cb'] ) ) {
            $sanitized_value = call_user_func( $field_args['sanitize_cb'], $value, $entry, $field, $field_args );
        }

        return $sanitized_value;

    }

    /**
     * Find object by "import_id" & "import_from" metas
     *
     * @param string $table
     * @param int $object_id
     *
     * @return int
     */
    function find_imported_object( $table, $object_id ) {

        global $wpdb, $ct_table;

        $cache_key = "find_imported_object_{$table}_{$object_id}";

        if( isset( $this->cache[$cache_key] ) ) {
            return $this->cache[$cache_key];
        }

        $ct_table = ct_setup_table( $table );

        // Find object by "import_id" & "import_from" metas
        $sql = "
                SELECT t.{$ct_table->db->primary_key} 
                FROM {$ct_table->db->table_name} t
                LEFT JOIN {$ct_table->meta->db->table_name} m1 ON ( t.{$ct_table->db->primary_key} = m1.{$ct_table->db->primary_key} )
                LEFT JOIN {$ct_table->meta->db->table_name} m2 ON ( t.{$ct_table->db->primary_key} = m2.{$ct_table->db->primary_key} )
                WHERE 1=1
                    AND ( m1.meta_key = 'import_id' AND m1.meta_value = '{$object_id}' ) 
                    AND ( m2.meta_key = 'import_from' AND m2.meta_value = '{$this->plugin}' ) 
                LIMIT 1
            ";
        $found = absint( $wpdb->get_var( $sql ) );

        if( $found ) {
            $this->cache[$cache_key] = $found;
        }

        ct_reset_setup_table();

        return $found;

    }

    /**
     * Find a unique object by a specific field value
     *
     * @param string $unique_field
     * @param string $unique_value
     *
     * @return int
     */
    function find_unique_object( $unique_field, $unique_value ) {

        global $wpdb, $ct_table;

        $cache_key = "find_unique_object_{$ct_table->name}_{$unique_field}_{$unique_value}";

        if( isset( $this->cache[$cache_key] ) ) {
            return $this->cache[$cache_key];
        }

        // Find object by unique field value
        $sql = "
                SELECT {$ct_table->db->primary_key} 
                FROM {$ct_table->db->table_name}
                WHERE {$unique_field} = '{$unique_value}'
                LIMIT 1
            ";

        $found = absint( $wpdb->get_var( $sql ) );

        if( $found ) {
            $this->cache[$cache_key] = $found;
        }

        return $found;

    }

    /**
     * Insert multiple object metas
     *
     * @param array $object_data
     * @param array $object_metas
     * @param CT_Table $ct_table
     *
     * @return void
     */
    function insert_object_metas( $object_data, $object_metas, $ct_table ) {

        global $wpdb;

        if( ! in_array( 'meta', $ct_table->supports ) ) {
            return;
        }

        if( empty( $object_metas ) ) {
            return;
        }

        $metas = array();

        foreach( $object_metas as $meta_key => $meta_value ) {
            $original_meta_key = $meta_key;
            $original_meta_value = $meta_value;

            // Sanitize vars
            $meta_key = sanitize_key( $meta_key );
            $meta_key = wp_unslash( $meta_key );
            $meta_value = wp_unslash( $meta_value );
            $meta_value = esc_sql( $meta_value );
            $meta_value = sanitize_meta( $meta_key, $meta_value, $ct_table->name );
            $meta_value = maybe_serialize( $meta_value );

            /**
             * Filter to override the meta value
             *
             * @since 1.0.0
             *
             * @param string    $meta_value             The parsed meta value
             * @param string    $meta_key               The parsed meta key
             * @param string    $original_meta_value    The original meta value
             * @param string    $original_meta_key      The original meta key
             * @param array     $object_data            The object data
             * @param array     $object_metas           The original log metas
             *
             * @return mixed
             */
            $meta_value = apply_filters( 'shortlinkspro_plugin_importer_insert_meta_value', $meta_value, $meta_key, $original_meta_value, $original_meta_key, $object_data, $object_metas );

            // Setup the insert value
            $metas[] = $wpdb->prepare( '%d, %s, %s', array( $object_data[$ct_table->db->primary_key], $meta_key, $meta_value ) );
        }

        $metas = implode( '), (', $metas );

        // Is faster to run a single query to insert all metas instead of insert them one-by-one
        $wpdb->query( "INSERT INTO {$ct_table->meta->db->table_name} (id, meta_key, meta_value) VALUES ({$metas})" );

    }

    /**
     * Delete all object metas
     *
     * @param array $object_data
     * @param array $object_metas
     * @param CT_Table $ct_table
     *
     * @return void
     */
    function delete_object_metas( $object_data, $object_metas, $ct_table ) {

        global $wpdb;

        if( ! in_array( 'meta', $ct_table->supports ) ) {
            return;
        }

        if( empty( $object_metas ) ) {
            return;
        }

        $meta_keys = array_keys( $object_metas );

        if( empty( $meta_keys ) ) {
            return;
        }

        $wpdb->query( "
                DELETE FROM {$ct_table->meta->db->table_name} 
                WHERE id = {$object_data[$ct_table->db->primary_key]} 
                AND meta_key IN ('" . implode( "','", $meta_keys ) . "')" );

    }

    // UTILITIES

    /**
     * Helper function to pass array to string
     *
     * @param $array
     *
     * @return string
     */
    function array_to_string( $array ) {

        $string = '';

        foreach( $array as $key => $value ) {
            $string .= $key . '=' . $value . ', ';
        }

        return $string;

    }

}