<?php
/**
 * Admin Auto-Create Links Settings
 *
 * @package     ShortLinksPro\Auto_Create_Links
 * @author      ShortLinks Pro <contact@shortlinkspro.com>
 * @since       1.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Auto-Create Links settings section
 *
 * @since 1.2.0
 *
 * @param array $sections
 *
 * @return array
 */
function shortlinkspro_settings_auto_create_links_section( $sections ) {

    $sections['auto_create_links'] = array(
        'title' => __( 'Auto-Create Links', 'shortlinkspro' ),
        'icon' => 'dashicons-admin-links',
    );

    return $sections;

}
add_filter( 'shortlinkspro_settings_sections', 'shortlinkspro_settings_auto_create_links_section' );

/**
 * Auto-Create Links Settings meta boxes
 *
 * @since  1.2.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function shortlinkspro_settings_auto_create_links_meta_boxes( $meta_boxes ) {

    // Let's gather up all the public post types so the user can pick and choose
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    $post_type_options = array();

    foreach( $post_types as $post_type ) {
        // We definitely don't want to auto-create links for mere attachments!
        if( $post_type->name === 'attachment' ) {
            continue;
        }
        $post_type_options[ $post_type->name ] = $post_type->labels->name;
    }

    $meta_boxes['auto_create_links_settings'] = array(
        'title' => shortlinkspro_dashicon( 'admin-links' ) . __( 'Auto-Create Links', 'shortlinkspro' ),
        'fields' => apply_filters( 'shortlinkspro_auto_create_links_settings_fields', array(
            'auto_create_post_types' => array(
                'name'      => __( 'Post Types', 'shortlinkspro' ),
                'desc'      => __( 'Select the post types for which links will be auto-created.', 'shortlinkspro' ),
                'type'      => 'multicheck_inline',
                'options'   => $post_type_options,
                'select_all_button' => false,
                'tooltip'   => __( 'Check the post types where you want to automatically create short links.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'shortlinkspro_settings_auto_create_links_meta_boxes', 'shortlinkspro_settings_auto_create_links_meta_boxes' );

/**
 * Render Auto-Create Links per-post-type settings via custom HTML after the settings page
 *
 * @since 1.2.0
 */
