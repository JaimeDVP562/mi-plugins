<?php
/**
 * Auto-Create Links Meta Box for Post Edit Screens
 *
 * @package     ShortLinksPro\Auto_Create_Links
 * @author      ShortLinks Pro <contact@shortlinkspro.com>
 * @since       1.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the Auto-Create Links meta box on selected post types
 *
 * @since 1.2.0
 */
function shortlinkspro_auto_create_links_register_meta_box() {

    $enabled_post_types = shortlinkspro_get_option( 'auto_create_post_types', array() );

    if( ! is_array( $enabled_post_types ) || empty( $enabled_post_types ) ) {
        return;
    }

    foreach( $enabled_post_types as $post_type ) {
        add_meta_box(
            'shortlinkspro-auto-create-links',
            __( 'SLP - Auto-Create Links', 'shortlinkspro' ),
            'shortlinkspro_auto_create_links_meta_box_render',
            $post_type,
            'side',
            'default'
        );
    }

}
add_action( 'add_meta_boxes', 'shortlinkspro_auto_create_links_register_meta_box' );

/**
 * Render the Auto-Create Links meta box
 *
 * @since 1.2.0
 *
 * @param WP_Post $post
 */
function shortlinkspro_auto_create_links_meta_box_render( $post ) {

    // Always lock things down tight with a nonce for security
    wp_nonce_field( 'shortlinkspro_auto_create_link_save', 'shortlinkspro_auto_create_link_nonce' );

    $post_type = $post->post_type;

    // Get the default settings from the Settings page for this post type
    $default_prefix = shortlinkspro_get_option( 'slug_prefix', '' );
    $pt_prefix = shortlinkspro_get_option( 'auto_create_prefix_' . $post_type, $default_prefix );
    $pt_category = shortlinkspro_get_option( 'auto_create_category_' . $post_type, '' );
    $pt_tags = shortlinkspro_get_option( 'auto_create_tags_' . $post_type, array() );
    $pt_redirect = shortlinkspro_get_option( 'auto_create_redirect_' . $post_type, '307' );
    $pt_link_options = shortlinkspro_get_option( 'auto_create_link_options_' . $post_type, array( 'nofollow', 'tracking' ) );

    if( ! is_array( $pt_link_options ) ) {
        $pt_link_options = array();
    }
    if( ! is_array( $pt_tags ) ) {
        $pt_tags = array();
    }

    // Before we go any further, let's see if this post already has a link, so we don't accidentally create duplicates.
    $existing_link_id = get_post_meta( $post->ID, '_shortlinkspro_link_id', true );
    $existing_link = null;
    $link_slug = '';
    $link_category = $pt_category;
    $link_tags = $pt_tags;
    $link_redirect = $pt_redirect;
    $link_options = $pt_link_options;

    if( ! empty( $existing_link_id ) ) {
        // Cool, we found a link ID. Let's pull up all the details about it.
        $ct_table = ct_setup_table( 'shortlinkspro_links' );
        $existing_link = ct_get_object( absint( $existing_link_id ) );
        ct_reset_setup_table();

        if( $existing_link ) {
            $link_slug = $existing_link->slug;
            $link_redirect = $existing_link->redirect_type;

            // Load link options from existing link
            $link_options = array();
            if( $existing_link->nofollow ) $link_options[] = 'nofollow';
            if( $existing_link->sponsored ) $link_options[] = 'sponsored';
            if( $existing_link->parameter_forwarding ) $link_options[] = 'parameter_forwarding';
            if( $existing_link->tracking ) $link_options[] = 'tracking';

            // Load category from relationship
            ct_setup_table( 'shortlinkspro_link_categories_relationships' );
            $cat_terms = ct_get_object_terms( absint( $existing_link_id ) );
            ct_reset_setup_table();

            if( $cat_terms && ! is_array( $cat_terms ) ) {
                $link_category = $cat_terms->id;
            } elseif( is_array( $cat_terms ) && ! empty( $cat_terms ) ) {
                $link_category = $cat_terms[0]->id;
            }

            // Load tags from relationship
            ct_setup_table( 'shortlinkspro_link_tags_relationships' );
            $tag_terms = ct_get_object_terms( absint( $existing_link_id ) );
            ct_reset_setup_table();

            $link_tags = array();
            if( $tag_terms ) {
                if( ! is_array( $tag_terms ) ) {
                    $tag_terms = array( $tag_terms );
                }
                foreach( $tag_terms as $t ) {
                    $link_tags[] = $t->id;
                }
            }
        }
    }

    // Let's whip up a shiny new preview slug if there isn't one yet
    if( empty( $link_slug ) ) {
        $length = absint( shortlinkspro_get_option( 'slug_length', '4' ) );
        $link_slug = shortlinkspro_generate_link_slug( $pt_prefix, $length );
    }

    // Get redirect types
    $redirect_types = shortlinkspro_redirect_types();

    // Get categories and tags
    global $wpdb;
    $ct_table = ct_setup_table( 'shortlinkspro_link_categories' );
    $categories = $wpdb->get_results( "SELECT id, name FROM {$ct_table->db->table_name} ORDER BY name ASC" );
    ct_reset_setup_table();

    $ct_table = ct_setup_table( 'shortlinkspro_link_tags' );
    $all_tags = $wpdb->get_results( "SELECT id, name FROM {$ct_table->db->table_name} ORDER BY name ASC" );
    ct_reset_setup_table();

    ?>
    <div class="shortlinkspro-auto-create-meta-box">

        <p class="shortlinkspro-auto-create-info">
            <?php if( $existing_link ) : ?>
                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                <?php esc_html_e( 'A BetterLink for this post already exists.', 'shortlinkspro' ); ?>
            <?php else : ?>
                <span class="dashicons dashicons-info" style="color: #0073aa;"></span>
                <?php esc_html_e( 'A BetterLink for this post will be generated on publish.', 'shortlinkspro' ); ?>
            <?php endif; ?>
        </p>

        <!-- Slug -->
        <p>
            <label><strong><?php esc_html_e( 'ShortLink', 'shortlinkspro' ); ?></strong></label><br>
            <span class="shortlinkspro-auto-create-site-url"><?php echo esc_html( site_url('/') ); ?></span><br>
            <input type="text" name="shortlinkspro_auto_create_slug" value="<?php echo esc_attr( $link_slug ); ?>" class="widefat" />
        </p>

        <!-- Category -->
        <p>
            <label><strong><?php esc_html_e( 'BetterLinks Category', 'shortlinkspro' ); ?></strong></label><br>
            <select name="shortlinkspro_auto_create_category" class="widefat">
                <option value=""><?php esc_html_e( 'Uncategorized', 'shortlinkspro' ); ?></option>
                <?php foreach( $categories as $cat ) : ?>
                    <option value="<?php echo esc_attr( $cat->id ); ?>" <?php selected( $link_category, $cat->id ); ?>><?php echo esc_html( $cat->name ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <!-- Tags -->
        <p>
            <label><strong><?php esc_html_e( 'Tags', 'shortlinkspro' ); ?></strong></label><br>
            <select name="shortlinkspro_auto_create_tags[]" class="widefat" multiple="multiple" style="min-height: 60px;">
                <?php foreach( $all_tags as $tag ) : ?>
                    <option value="<?php echo esc_attr( $tag->id ); ?>" <?php echo in_array( $tag->id, $link_tags ) ? 'selected' : ''; ?>><?php echo esc_html( $tag->name ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <!-- Redirect Type -->
        <p>
            <label><strong><?php esc_html_e( 'Redirect Type', 'shortlinkspro' ); ?></strong></label><br>
            <select name="shortlinkspro_auto_create_redirect" class="widefat">
                <?php foreach( $redirect_types as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $link_redirect, $value ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <!-- Link Options -->
        <p>
            <label><strong><?php esc_html_e( 'Link Options', 'shortlinkspro' ); ?></strong></label><br>
            <label><input type="checkbox" name="shortlinkspro_auto_create_options[]" value="nofollow" <?php checked( in_array( 'nofollow', $link_options ) ); ?> /> <?php esc_html_e( 'No Follow', 'shortlinkspro' ); ?></label><br>
            <label><input type="checkbox" name="shortlinkspro_auto_create_options[]" value="sponsored" <?php checked( in_array( 'sponsored', $link_options ) ); ?> /> <?php esc_html_e( 'Sponsored', 'shortlinkspro' ); ?></label><br>
            <label><input type="checkbox" name="shortlinkspro_auto_create_options[]" value="parameter_forwarding" <?php checked( in_array( 'parameter_forwarding', $link_options ) ); ?> /> <?php esc_html_e( 'Parameter Forwarding', 'shortlinkspro' ); ?></label><br>
            <label><input type="checkbox" name="shortlinkspro_auto_create_options[]" value="tracking" <?php checked( in_array( 'tracking', $link_options ) ); ?> /> <?php esc_html_e( 'Tracking', 'shortlinkspro' ); ?></label>
        </p>

        <?php if( $existing_link ) : ?>
            <p>
                <a href="<?php echo esc_attr( ct_get_edit_link( 'shortlinkspro_links', $existing_link->id ) ); ?>" target="_blank">
                    <?php esc_html_e( 'Edit link in ShortLinks Pro', 'shortlinkspro' ); ?> →
                </a>
            </p>
        <?php endif; ?>

        <input type="hidden" name="shortlinkspro_auto_create_existing_link_id" value="<?php echo esc_attr( $existing_link_id ); ?>" />
    </div>
    <?php
}

/**
 * Save the auto-create link data when the post is saved
 * Creates or updates the link directly in ShortLinks Pro
 *
 * @since 1.2.0
 *
 * @param int $post_id
 * @param WP_Post $post
 * @param bool $update
 */
function shortlinkspro_auto_create_links_save_post( $post_id, $post, $update ) {

    // Verify nonce
    if( ! isset( $_POST['shortlinkspro_auto_create_link_nonce'] )
        || ! wp_verify_nonce( $_POST['shortlinkspro_auto_create_link_nonce'], 'shortlinkspro_auto_create_link_save' ) ) {
        return;
    }

    // Bail on autosave
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check user permissions
    if( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Bail if not in admin
    if( ! is_admin() ) {
        return;
    }

    // Bail if this post type is not enabled
    $enabled_post_types = shortlinkspro_get_option( 'auto_create_post_types', array() );
    if( ! is_array( $enabled_post_types ) || ! in_array( $post->post_type, $enabled_post_types ) ) {
        return;
    }

    // We only care about doing this if the post has actually been published - draft links? No thanks!
    if( $post->post_status !== 'publish' ) {
        return;
    }

    // Alright, let's grab all that sweet data that the user just submitted
    $slug = isset( $_POST['shortlinkspro_auto_create_slug'] ) ? sanitize_text_field( $_POST['shortlinkspro_auto_create_slug'] ) : '';
    $category = isset( $_POST['shortlinkspro_auto_create_category'] ) ? absint( $_POST['shortlinkspro_auto_create_category'] ) : 0;
    $tags = isset( $_POST['shortlinkspro_auto_create_tags'] ) ? array_map( 'absint', $_POST['shortlinkspro_auto_create_tags'] ) : array();
    $redirect_type = isset( $_POST['shortlinkspro_auto_create_redirect'] ) ? sanitize_text_field( $_POST['shortlinkspro_auto_create_redirect'] ) : '307';
    $link_options = isset( $_POST['shortlinkspro_auto_create_options'] ) ? array_map( 'sanitize_text_field', $_POST['shortlinkspro_auto_create_options'] ) : array();
    $existing_link_id = isset( $_POST['shortlinkspro_auto_create_existing_link_id'] ) ? absint( $_POST['shortlinkspro_auto_create_existing_link_id'] ) : 0;

    // Build link data
    $link_data = array(
        'title'                 => $post->post_title,
        'url'                   => get_permalink( $post_id ),
        'slug'                  => $slug,
        'redirect_type'         => $redirect_type,
        'nofollow'              => absint( in_array( 'nofollow', $link_options ) ),
        'sponsored'             => absint( in_array( 'sponsored', $link_options ) ),
        'parameter_forwarding'  => absint( in_array( 'parameter_forwarding', $link_options ) ),
        'tracking'              => absint( in_array( 'tracking', $link_options ) ),
        'author_id'             => get_current_user_id(),
        'updated_at'            => gmdate( 'Y-m-d H:i:s' ),
    );

    // Setup table
    $ct_table = ct_setup_table( 'shortlinkspro_links' );

    if( $existing_link_id > 0 ) {
        // Update existing link
        $link_data['id'] = $existing_link_id;
        ct_update_object( $link_data );
        $link_id = $existing_link_id;
    } else {
        // Create new link
        $link_data['created_at'] = gmdate( 'Y-m-d H:i:s' );
        $link_id = ct_insert_object( $link_data );
    }

    ct_reset_setup_table();

    if( $link_id ) {
        $link_id = absint( $link_id );

        // Save post meta with the link ID
        update_post_meta( $post_id, '_shortlinkspro_link_id', $link_id );

        // Save link metas (relationship Link -> Post)
        shortlinkspro_update_link_meta( $link_id, 'auto_create_post_id', $post_id );
        shortlinkspro_update_link_meta( $link_id, 'auto_create_post_type', $post->post_type );

        // Handle category relationship
        if( $category > 0 ) {
            ct_setup_table( 'shortlinkspro_link_categories_relationships' );
            ct_set_object_terms( $link_id, $category );
            ct_reset_setup_table();
        }

        // Handle tags relationship
        if( ! empty( $tags ) ) {
            ct_setup_table( 'shortlinkspro_link_tags_relationships' );
            ct_set_object_terms( $link_id, $tags );
            ct_reset_setup_table();
        }
    }

}
add_action( 'save_post', 'shortlinkspro_auto_create_links_save_post', 10, 3 );
