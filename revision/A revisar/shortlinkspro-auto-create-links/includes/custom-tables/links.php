<?php
/**
 * Links
 *
 * @package     ShortLinksPro\Custom_Tables\Links
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Custom Table Labels
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_links_labels() {

    return array(
        'singular' => __( 'Link', 'shortlinkspro' ),
        'plural' => __( 'Links', 'shortlinkspro' ),
        'labels' => array(
            'list_menu_title' => __( 'Manage Links', 'shortlinkspro' ),
            'add_menu_title' => __( 'Add New Link', 'shortlinkspro' ),
        ),
    );

}
add_filter( 'ct_shortlinkspro_links_labels', 'shortlinkspro_links_labels' );

/**
 * Parse query args for fields
 *
 * @since 1.0.0
 *
 * @param string $where
 * @param CT_Query $ct_query
 *
 * @return string
 */
function shortlinkspro_links_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'shortlinkspro_links' ) {
        return $where;
    }

    // Shorthand
    $qv = $ct_query->query_vars;

    // Title
    $where .= shortlinkspro_custom_table_where( $qv, 'title', 'string' );

    // URL
    $where .= shortlinkspro_custom_table_where( $qv, 'url', 'string' );

    // Slug
    $where .= shortlinkspro_custom_table_where( $qv, 'slug', 'string' );

    // Link Category ID
    if( isset( $qv['link_category_id'] ) ) {
        $link_category_id = absint( $qv['link_category_id'] );
        $where .= " AND lc.link_category_id = {$link_category_id}";
    }

    // Link Tag ID
    if( isset( $qv['link_tag_id'] ) ) {
        $link_tag_id = absint( $qv['link_tag_id'] );
        $where .= " AND lt.link_tag_id = {$link_tag_id}";
    }

    return $where;
}
add_filter( 'ct_query_where', 'shortlinkspro_links_query_where', 10, 2 );

/**
 * Parse query args for user earnings to be applied on JOIN clause
 *
 * @since   1.2.8
 *
 * @param string    $join
 * @param CT_Query  $ct_query
 *
 * @return string
 */
function shortlinkspro_links_query_join( $join, $ct_query ) {

    global $ct_table, $ct_registered_tables;

    if( $ct_table->name !== 'shortlinkspro_links' ) {
        return $join;
    }

    $table_name = $ct_table->db->table_name;

    // Shorthand
    $qv = $ct_query->query_vars;

    // Parent link category
    if( isset( $qv['link_category_id'] ) ) {
        $relationship_table = $ct_registered_tables['shortlinkspro_link_categories_relationships'];
        $join .= " LEFT JOIN {$relationship_table->db->table_name} lc ON ( lc.link_id = {$table_name}.id )";
    }

    // Parent link tag
    if( isset( $qv['link_tag_id'] ) ) {
        $relationship_table = $ct_registered_tables['shortlinkspro_link_tags_relationships'];
        $join .= " LEFT JOIN {$relationship_table->db->table_name} lt ON ( lt.link_id = {$table_name}.id )";
    }

    return $join;

}
add_filter( 'ct_query_join', 'shortlinkspro_links_query_join', 10, 2 );

/**
 * Define the search fields
 *
 * @since 1.0.0
 *
 * @param array $search_fields
 *
 * @return array
 */
function shortlinkspro_links_search_fields( $search_fields ) {

    $search_fields[] = 'title';
    $search_fields[] = 'url';
    $search_fields[] = 'slug';

    return $search_fields;

}
add_filter( 'ct_query_shortlinkspro_links_search_fields', 'shortlinkspro_links_search_fields' );

/**
 * Columns in list view
 *
 * @since 1.0.0
 *
 * @param array $columns
 *
 * @return array
 */
function shortlinkspro_links_manage_columns( $columns = array() ) {

    $columns['title']         = __( 'Title', 'shortlinkspro' );
    $columns['url']           = __( 'Target URL', 'shortlinkspro' );
    $columns['link_category'] = __( 'Category', 'shortlinkspro' );
    $columns['link_tags']     = __( 'Tags', 'shortlinkspro' );
    $columns['options']       = __( 'Options', 'shortlinkspro' );
    $columns['clicks']        = __( 'Clicks', 'shortlinkspro' );
    $columns['slug']          = __( 'ShortLink', 'shortlinkspro' );

    return $columns;
}
add_filter( 'manage_shortlinkspro_links_columns', 'shortlinkspro_links_manage_columns' );

