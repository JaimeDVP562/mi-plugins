<?php
/**
 * Settings
 *
 * @package GamiPress\Credly\Admin\Settings
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Plugin Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_credly_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_credly_';

    $meta_boxes['gamipress-credly-settings'] = array(
        'title' => gamipress_dashicon( 'awards' ) . __( 'Credly', 'gamipress-credly' ),
        'vertical_tabs' => true,
        'tabs' => apply_filters( 'gamipress_credly_settings_tabs', array(
            'auth' => array(
                'title' => __( 'Authorization', 'gamipress-credly' ),
                'icon' => 'dashicons-admin-network',
                'fields' => array(
                    $prefix . 'token',
                    $prefix . 'authorization_status',
                    $prefix . 'authorize',
                    
                )
            ),
            'sync_options' => array(
                'title' => __( 'Sync Options', 'gamipress-credly' ),
                'icon' => 'dashicons-update',
                'fields' => array(
                    $prefix . 'auto_sync_users',
                )
            ),
            'achievements' => array(
                'title' => __( 'Import Achievements', 'gamipress-credly' ),
                'icon' => 'dashicons-awards',
                'fields' => array(
                    $prefix . 'achievement_type',
                    $prefix . 'post_status',
                    $prefix . 'auto_sync_status',
                    $prefix . 'enable_update_badge',
                    $prefix . 'import_achievements',
                )
            ),
        ) ),
        'fields' => apply_filters( 'gamipress_credly_settings_fields', array(

            // Authorization
            $prefix . 'token' => array(
                'name' => __( 'Authorization token', 'gamipress-credly' ),
                'type' => 'text',
            ),

            $prefix . 'authorization_status' => array(
                'name' => __( 'Authorization Status', 'gamipress-credly' ),
                'type' => 'html',
                'content_cb' => 'gamipress_credly_authorization_status_cb'
            ),
            $prefix . 'authorize' => array(
                'label' => __( 'Authorize', 'gamipress-credly' ),
                'type' => 'button',
                'button' => 'primary',
            ),

            // Sync Options

            $prefix . 'auto_sync_users' => array(
                'name' => __( 'Sync Users Automatically', 'gamipress-credly' ),
                'desc' => __( 'Check this option to sync user account automatically if the user email matches with his Credly account email.', 'gamipress-credly' )
                . '<br>' . __( 'Users who don\'t use the same email as in Credly will need to perform this connection manually through the [gamipress_credly_login].', 'gamipress-credly' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),

            // Achievements

            $prefix . 'achievement_type' => array(
                'name' => __( 'Achievement Type', 'gamipress-credly' ),
                'desc' => __( 'Choose the achievement type to store the imported achievements from Credly.', 'gamipress-credly' ),
                'type' => 'select',
                'option_all'  => false,
                'option_none' => true,
                'options_cb' => 'gamipress_options_cb_achievement_types',
            ),
            $prefix . 'post_status' => array(
                'name' => __( 'Status', 'gamipress-credly' ),
                'desc' => __( 'The status to set to achievement imported.', 'gamipress-credly' ),
                'type' => 'select',
                'options' => array(
                    'draft' => __( 'Draft', 'gamipress-credly' ),
                    'pending' => __( 'Pending', 'gamipress-credly' ),
                    'publish' => __( 'Published', 'gamipress-credly' ),
                ),
            ),
            $prefix . 'auto_sync_status' => array(
                'name' => __( 'Force status to existent achievements', 'gamipress-credly' ),
                'desc' => __( 'Check this option to change the status to already imported achievements.', 'gamipress-credly' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'enable_update_badge' => array(
                'name' => __( 'Update existent achievements', 'gamipress-credly' ),
                'desc' => __( 'Check this option to update already imported achievements titles and descriptions.', 'gamipress-credly' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'import_achievements' => array(
                'label' => __( 'Import Achievements', 'gamipress-credly' ),
                'type' => 'button',
                'button' => 'primary',
            ),


        ) )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_credly_settings_meta_boxes' );

// Authorization status
function gamipress_credly_authorization_status_cb() {
    $auth = gamipress_credly_get_authorization_code(); ?>

    <div style="padding: 5px 0; color: <?php echo ( $auth ? '#37863e' : '#a00' ); ?>;">
        <?php if( $auth ) {
            _e( 'Connected', 'gamipress-credly' );
        } else {
            _e( 'Not connected', 'gamipress-credly' );
        } ?>
    </div>
    <?php
}