function shortlinkspro_auto_create_links_after_settings() {

    // Let's make sure we only inject this setup stuff on our own specific settings page
    if( ! isset( $_GET['page'] ) || $_GET['page'] !== 'shortlinkspro_settings' ) {
        return;
    }

    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    $saved_post_types = shortlinkspro_get_option( 'auto_create_post_types', array() );

    if( ! is_array( $saved_post_types ) ) {
        $saved_post_types = array();
    }

    // Get default prefix from General settings
    $default_prefix = shortlinkspro_get_option( 'slug_prefix', '' );

    // Get redirect types
    $redirect_types = shortlinkspro_redirect_types();

    // Get categories for dropdown
    global $wpdb;
    $ct_table = ct_setup_table( 'shortlinkspro_link_categories' );
    $categories = $wpdb->get_results( "SELECT id, name FROM {$ct_table->db->table_name} ORDER BY name ASC" );
    ct_reset_setup_table();

    // Get tags for dropdown
    $ct_table = ct_setup_table( 'shortlinkspro_link_tags' );
    $tags = $wpdb->get_results( "SELECT id, name FROM {$ct_table->db->table_name} ORDER BY name ASC" );
    ct_reset_setup_table();

    ?>
    <div id="shortlinkspro-auto-create-links-settings" style="display: none;">
        <?php foreach( $post_types as $post_type ) :
            if( $post_type->name === 'attachment' ) continue;

            // Get per-post-type saved settings
            $pt_prefix = shortlinkspro_get_option( 'auto_create_prefix_' . $post_type->name, $default_prefix );
            $pt_category = shortlinkspro_get_option( 'auto_create_category_' . $post_type->name, '' );
            $pt_tags = shortlinkspro_get_option( 'auto_create_tags_' . $post_type->name, array() );
            $pt_redirect = shortlinkspro_get_option( 'auto_create_redirect_' . $post_type->name, '307' );
            $pt_link_options = shortlinkspro_get_option( 'auto_create_link_options_' . $post_type->name, array( 'nofollow', 'tracking' ) );

            if( ! is_array( $pt_link_options ) ) {
                $pt_link_options = array();
            }
            if( ! is_array( $pt_tags ) ) {
                $pt_tags = array();
            }

            // Count links that belong to this post type
            $link_count = shortlinkspro_count_auto_created_links_for_post_type( $post_type->name );
        ?>
        <div class="shortlinkspro-auto-create-pt-settings" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" style="display: none;">
            <h3><?php echo esc_html( $post_type->labels->name ); ?> — <?php esc_html_e( 'Auto-Create Link Settings', 'shortlinkspro' ); ?></h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Prefix', 'shortlinkspro' ); ?></label></th>
                    <td>
                        <input type="text"
                               name="auto_create_prefix_<?php echo esc_attr( $post_type->name ); ?>"
                               class="regular-text shortlinkspro-auto-create-prefix"
                               value="<?php echo esc_attr( $pt_prefix ); ?>"
                               placeholder="<?php echo esc_attr( $default_prefix ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Category', 'shortlinkspro' ); ?></label></th>
                    <td>
                        <select name="auto_create_category_<?php echo esc_attr( $post_type->name ); ?>" class="shortlinkspro-auto-create-category">
                            <option value=""><?php esc_html_e( '— None —', 'shortlinkspro' ); ?></option>
                            <?php foreach( $categories as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->id ); ?>" <?php selected( $pt_category, $cat->id ); ?>><?php echo esc_html( $cat->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Tags', 'shortlinkspro' ); ?></label></th>
                    <td>
                        <select name="auto_create_tags_<?php echo esc_attr( $post_type->name ); ?>[]" class="shortlinkspro-auto-create-tags" multiple="multiple">
                            <?php foreach( $tags as $tag ) : ?>
                                <option value="<?php echo esc_attr( $tag->id ); ?>" <?php echo in_array( $tag->id, $pt_tags ) ? 'selected' : ''; ?>><?php echo esc_html( $tag->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Redirect Type', 'shortlinkspro' ); ?></label></th>
                    <td>
                        <select name="auto_create_redirect_<?php echo esc_attr( $post_type->name ); ?>" class="shortlinkspro-auto-create-redirect">
                            <?php foreach( $redirect_types as $value => $label ) : ?>
                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $pt_redirect, $value ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Link Options', 'shortlinkspro' ); ?></label></th>
                    <td>
                        <label><input type="checkbox" name="auto_create_link_options_<?php echo esc_attr( $post_type->name ); ?>[]" value="nofollow" <?php checked( in_array( 'nofollow', $pt_link_options ) ); ?> /> <?php esc_html_e( 'No Follow', 'shortlinkspro' ); ?></label><br>
                        <label><input type="checkbox" name="auto_create_link_options_<?php echo esc_attr( $post_type->name ); ?>[]" value="sponsored" <?php checked( in_array( 'sponsored', $pt_link_options ) ); ?> /> <?php esc_html_e( 'Sponsored', 'shortlinkspro' ); ?></label><br>
                        <label><input type="checkbox" name="auto_create_link_options_<?php echo esc_attr( $post_type->name ); ?>[]" value="parameter_forwarding" <?php checked( in_array( 'parameter_forwarding', $pt_link_options ) ); ?> /> <?php esc_html_e( 'Parameter Forwarding', 'shortlinkspro' ); ?></label><br>
                        <label><input type="checkbox" name="auto_create_link_options_<?php echo esc_attr( $post_type->name ); ?>[]" value="tracking" <?php checked( in_array( 'tracking', $pt_link_options ) ); ?> /> <?php esc_html_e( 'Tracking', 'shortlinkspro' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Actions', 'shortlinkspro' ); ?></label></th>
                    <td>
                        <div class="shortlinkspro-auto-create-buttons" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" data-link-count="<?php echo esc_attr( $link_count ); ?>">
                            <button type="button" class="button button-primary shortlinkspro-auto-create-btn" data-action="create">
                                <?php esc_html_e( 'Create Links', 'shortlinkspro' ); ?>
                            </button>
                            <?php if( $link_count > 0 ) : ?>
                            <button type="button" class="button shortlinkspro-auto-create-btn" data-action="update">
                                <?php esc_html_e( 'Update Links', 'shortlinkspro' ); ?>
                            </button>
                            <button type="button" class="button shortlinkspro-auto-create-btn shortlinkspro-auto-delete-btn" data-action="delete">
                                <?php esc_html_e( 'Delete Links', 'shortlinkspro' ); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="shortlinkspro-auto-create-status" style="margin-top: 10px;"></div>
                    </td>
                </tr>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}
add_action( 'admin_footer', 'shortlinkspro_auto_create_links_after_settings' );

/**
 * Count auto-created links for a specific post type
 *
 * @since 1.2.0
 *
 * @param string $post_type
 *
 * @return int
 */
function shortlinkspro_count_auto_created_links_for_post_type( $post_type ) {

    global $wpdb;

    $ct_table = ct_setup_table( 'shortlinkspro_links' );
    $meta_table = $ct_table->meta->db->table_name;
    $table_name = $ct_table->db->table_name;

    // Let's dive into the DB and count how many links are actually attached to this post type.
    // We basically just make sure the link has our special 'auto_create_post_id' and 'auto_create_post_type' metas!
    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT l.id)
        FROM {$table_name} AS l
        INNER JOIN {$meta_table} AS lm1 ON ( l.id = lm1.id AND lm1.meta_key = 'auto_create_post_id' )
        INNER JOIN {$meta_table} AS lm2 ON ( l.id = lm2.id AND lm2.meta_key = 'auto_create_post_type' AND lm2.meta_value = %s )",
        $post_type
    ) );

    ct_reset_setup_table();

    return absint( $count );

}