/**
 * Sortable columns for list view
 *
 * @since 1.0.0
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function shortlinkspro_links_manage_sortable_columns( $sortable_columns ) {

    $sortable_columns['title']              = array( 'title', false );
    $sortable_columns['slug']               = array( 'slug', false );
    $sortable_columns['url']                = array( 'url', false );

    return $sortable_columns;

}
add_filter( 'manage_shortlinkspro_links_sortable_columns', 'shortlinkspro_links_manage_sortable_columns' );

/**
 * Row actions.
 *
 * @since 1.0.0
 *
 * @param array $actions An array of row action links. Defaults are
 *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
 *                         'Delete Permanently', 'Preview', and 'View'.
 * @param stdClass $object The item object.
 *
 * @return array
 */
function shortlinkspro_links_row_actions( $actions, $object ) {

    global $ct_table;

    // List link
    $url = $ct_table->views->list->get_link();
    $url = add_query_arg( array( 'id' => $object->id ), $url );

    if ( current_user_can( $ct_table->cap->edit_item, $object->id ) ) {
        $duplicate_url = add_query_arg( array( 'shortlinkspro-action' => 'duplicate_link' ), $url );
        $duplicate_url = add_query_arg( '_wpnonce', wp_create_nonce( 'shortlinkspro_duplicate_link_' . $object->id ), $duplicate_url );

        $actions['duplicate'] = sprintf(
            '<a href="%s" class="shortlinkspro-duplicate-link" aria-label="%s">%s</a>',
            $duplicate_url,
            esc_attr( __( 'Duplicate', 'shortlinkspro' ) ),
            __( 'Duplicate', 'shortlinkspro' )
        );
    }

    /**
     * Row actions, after duplicate (for other plugins).
     *
     * @since 1.0.0
     *
     * @param array $actions An array of row action links. Defaults are
     *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
     *                         'Delete Permanently', 'Preview', and 'View'.
     * @param stdClass $object The item object.
     *
     * @return array
     */
    $actions = apply_filters( 'shortlinkspro_links_row_actions_after_duplicate', $actions, $object );

    // Check delete cap from clicks
    $clicks_table = ct_setup_table( 'shortlinkspro_clicks' );

    if ( $object->tracking && current_user_can( $clicks_table->cap->delete_items ) ) {
        $reset_url = add_query_arg( array( 'shortlinkspro-action' => 'reset_clicks' ), $url );
        $reset_url = add_query_arg( '_wpnonce', wp_create_nonce( 'shortlinkspro_reset_clicks_' . $object->id ), $reset_url );

        $actions['reset_clicks'] = sprintf(
            '<a href="%s" class="shortlinkspro-reset-link-clicks" onclick="%s" aria-label="%s">%s</a>',
            $reset_url,
            "return confirm('" .
            esc_attr( __( "Are you sure you want to reset clicks for this link?\\n\\nClick \\'Cancel\\' to go back, \\'OK\\' to confirm the reset.", 'shortlinkspro' ) ) .
            "');",
            esc_attr( __( 'Reset Clicks', 'shortlinkspro' ) ),
            __( 'Reset Clicks', 'shortlinkspro' )
        );
    }

    ct_reset_setup_table();

    // Override delete
    if ( current_user_can( $ct_table->cap->delete_item, $object->id ) ) {
        unset( $actions['delete'] );
        $actions['delete'] = sprintf(
            '<a href="%s" class="submitdelete" onclick="%s" aria-label="%s">%s</a>',
            ct_get_delete_link( $ct_table->name, $object->id ),
            "return confirm('" .
            esc_attr( __( "Are you sure you want to delete this link?\\n\\nClick \\'Cancel\\' to go back, \\'OK\\' to confirm deletion.", 'shortlinkspro' ) ) .
            "');",
            esc_attr( __( 'Delete', 'shortlinkspro' ) ),
            __( 'Delete', 'shortlinkspro' )
        );
    }

    return $actions;

}
add_filter( 'shortlinkspro_links_row_actions', 'shortlinkspro_links_row_actions', 10, 2 );

/**
 * Process duplicate link action
 *
 * @since 1.0.0
 *
 * @param array $request
 */
