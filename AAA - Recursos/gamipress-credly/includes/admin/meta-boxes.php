<?php
/**
 * Meta Boxes
 *
 * @package GamiPress\Credly\Admin\Meta_Boxes
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register achievements meta boxes
 *
 * @param string $post_type
 *
 * @since 1.0.0
 */
function gamipress_credly_achievements_meta_boxes( $post_type ) {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_credly_';

    // Grab our achievement types slugs
    $achievement_types = gamipress_get_achievement_types_slugs();

    if( ! in_array( $post_type, $achievement_types ) ) {
        return;
    }

    // Achievement Data
    gamipress_add_meta_box(
        'credly-data',
        __( 'Credly Data', 'gamipress-credly' ),
        $achievement_types,
        array(
            $prefix . 'sync' => array(
                'type' => 'title',
                'before' => 'gamipress_credly_sync_status', 
                'after' => 'gamipress_credly_remote_link_cb',
            ),
            
            
        )
    );

}
add_action( 'gamipress_init_meta_boxes', 'gamipress_credly_achievements_meta_boxes' );

function gamipress_credly_sync_status( $field_args, $field) {
    $prefix = '_gamipress_credly_';

    $remote_id = gamipress_get_post_meta( $field->object_id, $prefix . 'remote_id', true );
    $state = gamipress_get_post_meta( $field->object_id, $prefix . 'state', true );

    if ( ! empty( $remote_id ) && $state == 'active' ){       
        echo '<p><span class="dashicons dashicons-yes" style="color:green"></span>' . __( 'Achievement synced with Credly.', 'gamipress-credly' ) . '</p>';
    }
    else {
            echo '<p><span class="dashicons dashicons-no" style="color:red"></span>' . __( 'Achievement not synced with Credly.', 'gamipress-credly' ) . '</p>';
    }
}

function gamipress_credly_remote_link_cb( $field_args, $field ) {

    $prefix = '_gamipress_credly_';

    $remote_id = gamipress_get_post_meta( $field->object_id, $prefix . 'remote_id', true );
    $state = gamipress_get_post_meta( $field->object_id, $prefix . 'state', true );

    // Get badge Credly url
    $url = gamipress_get_post_meta( $field->object_id, $prefix . 'url', true );

    if( ! empty( $remote_id ) && $state == 'active' ) : ?>
        <a href=<?php echo $url; ?> target='_blank'><?php _e( 'See achievement on Credly', 'gamipress-credly' ); ?></a>
    <?php endif;

}
