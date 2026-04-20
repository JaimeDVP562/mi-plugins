<?php
/**
 * @package      RGC\CMB2\CMB2_Field_Select2
 * @author       Ruben Garcia (RubenGC) <rubengcdev@gmail.com>, GamiPress <contact@gamipress.com>
 * @copyright    Copyright (c) Tsunoa
 *
 * Plugin Name: CMB2 Field Select2
 * Plugin URI: https://github.com/rubengc/cmb2-field-js-controls
 * GitHub Plugin URI: https://github.com/rubengc/cmb2-field-js-controls
 * Description: Show any field similar to Wordpress publishing actions (Post/Page post_status, visibility and post_date submit box field).
 * Version: 1.0.2
 * Author: Tsunoa
 * Author URI: https://tsunoa.com/
 * License: GPLv2+
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Prevent CMB2 autoload adding "RGC_" at start
if( !class_exists( 'RGC_CMB2_Field_Select2' ) ) {

    /**
     * Class RGC_CMB2_Field_JS_Controls
     */
    class RGC_CMB2_Field_Select2 {

        /**
         * Current version number
         */
        const VERSION = '1.0.2';

        /**
         * Initialize the plugin by hooking into CMB2
         */
        public function __construct() {
            add_action( 'wp_ajax_cmb2_select2_get_results', array( $this, 'get_results' ) );
            add_filter( 'cmb2_select2_get_results_sql_posts', array( $this, 'get_results_sql_from_posts' ), 10, 2 );
            add_filter( 'cmb2_select2_get_results_sql_users', array( $this, 'get_results_sql_from_users' ), 10, 2 );
            add_filter( 'cmb2_select2_get_results_sql_terms', array( $this, 'get_results_sql_from_terms' ), 10, 2 );
            add_filter( 'cmb2_select2_get_results_sql_ct', array( $this, 'get_results_sql_from_ct' ), 10, 2 );


            add_filter( 'cmb2_render_select2', array( $this, 'render_select2' ), 10, 5 );
            add_filter( 'cmb2_render_multiselect2', array( $this, 'render_multiselect2' ), 10, 5 );
            add_filter( 'cmb2_sanitize_multiselect2', array( $this, 'multiselect2_sanitize' ), 10, 4 );
            add_filter( 'cmb2_types_esc_multiselect2', array( $this, 'multiselect2_escaped_value' ), 10, 3 );
            add_filter( 'cmb2_repeat_table_row_types', array( $this, 'multiselect2_table_row_class' ), 10, 1 );

            //add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ) );
        }

        public function get_results() {
            global $wpdb;

            // Security check, forces to die if not security passed
            check_ajax_referer( 'cmb2_select2', 'nonce' );

            // Pull back the search string
            $q = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

            $query_args = array(
                'from' => isset( $_REQUEST['from'] ) ? sanitize_text_field( $_REQUEST['from'] ) : '',
                'field_id' => isset( $_REQUEST['field_id'] ) ? sanitize_text_field( $_REQUEST['field_id'] ) : '',
                'q' => $q,
                // from = posts
                'post_type' => isset( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : '',
                'post_type_not_in' => isset( $_REQUEST['post_type_not_in'] ) ? sanitize_text_field( $_REQUEST['post_type_not_in'] ) : '',
                'post_status' => isset( $_REQUEST['post_status'] ) ? sanitize_text_field( $_REQUEST['post_status'] ) : '',
                'post_status_not_in' => isset( $_REQUEST['post_status_not_in'] ) ? sanitize_text_field( $_REQUEST['post_status_not_in'] ) : '',
                // from = users
                // Nothing
                // from = terms
                'taxonomy' => isset( $_REQUEST['taxonomy'] ) ? sanitize_text_field( $_REQUEST['taxonomy'] ) : '',
                // from = ct
                'table' => isset( $_REQUEST['table'] ) ? sanitize_text_field( $_REQUEST['table'] ) : '',
                'id_field' => isset( $_REQUEST['id_field'] ) ? sanitize_text_field( $_REQUEST['id_field'] ) : '',
                'text_field' => isset( $_REQUEST['text_field'] ) ? sanitize_text_field( $_REQUEST['text_field'] ) : '',
                'label_field' => isset( $_REQUEST['label_field'] ) ? sanitize_text_field( $_REQUEST['label_field'] ) : '',
            );

            $sql = $this->get_results_sql( $query_args );

            if( empty( $sql ) ) {
                wp_send_json_error( __( 'No results found.' ) );
            }

            // Pagination args
            $page = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
            $limit = 20;
            $offset = $limit * ( $page - 1 );

            // Escape the SQL query
            $sql = $wpdb->prepare( $sql, "%%{$q}%%" );

            // Query results
            $results = $wpdb->get_results( $sql . " LIMIT {$offset}, {$limit}" );

            // Count results
            $count = absint( $wpdb->get_var( $sql ) );

            $response = array(
                'results' => $results,
                'pagination' => array(
                    'more' => $count > $limit && $count > $offset,
                )
            );

            // Return our results
            wp_send_json_success( $response );
        }

        public function get_results_sql( $query_args ) {

            $sql = '';
            $from = $query_args['from'];
            $field_id = $query_args['field_id'];

            // Bail if there is no determine from where it comes
            if( empty( $from ) ) {
                return $sql;
            }

            $sql = apply_filters( 'cmb2_select2_get_results_sql', $sql, $query_args );
            $sql = apply_filters( "cmb2_select2_get_results_sql_{$from}", $sql, $query_args );
            $sql = apply_filters( "cmb2_select2_get_results_sql_{$from}_{$field_id}", $sql, $query_args );

            return $sql;

        }

        public function get_results_sql_from_posts( $sql, $query_args ) {

            global $wpdb;

            // Permissions check
            if( ! current_user_can( 'edit_posts' ) ) {
                return $sql;
            }

            // Select
            $sql = "SELECT p.ID as id, p.post_title as text";

            // Only add label if 0 o multiple post types
            $post_type = $this->parse_array_arg( $query_args['post_type'] );

            if( count( $post_type ) === 0 || count( $post_type ) > 1 ) {
                $sql .= ", p.post_type as label";
            }

            // From
            $sql .= " FROM {$wpdb->posts} AS p";

            // Apply search
            $q = $query_args['q'];

            $sql .= " WHERE 1=1";

            if ( ! empty( $q ) ) {
                $sql .= " AND p.post_title LIKE %s";
            }

            // Post type
            $sql .= $this->where_array( $post_type, 'p.post_type' );

            // Post type not in
            $post_type_not_in = $this->parse_array_arg( $query_args['post_type_not_in'] );

            // Default post types excluded
            $post_types_excluded = array(
                'attachment',
                'revision',
                'nav_menu_item',
                'custom_css',
                'customize_changeset',
                'user_request',
                'oembed_cache',
                'wp_block',
                'wp_template',
                // BuddyPress
                'bp-email',
            );

            // Compare if in post type there is any of the excluded post types to remove them from the exclusion
            $post_types_excluded = array_diff( $post_types_excluded, $post_type );

            $post_type_not_in = array_merge( $post_type_not_in, $post_types_excluded );

            $sql .= $this->where_array( $post_type_not_in, 'p.post_type', 'NOT IN' );

            // Post status
            $post_status = $this->parse_array_arg( $query_args['post_status'] );

            $sql .= $this->where_array( $post_status, 'p.post_status' );

            // Post status not in
            $post_status_not_in = $this->parse_array_arg( $query_args['post_status_not_in'] );

            // Default post types excluded
            $post_status_excluded = array(
                'pending',
                'draft',
            );

            $post_status_not_in = array_merge( $post_status_not_in, $post_status_excluded );

            $sql .= $this->where_array( $post_status_not_in, 'p.post_status', 'NOT IN' );

            // Order by
            $sql .= ' ORDER BY p.post_type ASC, p.menu_order DESC';

            return $sql;

        }

        public function get_results_sql_from_users( $sql, $query_args ) {

            global $wpdb;

            // Permissions check
            if( ! current_user_can( 'edit_users' ) ) {
                return $sql;
            }

            $sql = "SELECT ID as id, user_login as text, user_email as label FROM {$wpdb->users}";

            // Apply search
            $q = esc_sql( $query_args['q'] );

            if ( ! empty( $q ) ) {
                $sql .= " WHERE user_login LIKE '%{$q}%'";
                $sql .= " OR user_email LIKE '%{$q}%'";
                $sql .= " OR display_name LIKE '%{$q}%'";
            }

            return $sql;

        }

        public function get_results_sql_from_terms( $sql, $query_args ) {

            global $wpdb;

            // Permissions check
            if( ! current_user_can( 'edit_posts' ) ) {
                return $sql;
            }

            // Select
            $sql = "SELECT term.term_id as id, term.name as text";

            // Only add label if 0 o multiple taxonomies
            $taxonomy = $this->parse_array_arg( $query_args['taxonomy'] );

            if( count( $taxonomy ) === 0 || count( $taxonomy ) > 1 ) {
                $sql .= ", tax.taxonomy as label";
            }

            // From
            $sql .= " FROM {$wpdb->terms} AS term LEFT JOIN {$wpdb->term_taxonomy} AS tax ON ( tax.term_id = term.term_id )";

            // Apply search
            $q = $query_args['q'];

            $sql .= " WHERE 1=1";

            if ( ! empty( $q ) ) {
                $sql .= " AND term.name LIKE %s";
            }

            // Taxonomy
            $sql .= $this->where_array( $taxonomy, 'tax.taxonomy' );

            return $sql;

        }

        public function get_results_sql_from_ct( $sql, $query_args ) {

            global $wpdb;

            if( ! class_exists( 'CT' ) ) {
                return $sql;
            }

            $table = $query_args['table'];
            $id_field = $query_args['id_field'];
            $text_field = $query_args['text_field'];
            $label_field = $query_args['label_field'];

            if( empty( $table ) || empty( $id_field ) || empty( $text_field ) ) {
                return $sql;
            }

            $ct_table = ct_setup_table( $table );

            if( ! $ct_table ) {
                ct_reset_setup_table();
                return $sql;
            }

            $sql = "SELECT {$id_field} as id, {$text_field} as text";

            if( ! empty( $label_field ) ) {
                $sql .= ", {$label_field} as label";
            }
            $sql .= " FROM {$ct_table->db->table_name}";

            // Apply search
            if( ! empty( $query_args['q'] ) ) {
                $sql .= " WHERE {$text_field} LIKE %s";
            }

            ct_reset_setup_table();

            return $sql;

        }

        public function parse_array_arg( $value ) {

            if( $value === '' ) {
                $value = array();
            } else {
                $value = explode( ',', $value );
            }

            return $value;

        }

        public function where_array( $value, $field, $condition = 'IN', $sanitization = 'text' ) {

            if( count( $value ) ) {

                foreach( $value as $k => $v ) {
                    switch ( $sanitization ) {
                        case 'integer':
                        case 'int':
                            $value[$k] = absint( $v );
                            break;
                        default:
                            $value[$k] = esc_sql( sanitize_text_field( $v ) );
                            break;
                    }

                }

                if( $condition === 'IN' ) {
                    return " AND {$field} IN( '" . implode( "','", $value ) . "' )";
                } else {
                    return " AND {$field} NOT IN( '" . implode( "','", $value ) . "' )";
                }

            }

            return '';

        }

        /**
         * Render select box field
         */
        public function render_select2( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {
            $this->setup_admin_scripts();

            $field_type_object->type = new CMB2_Type_Select( $field_type_object );

            echo $field_type_object->select( array(
                'class'            => 'cmb2-select2',
                'desc'             => $field_type_object->_desc( true ),
                //'options'          => '<option></option>' . $field_type_object->concat_items(),
                'data-placeholder' => $field->args( 'attributes', 'placeholder' ) ? $field->args( 'attributes', 'placeholder' ) : '',
                'data-action' => $field->args( 'attributes', 'action' ) ? $field->args( 'attributes', 'action' ) : 'cmb2_select2_get_results',
                'data-tags' => $field->args( 'attributes', 'tags' ) ? $field->args( 'attributes', 'tags' ) : '',
            ) );
        }

        /**
         * Render multi-value select input field
         */
        public function render_multiselect2( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {
            $this->setup_admin_scripts();

            $field_type_object->type = new CMB2_Type_Select( $field_type_object );

            $a = $field_type_object->parse_args( 'multiselect2', array(
                'multiple'          => 'multiple',
                //'style'            => 'width: 99%',
                'class'             => 'cmb2-select2 cmb2-multiselect2',
                'name'              => $field_type_object->_name() . '[]',
                'id'                => $field_type_object->_id(),
                'desc'              => $field_type_object->_desc( true ),
                'options'           => $this->get_multiselect2_options( $field_escaped_value, $field_type_object ),
                'data-placeholder'  => $field->args( 'attributes', 'placeholder' ) ? $field->args( 'attributes', 'placeholder' ) : '',
                'data-action'       => $field->args( 'attributes', 'action' ) ? $field->args( 'attributes', 'action' ) : 'cmb2_select2_get_results',
                'data-tags'         => $field->args( 'attributes', 'tags' ) ? $field->args( 'attributes', 'tags' ) : '',
            ) );

            $attrs = $field_type_object->concat_attrs( $a, array( 'desc', 'options' ) );

            echo sprintf( '<select%s>%s</select>%s', $attrs, $a['options'], $a['desc'] );
        }

        /**
         * Return list of options for multiselect2
         *
         * Return the list of options, with selected options at the top preserving their order. This also handles the
         * removal of selected options which no longer exist in the options array.
         */
        public function get_multiselect2_options( $field_escaped_value, $field_type_object ) {
            $options = (array) $field_type_object->field->options();

            // If we have selected items, we need to preserve their order
            if ( ! empty( $field_escaped_value ) ) {
                $options = $this->sort_array_by_array( $options, $field_escaped_value );
            }

            $selected_items = '';
            $other_items = '';

            foreach ( $options as $option_value => $option_label ) {

                // Clone args & modify for just this item
                $option = array(
                    'value' => $option_value,
                    'label' => $option_label,
                );

                // Split options into those which are selected and the rest
                if ( in_array( $option_value, (array) $field_escaped_value ) ) {
                    $option['checked'] = true;
                    $selected_items .= $field_type_object->select_option( $option );
                } else if ( isset( $field_escaped_value[$option_value] ) ) {
                    $option['checked'] = true;
                    $selected_items .= $field_type_object->select_option( $option );
                } else {
                    $other_items .= $field_type_object->select_option( $option );
                }
            }

            return $selected_items . $other_items;
        }

        /**
         * Sort an array by the keys of another array
         *
         * @author Eran Galperin
         * @link http://link.from.pw/1Waji4l
         */
        public function sort_array_by_array( array $array, array $orderArray ) {
            $ordered = array();

            foreach ( $orderArray as $key ) {
                if ( array_key_exists( $key, $array ) ) {
                    $ordered[ $key ] = $array[ $key ];
                    unset( $array[ $key ] );
                }
            }

            return $ordered + $array;
        }

        /**
         * Handle sanitization for repeatable fields
         */
        public function multiselect2_sanitize( $check, $meta_value, $object_id, $field_args ) {
            if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
                return $check;
            }

            foreach ( $meta_value as $key => $val ) {
                $meta_value[$key] = array_map( 'sanitize_text_field', $val );
            }

            return $meta_value;
        }

        /**
         * Handle escaping for repeatable fields
         */
        public function multiselect2_escaped_value( $check, $meta_value, $field_args ) {
            if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
                return $check;
            }

            foreach ( $meta_value as $key => $val ) {
                $meta_value[$key] = array_map( 'esc_attr', $val );
            }

            return $meta_value;
        }

        /**
         * Add 'table-layout' class to multi-value select field
         */
        public function multiselect2_table_row_class( $check ) {
            $check[] = 'cmb2-multiselect2';

            return $check;
        }

        /**
         * Enqueue scripts and styles
         */
        public function setup_admin_scripts() {

            global $cmb2_field_select2_scripts_enqueued;

            if( $cmb2_field_select2_scripts_enqueued === true ) {
                return;
            }

            $cmb2_field_select2_scripts_enqueued = true;

            // CMB2 Select2
            wp_register_script( 'cmb2-select2-js', plugins_url( 'lib/select2.min.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
            wp_enqueue_script( 'cmb2-select2-js' );
            wp_enqueue_style( 'cmb2-select2-css', plugins_url( 'lib/select2.css', __FILE__ ), array(), self::VERSION );


            wp_register_script( 'cmb2-field-select2-js', plugins_url( 'js/cmb2-field-select2.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );

            wp_localize_script( 'cmb2-field-select2-js', 'cmb2_field_select2', array(
                'ajaxurl' => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
                'nonce' => $this->get_nonce(),
            ) );

            wp_enqueue_script( 'cmb2-field-select2-js' );

            wp_enqueue_style( 'cmb2-field-select2-css', plugins_url( 'css/cmb2-field-select2.css', __FILE__ ), array(), self::VERSION );
        }

        public function get_nonce() {

            if( ! defined( 'CMB2_SELECt2_NONCE' ) )
                define( 'CMB2_SELECt2_NONCE', wp_create_nonce( 'cmb2_select2' ) );

            return CMB2_SELECt2_NONCE;

        }

    }

    function cmb2_select2_options_cb( $field ) {

        // Setup vars
        $value = absint( $field->escaped_value );
        $attributes = $field->args( 'attributes' );
        $from = isset( $attributes['data-from'] ) ? $attributes['data-from'] : '';
        $options = array();

        if( ! empty( $value ) ) {
            if( ! is_array( $value ) ) {
                $value = array( $value );
            }

            foreach( $value as $id ) {

                switch ( $from ) {
                    case 'posts':
                        $post = get_post( $id );
                        if( $post ) {
                            $options[$id] = $post->post_title;
                        }
                        break;
                    case 'terms':
                        $term = get_term_by( 'term_id', $id );
                        if( $term ) {
                            $options[$id] = $term->name;
                        }
                        break;
                    case 'users':
                        $user = get_userdata( $id );
                        if( $user ) {
                            $options[$id] = $user->user_login;
                        }
                        break;
                    case 'ct':
                        $table = isset( $attributes['data-table'] ) ? $attributes['data-table'] : '';
                        $id_field = isset( $attributes['data-id-field'] ) ? $attributes['data-id-field'] : '';
                        $text_field = isset( $attributes['data-text-field'] ) ? $attributes['data-text-field'] : '';

                        if( ! empty( $table ) && ! empty( $id_field ) && ! empty( $text_field ) ) {
                            global $ct_table;

                            $ct_table = ct_setup_table( $table );

                            $object = ct_get_object( $id, ARRAY_A );

                            if( $object ) {
                                $options[$id] = $object[$text_field];
                            }

                            ct_reset_setup_table();
                        }
                        break;
                }
            }
        }

        return $options;

    }

    $cmb2_field_select2 = new RGC_CMB2_Field_Select2();
}