function shortlinkspro_action_duplicate_link( $request ) {

    if( isset( $request['id'] ) ) {

        $link_id = absint( $request['id'] );

        // Check id
        if( $link_id !== 0 ) {

            $nonce = $request['_wpnonce'];

            // Check nonce
            if( wp_verify_nonce( sanitize_text_field( wp_unslash ( $nonce ) ), 'shortlinkspro_duplicate_link_' . $link_id ) ) {

                // Setup table
                $ct_table = ct_setup_table( 'shortlinkspro_links' );

                // Check user capabilities
                if ( current_user_can( $ct_table->cap->edit_item, $link_id ) ) {
                    $link = ct_get_object( $link_id, ARRAY_A );

                    unset( $link['id'] );

                    $prefix = shortlinkspro_get_option( 'slug_prefix', '' );
                    $length = absint( shortlinkspro_get_option( 'slug_length', '4' ) );

                    // Create a new slug
                    $link['slug'] = shortlinkspro_generate_link_slug( $prefix, $length );

                    $new_link_id = ct_insert_object( $link );

                    if( $new_link_id ) {
                        $new_link_id = absint( $new_link_id );

                        // Clone metas
                        shortlinkspro_clone_item_metas( 'shortlinkspro_links', $link_id, $new_link_id );
                    }

                }

                ct_reset_setup_table();

                // Redirect to the same URL but without action parameters
                wp_redirect( remove_query_arg( array( 'id', 'shortlinkspro-action', '_wpnonce' ) ) );
                exit;

            }

        }

    }

}
add_action( 'shortlinkspro_action_duplicate_link', 'shortlinkspro_action_duplicate_link' );

/**
 * Process reset clicks action
 *
 * @since 1.0.0
 *
 * @param array $request
 */
function shortlinkspro_action_reset_clicks( $request ) {

    if( isset( $request['id'] ) ) {

        $link_id = absint( $request['id'] );

        // Check id
        if( $link_id !== 0 ) {

            $nonce = $request['_wpnonce'];

            // Check nonce
            if( wp_verify_nonce( sanitize_text_field( wp_unslash ( $nonce ) ), 'shortlinkspro_reset_clicks_' . $link_id ) ) {

                // Setup table
                $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

                // Check user capabilities
                if ( current_user_can( $ct_table->cap->delete_items ) ) {
                    // Reset the link clicks
                    shortlinkspro_reset_link_clicks( $link_id );
                }

                ct_reset_setup_table();

                // Redirect to the same URL but without action parameters
                wp_redirect( remove_query_arg( array( 'id', 'shortlinkspro-action', '_wpnonce' ) ) );
                exit;

            }

        }

    }

}
add_action( 'shortlinkspro_action_reset_clicks', 'shortlinkspro_action_reset_clicks' );

function shortlinkspro_links_get_views( $views ) {

    $link_category_id = ( isset( $_GET['link_category_id'] ) ) ? absint( $_GET['link_category_id'] ) : 0;
    $link_tag_id = ( isset( $_GET['link_tag_id'] ) ) ? absint( $_GET['link_tag_id'] ) : 0;
    $from = ( isset( $_GET['from'] ) ) ? $_GET['from'] : '';

    ?>
        <?php if( isset( $_GET['link_category_id'] ) || isset( $_GET['link_tag_id'] ) ) : ?>
            <div class="shortlinkspro-links-filtered-by">
                <?php
                if( isset( $_GET['link_category_id'] ) ) {
                    /* translators: %s: Category title. */
                    echo sprintf( __( "Filtered by category %s", 'shortlinkspro' ), shortlinkspro_get_link_category_edit_link( $link_category_id ) );
                } else {
                    /* translators: %s: Tag title. */
                    echo sprintf( __( "Filtered by tag %s", 'shortlinkspro' ), shortlinkspro_get_link_tag_edit_link( $link_tag_id ) );
                }

                echo ' | ';

                if( $from === 'links' ) {
                    echo sprintf( '<a href="%s">&laquo %s</a>',
                        esc_attr( ct_get_list_link( 'shortlinkspro_links' ) ),
                        esc_html__( "Remove filter", 'shortlinkspro' )
                    );
                } else if( $from === 'link_categories' ) {
                    echo sprintf( '<a href="%s">&laquo %s</a>',
                        esc_attr( ct_get_list_link( 'shortlinkspro_link_categories' ) ),
                        esc_html__( "Back to Categories", 'shortlinkspro' )
                    );
                }  else if( $from === 'link_tags' ) {
                    echo sprintf( '<a href="%s">&laquo %s</a>',
                        esc_attr( ct_get_list_link( 'shortlinkspro_link_tags' ) ),
                        esc_html__( "Back to Tags", 'shortlinkspro' )
                    );
                }
                ?>
            </div>
        <?php endif; ?>

    <?php

    return $views;

}
add_filter( 'shortlinkspro_links_get_views', 'shortlinkspro_links_get_views' );

/**
 * Columns rendering for list view
 *
 * @since  1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function shortlinkspro_links_manage_custom_column(  $column_name, $object_id ) {

    // Setup vars
    $link = ct_get_object( $object_id );

    switch( $column_name ) {
        case 'title':
            $title = $link->title;
            if( empty( $title ) ) {
                $title = __( '(no title)', 'shortlinkspro' );
            }
            ?>
            <strong><a href="<?php echo esc_attr( ct_get_edit_link( 'shortlinkspro_links', $link->id ) ); ?>"><?php echo esc_html( $title ); ?></a></strong>
            <?php

            break;
        case 'url':
            $url = $link->url;
            if( ! empty( $url ) ) {
                /* translators: %s: URL. */
                $title = sprintf( __( 'Visit %s', 'shortlinkspro' ), $url );
                ?>
                <a href="<?php echo esc_attr( $url ); ?>" target="_blank" title="<?php echo esc_attr( $title ); ?>"><?php echo esc_html( $url ); ?></a>
                <?php
            }
            break;
        case 'link_category':
            $title = __( 'View links with this category', 'shortlinkspro' );

            $list_url = ct_get_list_link( 'shortlinkspro_links' );
            $list_url = add_query_arg( array( 'from' => 'links' ), $list_url );

            ct_setup_table( 'shortlinkspro_link_categories_relationships' );
            $categories = ct_get_object_terms( $link->id );
            ct_reset_setup_table();

            if( $categories !== null ) {

                if( ! is_array( $categories ) ) {
                    $categories = array( $categories );
                }

                foreach( $categories as $category ) {
                    $url = add_query_arg( array( 'link_category_id' => $category->id ), $list_url );

                    ?>
                    <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>" class="shortlinkspro-link-category"><?php echo esc_html( $category->name ); ?></a>
                    <?php
                }
            }
            break;
        case 'link_tags':
            $title = __( 'View links with this tag', 'shortlinkspro' );

            $list_url = ct_get_list_link( 'shortlinkspro_links' );
            $list_url = add_query_arg( array( 'from' => 'links' ), $list_url );

            ct_setup_table( 'shortlinkspro_link_tags_relationships' );
            $tags = ct_get_object_terms( $link->id );
            ct_reset_setup_table();

            if( $tags !== null ) {

                if( ! is_array( $tags ) ) {
                    $tags = array( $tags );
                }

                foreach( $tags as $tag ) {
                    $url = add_query_arg( array( 'link_tag_id' => $tag->id ), $list_url );

                    ?>
                    <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>" class="shortlinkspro-link-tag"><?php echo esc_html( $tag->name ); ?></a>
                    <?php
                }
            }
            break;
        case 'options':
            $redirect_type = $link->redirect_type;
            $redirect_types = shortlinkspro_redirect_types();
            $redirect_type_label = ( isset( $redirect_types[$redirect_type] ) ) ? $redirect_types[$redirect_type] : $redirect_type;
            $nofollow = $link->nofollow;
            $sponsored = $link->sponsored;
            $parameter_forwarding = $link->parameter_forwarding;
            ?>

            <span class="shortlinkspro-link-option shortlinkspro-link-option-enabled shortlinkspro-link-option-redirect-type shortlinkspro-link-option-redirect-<?php echo esc_attr( $redirect_type ); ?>">
                <?php cmb_tooltip_html( __( 'Redirect:', 'shortlinkspro' ) . ' ' . $redirect_type_label, 'arrow-right-alt' ); ?>
            </span>

            <span class="shortlinkspro-link-option shortlinkspro-link-option-<?php echo ( $nofollow ? 'enabled' : 'disabled' ); ?> shortlinkspro-link-option-nofollow">
                <?php cmb_tooltip_html( ( $nofollow ? __( 'No Follow enabled', 'shortlinkspro' ) : __( 'No Follow disabled', 'shortlinkspro' ) ), 'no' ); ?>
            </span>

            <span class="shortlinkspro-link-option shortlinkspro-link-option-<?php echo ( $sponsored ? 'enabled' : 'disabled' ); ?> shortlinkspro-link-option-sponsored">
                <?php cmb_tooltip_html( ( $sponsored ? __( 'Sponsored enabled', 'shortlinkspro' ) : __( 'Sponsored disabled', 'shortlinkspro' ) ), 'star-filled' ); ?>
            </span>

            <span class="shortlinkspro-link-option shortlinkspro-link-option-<?php echo ( $parameter_forwarding ? 'enabled' : 'disabled' ); ?> shortlinkspro-link-option-parameter-forwarding">
                <?php cmb_tooltip_html( ( $parameter_forwarding ? __( 'Parameter Forwarding enabled', 'shortlinkspro' ) : __( 'Parameter Forwarding disabled', 'shortlinkspro' ) ), 'controls-forward' ); ?>
            </span>

            <?php
            break;
        case 'clicks':
            $tracking = $link->tracking;

            if( $tracking ) {
                $clicks = shortlinkspro_get_link_clicks( $link->id );
                $unique_clicks = shortlinkspro_get_link_unique_clicks( $link->id );
                /* translators: %1$d: Number of clicks. %2$d: Number of unique clicks */
                $title = sprintf( __( '%1$d Clicks / %2$d Unique', 'shortlinkspro' ), $clicks, $unique_clicks );

                $url = ct_get_list_link( 'shortlinkspro_clicks' );
                $url = add_query_arg( array( 'link_id' => $link->id ), $url );
                $url = add_query_arg( array( 'from' => 'links' ), $url );
                $url = add_query_arg( '_wpnonce', wp_create_nonce( 'shortlinkspro_clicks_filter' ), $url );
                ?>
                <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>"><?php echo esc_html( $clicks ). '/' . esc_html( $unique_clicks ); ?></a>
                <?php
            } else {
                echo esc_html( __( 'Tracking disabled', 'shortlinkspro' ) );
            }
            break;
        case 'slug':
            /* translators: %s: Visit */
            $title = sprintf( __( 'Visit %s', 'shortlinkspro' ), site_url('/') . $link->slug );
            ?>
            <span class="shortlinkspro-site-url"><?php echo esc_html( site_url('/') ); ?></span>
            <input value="<?php echo esc_attr( $link->slug ); ?>" onclick="this.focus(); this.select();" readonly class="shortlinkspro-slug-input"/>
            <?php shortlinkspro_copy_to_clipboard( site_url('/') . $link->slug ); ?>
            <a href="<?php echo esc_attr( site_url('/') . $link->slug ); ?>" target="_blank" title="<?php echo esc_attr( $title ); ?>" class="shortlinkspro-slug-anchor"><?php echo shortlinkspro_dashicon( 'external' ); ?></a>
            <?php
            break;
    }
}
add_action( 'manage_shortlinkspro_links_custom_column', 'shortlinkspro_links_manage_custom_column', 10, 2 );

/**
 * Default data when creating a new item (similar to WP auto draft) see ct_insert_object()
 *
 * @since  1.0.0
 *
 * @param array $default_data
 *
 * @return array
 */
function shortlinkspro_links_default_data( $default_data = array() ) {

    $default_data['title'] = '';
    $default_data['url'] = '';

    $prefix = shortlinkspro_get_option( 'slug_prefix', '' );
    $length = absint( shortlinkspro_get_option( 'slug_length', '4' ) );

    $default_data['slug'] = shortlinkspro_generate_link_slug( $prefix, $length );

    // Get default redirect type
    $default_data['redirect_type'] = shortlinkspro_get_option( 'redirect_type', '307' );

    // Get default link options
    $link_options = shortlinkspro_get_option( 'link_options', array( 'nofollow', 'tracking' ) ) ;
    $default_data['nofollow'] = absint( in_array( 'nofollow', $link_options ) );
    $default_data['sponsored'] = absint( in_array( 'sponsored', $link_options ) );
    $default_data['parameter_forwarding'] = absint( in_array( 'parameter_forwarding', $link_options ) );
    $default_data['tracking'] = absint( in_array( 'tracking', $link_options ) );

    $default_data['author_id'] = get_current_user_id();
    $default_data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $default_data['updated_at'] = gmdate( 'Y-m-d H:i:s' );

    return $default_data;
}
add_filter( 'ct_shortlinkspro_links_default_data', 'shortlinkspro_links_default_data' );

/**
 * CMB2 Meta boxes
 *
 * @since  1.0.0
 */
function shortlinkspro_links_meta_boxes() {

    // Title
    shortlinkspro_add_meta_box(
        'shortlinkspro-link-title',
        __( 'Link Title', 'shortlinkspro' ),
        'shortlinkspro_links',
        array(
            'title' => array(
                'name'      => __( 'Title', 'shortlinkspro' ),
                'type'      => 'text',
                'attributes' => array(
                    'placeholder' => __('Enter title here', 'shortlinkspro'),
                ),
            ),
        ),
        array(
            'priority' => 'high',
        )
    );

    // Link Settings
    shortlinkspro_add_meta_box(
        'shortlinkspro-link-settings',
        __( 'Link Settings', 'shortlinkspro' ),
        'shortlinkspro_links',
        array(
            'redirect_type' => array(
                'name'      => __( 'Redirect Type', 'shortlinkspro' ),
                'type'      => 'select',
                'options'   => shortlinkspro_redirect_types(),
                'tooltip'   => __( 'Redirect type of this link.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'url' => array(
                'name'      => __( 'Target URL', 'shortlinkspro' ),
                'type'      => 'textarea',
                'attributes' => array(
                    'rows' => 2,
                ),
                'tooltip'   => __( 'The URL that your link will redirect to.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'after_field' => 'shortlinkspro_utm_builder',
            ),
            'slug' => array(
                'name'      => __( 'ShortLink', 'shortlinkspro' ),
                'type'      => 'text',
                'tooltip'   => __( 'How your link will appear.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'before_field' => site_url('/'),
                'after_field' => 'shortlinkspro_slug_after_field',
            ),
            'notes' => array(
                'name'      => __( 'Notes', 'shortlinkspro' ),
                'type'      => 'textarea',
                'attributes' => array(
                    'rows' => 2,
                ),
                'tooltip'   => __( 'Add internal notes to your link for your own needs. Those notes are not displayed anywhere.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
        ),
        array(
            'priority' => 'high',
        )
    );

    // Link Options
    shortlinkspro_add_meta_box(
        'shortlinkspro-link-options',
        __( 'Link Options', 'shortlinkspro' ),
        'shortlinkspro_links',
        array(
            'nofollow' => array(
                'desc'      => __( 'No Follow', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Adds the nofollow and noindex parameters in the HTTP response headers. Recommended.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
            'sponsored' => array(
                'desc'      => __( 'Sponsored', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Adds the sponsored parameter in the HTTP response headers. Recommended if this an affiliate link.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
            'parameter_forwarding' => array(
                'desc'      => __( 'Parameter Forwarding', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Forward parameters passed to this link onto the target URL.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
            'tracking' => array(
                'desc'      => __( 'Tracking', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Enable clicks tracking.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
        ),
        array(
            'context' => 'side',
            'priority' => 'high',
        )
    );

    // Link Categories & Tags
    shortlinkspro_add_meta_box(
        'shortlinkspro-link-terms',
        __( 'Category & Tags', 'shortlinkspro' ),
        'shortlinkspro_links',
        array(
            'category' => array(
                'name'      => __( 'Category', 'shortlinkspro' ),
                'type'      => 'select2',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Set the category of your choice to this link.', 'shortlinkspro' ),
                ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'attributes' => array(
                    'placeholder' => __('Select a category', 'shortlinkspro'),
                    'data-from' => 'ct',
                    'data-table' => 'shortlinkspro_link_categories',
                    'data-id-field' => 'id',
                    'data-text-field' => 'name',
                    'data-label-field' => '',
                ),
                'options_cb' => 'shortlinkspro_term_options_cb',
                'default_cb' => 'shortlinkspro_term_default_cb',
                'save_field' => false,
            ),
            'tags' => array(
                'name'      => __( 'Tags', 'shortlinkspro' ),
                'type'      => 'multiselect2',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Set the tags of your choice to this link.', 'shortlinkspro' ),
                ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'attributes' => array(
                    'placeholder' => __('Select the tags', 'shortlinkspro'),
                    'data-tags' => true,
                    'data-from' => 'ct',
                    'data-table' => 'shortlinkspro_link_tags',
                    'data-id-field' => 'id',
                    'data-text-field' => 'name',
                    'data-label-field' => '',
                ),
                'options_cb' => 'shortlinkspro_term_options_cb',
                'default_cb' => 'shortlinkspro_term_default_cb',
                'save_field' => false,
            ),
        ),
        array(
            'context' => 'side',
            'priority' => 'high',
        )
    );
}

add_action( 'cmb2_init', 'shortlinkspro_links_meta_boxes' );

/**
 * Handles sanitization for url field
 *
 * @since 1.0.0
 *
 * @param  mixed      $value      The unsanitized value from the form
 *
 * @return mixed                  Sanitized value to be stored/displayed
 */
function shortlinkspro_url_sanitization_cb( $value ) {

    $sanitized = $value;
    $sanitized = is_array( $sanitized ) ? array_map( 'shortlinkspro_url_keep_ampersands', $sanitized ) : shortlinkspro_url_keep_ampersands( $sanitized );
    $sanitized = is_array( $sanitized ) ? array_map( 'wp_kses_post', $sanitized ) : wp_kses_post( $sanitized );
    $sanitized = is_array( $sanitized ) ? array_map( 'shortlinkspro_url_restore_ampersands', $sanitized ) : shortlinkspro_url_restore_ampersands( $sanitized );

    return $sanitized;

}

/**
 * Keep &amp; in a string
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return string
 */
function shortlinkspro_url_keep_ampersands( $value ) {
    return str_replace( '&amp;', 'KEEP_AMP', $value );
}

/**
 * Restore & in a string
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return string
 */
function shortlinkspro_url_restore_ampersands( $value ) {
    $value = str_replace( '&amp;', '&', $value );
    return str_replace( 'KEEP_AMP', '&amp;', $value );
}

function shortlinkspro_slug_after_field() {
    shortlinkspro_copy_to_clipboard();
}

function shortlinkspro_term_default_cb( $field_args, $field ) {

    global $ct_table;

    $default = array();

    $table = $field->args( 'attributes' )['data-table'];

    $ct_table = ct_setup_table( $table . '_relationships' );

    $terms = ct_get_object_terms( $field->object_id );

    if( $terms === null ) {
        ct_reset_setup_table();
        return $default;
    }

    if( ! is_array( $terms ) ) {
        $terms = array( $terms );
    }

    foreach( $terms as $term ) {
        $default[$term->id] = $term->name;
    }

    ct_reset_setup_table();

    return $default;

}

function shortlinkspro_term_options_cb( $field ) {

    global $ct_table;

    $options = array();

    $table = $field->args( 'attributes' )['data-table'];

    $ct_table = ct_setup_table( $table . '_relationships' );

    $terms = ct_get_object_terms( $field->object_id );

    if( $terms === null ) {
        ct_reset_setup_table();
        return $options;
    }

    if( ! is_array( $terms ) ) {
        $terms = array( $terms );
    }

    foreach( $terms as $term ) {
        $options[$term->id] = $term->name;
    }

    ct_reset_setup_table();

    return $options;

}

/**
 * On insert link
 *
 * @since  1.0.0
 *
 * @param array $object_data
 * @param array $original_object_data
 */
function shortlinkspro_links_on_insert_object_data( $object_data, $original_object_data ) {

    global $ct_table;

    if( $ct_table->name !== 'shortlinkspro_links' ) {
        return $object_data;
    }

    // Sanitize url
    if( isset( $_REQUEST['url'] ) ) {
        $object_data['url'] = shortlinkspro_url_sanitization_cb( $_REQUEST['url'] );
    }

    // Check for checkbox fields
    $fields = array( 'nofollow', 'sponsored', 'parameter_forwarding', 'tracking' );

    foreach( $fields as $field ) {

        if ( ! empty( $original_object_data ) ){
            // Override value if option is not in the request
            if ( ! isset( $_REQUEST[$field] ) ) {
                $object_data[$field] = 0;
            }
        }

        if( isset( $object_data[$field] ) ) {
            // Override "on" value to 1 on checkboxes
            if( $object_data[$field] === 'on' ) {
                $object_data[$field] = 1;
            } else {
                $object_data[$field] = absint( $object_data[$field] );

                if( $object_data[$field] === 'on' ) {
                    $object_data[$field] = 1;
                }
            }
        }

    }

    // Check for slugs changes
    if( isset( $object_data['slug'] ) && isset( $original_object_data['slug'] ) ) {
        if( $original_object_data['slug'] !== '' && $object_data['slug'] !== $original_object_data['slug'] ) {

            $link = shortlinkspro_get_link_by_slug( $object_data['slug'] );

            if( $link && absint( $link->id ) !== absint( $object_data['id'] ) ) {
                // Revert back the new slug if another link has it
                $object_data['slug'] = $original_object_data['slug'];
            }
        }
    }
  
    // Check if we are updating
    if( isset( $object_data['id'] ) ) {
        if( isset( $object_data['category'] ) ) {
            // Update relationships
            shortlinkspro_handle_terms_save( $object_data['id'], $object_data['category'], 'shortlinkspro_link_categories' );

            // Remove from the object data
            unset( $object_data['category'] );
        } else {
            // Clear relationships
            shortlinkspro_handle_terms_save( $object_data['id'], array(), 'shortlinkspro_link_categories' );
        }

        if( isset( $object_data['tags'] ) ) {
            // Update relationships
            shortlinkspro_handle_terms_save( $object_data['id'], $object_data['tags'], 'shortlinkspro_link_tags' );

            // Remove from the object data
            unset( $object_data['tags'] );
        } else {
            // Clear relationships
            shortlinkspro_handle_terms_save( $object_data['id'], array(), 'shortlinkspro_link_tags' );
        }
    } else {
        // Removed unwanted fields if they exists
        if( isset( $object_data['category'] ) ) {
            unset( $object_data['category'] );
        }

        if( isset( $object_data['tags'] ) ) {
            unset( $object_data['tags'] );
        }
    }

    return $object_data;

}
add_filter( 'ct_insert_object_data', 'shortlinkspro_links_on_insert_object_data', 10, 2 );

function shortlinkspro_handle_terms_save( $link_id, $terms, $table ) {

    global $ct_table;

    $link_id = absint( $link_id );

    if( ! is_array( $terms ) ) {
        $terms = array( $terms );
    }

    // Clear relationships
    if( count( $terms ) === 0 ) {
        $ct_table = ct_setup_table( $table . '_relationships' );
        ct_delete_object_relationships( $link_id );
        ct_reset_setup_table();
        return;
    }

    $terms_ids = array();

    $ct_table = ct_setup_table( $table );

    // Check terms received
    foreach ( $terms as $term_id ) {

        // Add new term
        if( ! is_numeric( $term_id ) && ! empty( $term_id ) ) {
            $new_term_id = ct_insert_object( array(
                'name' => $term_id,
                'slug' => sanitize_title_with_dashes( $term_id ),
                'description' => '',
            ) );

            if( $new_term_id ) {
                $terms_ids[] = absint( $new_term_id );
            }

            continue;
        }

        $term_id = absint( $term_id );

        if( $term_id === 0 ) {
            continue;
        }

        $terms_ids[] = $term_id;

    }

    ct_reset_setup_table();

    // Update relationships
    $ct_table = ct_setup_table( $table . '_relationships' );
    ct_update_object_relationships( $link_id, $terms_ids );
    ct_reset_setup_table();


}

/**
 * On delete
 *
 * @since 1.0.0
 *
 * @param int $object_id
 */
function shortlinkspro_links_on_delete_object( $object_id ) {

    global $wpdb, $ct_table;

    if( ! ( $ct_table instanceof CT_Table ) ) {
        return;
    }

    if( $ct_table->name !== 'shortlinkspro_links' ) {
        return;
    }

    // Delete categories relationships
    $ct_table = ct_setup_table( 'shortlinkspro_link_categories_relationships' );
    ct_delete_object_relationships( $object_id );
    ct_reset_setup_table();

    // Delete tags relationships
    $ct_table = ct_setup_table( 'shortlinkspro_link_tags_relationships' );
    ct_delete_object_relationships( $object_id );
    ct_reset_setup_table();

    /**
     * Filter available to decide if link clicks should be deleted when a link gets deleted
     *
     * @since 1.0.0
     *
     * @param bool  $delete
     * @param int   $form_id
     *
     * @return bool
     */
    if( ! apply_filters( 'shortlinkspro_delete_clicks_on_delete_link', true, $object_id ) ) {
        return;
    }

    $ct_table = ct_setup_table( 'shortlinkspro_clicks' );

    $clicks = $ct_table->db->table_name;
    $clicks_meta = $ct_table->meta->db->table_name;

    // Delete all submissions assigned to this form
    $wpdb->query( "DELETE c FROM {$clicks} AS c WHERE c.link_id = {$object_id}" );

    // Delete orphaned submissions metas
    $wpdb->query( "DELETE cm FROM {$clicks_meta} cm LEFT JOIN {$clicks} c ON c.id = cm.id WHERE c.id IS NULL" );

    ct_reset_setup_table();

}
add_action( 'delete_object', 'shortlinkspro_links_on_delete_object' );