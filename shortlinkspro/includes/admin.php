<?php
/**
 * Admin
 *
 * @package     ShortLinksPro\Admin
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Admin includes
require_once SHORTLINKSPRO_DIR . 'includes/admin/notices.php';
// Admin pages
require_once SHORTLINKSPRO_DIR . 'includes/admin/pages/tools.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/pages/settings.php';
require_once SHORTLINKSPRO_DIR . 'includes/admin/pages/add-ons.php';

/**
 * Helper function to get an option value.
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed Option value or default parameter value if not exists.
 */
function shortlinkspro_get_option( $option_name, $default = false ) {

    if( ShortLinksPro()->settings === null ) {
        ShortLinksPro()->settings = get_option( 'shortlinkspro_settings' );
    }

    return isset( ShortLinksPro()->settings[ $option_name ] ) ? ShortLinksPro()->settings[ $option_name ] : $default;

}

/**
 * Admin menus
 *
 * @since   1.0.0
 */
function shortlinkspro_admin_menu() {

    $minimum_role = shortlinkspro_get_manager_capability();

    // Main menu
    add_menu_page( 'ShortLinks Pro', 'ShortLinks Pro', $minimum_role, 'shortlinkspro', '', 'dashicons-shortlinkspro', 50 );

}
add_action( 'admin_menu', 'shortlinkspro_admin_menu' );

/**
 * Fix admin menu
 *
 * @since   1.0.0
 */
function shortlinkspro_fix_admin_menu() {

    global $menu, $submenu;

    // Actually the page "shortlinkspro" does not exist, so we need to remove it to keep "shortlinkspro_links" as first menu
    // This lets us to keep the parent page "shortlinkspro" but not display it
    unset( $submenu['shortlinkspro'][0] );

}
add_action( 'admin_menu', 'shortlinkspro_fix_admin_menu', 9999 );

/**
 * Helper function to get the try of the day
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function shortlinkspro_get_try_option() {

    $options = array();

    if( ! class_exists( 'GamiPress' ) ) {
        $options[] = array(
            'label' => __( 'Try GamiPress!', 'shortlinkspro' ),
            'slug' => 'gamipress'
        );
    }

    if( ! class_exists( 'AutomatorWP' ) ) {
        $options[] = array(
            'label' => __( 'Try AutomatorWP!', 'shortlinkspro' ),
            'slug' => 'automatorwp'
        );
    }

    if( ! class_exists( 'BBForms' ) ) {
        $options[] = array(
            'label' => __( 'Try BBForms!', 'shortlinkspro' ),
            'slug' => 'bbforms'
        );
    }

    $count = count( $options );

    // Bail if no options found
    if( $count === 0 ) return false;

    $day = absint( gmdate( 'd' ) );
    $index = $day % $count;

    if( ! isset( $options[ $index ] ) ) {
        $index = 0;
    }

    return $options[ $index ];

}

/**
 * Add Try submenu
 *
 * @since 1.0.0
 */
function shortlinkspro_try_admin_submenu() {

    $option = shortlinkspro_get_try_option();

    if( ! $option ) return;

    // Set minimum role for menu
    $minimum_role = shortlinkspro_get_manager_capability();

    $badge = '<span class="shortlinkspro-admin-menu-badge">' . __( 'New', 'shortlinkspro' ) . '</span>';

    add_submenu_page( 'shortlinkspro', $option['label'], $option['label'] . $badge, $minimum_role, 'https://wordpress.org/plugins/' . $option['slug'] . '/', null );
}
add_action( 'admin_menu', 'shortlinkspro_try_admin_submenu', 9999 );

/**
 * Admin menus
 *
 * @since   1.0.0
 */
function shortlinkspro_admin_submenu() {

    $minimum_role = shortlinkspro_get_manager_capability();

    // Add-ons submenu
    add_submenu_page( 'shortlinkspro', __( 'Add-ons', 'shortlinkspro' ), __( 'Add-ons', 'shortlinkspro' ), $minimum_role, 'shortlinkspro_add_ons', 'shortlinkspro_add_ons_page' );

}
add_action( 'admin_menu', 'shortlinkspro_admin_submenu', 15 );

/**
 * Add the admin bar menu
 *
 * @since 1.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function shortlinkspro_admin_bar_menu( $wp_admin_bar ) {

    // Bail if current user can't manage
    if ( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        return;
    }

    // Bail if admin bar menu disabled
    if( (bool) shortlinkspro_get_option( 'disable_admin_bar_menu', false ) ) {
        return;
    }

    // Main menu
    $wp_admin_bar->add_node( array(
        'id'    => 'shortlinkspro',
        'title'	=>	'<span class="ab-icon"></span>' . 'ShortLinks Pro',
        'meta'  => array( 'class' => 'shortlinkspro' ),
    ) );

    // Links
    $wp_admin_bar->add_node( array(
        'id'     => 'shortlinkspro-links',
        'title'  => __( 'Manage Links', 'shortlinkspro' ),
        'parent' => 'shortlinkspro',
        'href'   => admin_url( 'admin.php?page=shortlinkspro_links' )
    ) );

    // Add New Link
    $wp_admin_bar->add_node( array(
        'id'     => 'shortlinkspro-add-link',
        'title'  => __( 'Add New Link', 'shortlinkspro' ),
        'parent' => 'shortlinkspro',
        'href'   => admin_url( 'admin.php?page=add_shortlinkspro_links' )
    ) );

    // Clicks
    $wp_admin_bar->add_node( array(
        'id'     => 'shortlinkspro-clicks',
        'title'  => __( 'Clicks', 'shortlinkspro' ),
        'parent' => 'shortlinkspro',
        'href'   => admin_url( 'admin.php?page=shortlinkspro_clicks' )
    ) );

}
add_action( 'admin_bar_menu', 'shortlinkspro_admin_bar_menu', 100 );

/**
 * Add GamiPress admin bar menu
 *
 * @since 1.0.0
 */
function shortlinkspro_admin_bar_menu_bottom( $wp_admin_bar ) {

    // Bail if current user can't manage
    if ( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        return;
    }

    // Bail if admin bar menu disabled
    if( (bool) shortlinkspro_get_option( 'disable_admin_bar_menu', false ) ) {
        return;
    }

    // Tools
    $wp_admin_bar->add_node( array(
        'id'     => 'shortlinkspro-tools',
        'title'  => __( 'Tools', 'shortlinkspro' ),
        'parent' => 'shortlinkspro',
        'href'   => admin_url( 'admin.php?page=shortlinkspro_tools' )
    ) );

    // Settings
    $wp_admin_bar->add_node( array(
        'id'     => 'shortlinkspro-settings',
        'title'  => __( 'Settings', 'shortlinkspro' ),
        'parent' => 'shortlinkspro',
        'href'   => admin_url( 'admin.php?page=shortlinkspro_settings' )
    ) );

    // Add-ons
    $wp_admin_bar->add_node( array(
        'id'     => 'shortlinkspro-add-ons',
        'title'  => __( 'Add-ons', 'automatorwp' ),
        'parent' => 'shortlinkspro',
        'href'   => admin_url( 'admin.php?page=shortlinkspro_add_ons' )
    ) );

}
add_action( 'admin_bar_menu', 'shortlinkspro_admin_bar_menu_bottom', 999 );

/**
 * Add Try admin bar submenu
 *
 * @since 1.0.0
 *
 * @param object $wp_admin_bar The WordPress toolbar object
 */
function shortlinkspro_try_admin_bar_submenu( $wp_admin_bar ) {

    // Bail if current user can't manage
    if ( ! current_user_can( shortlinkspro_get_manager_capability() ) ) {
        return;
    }

    // Bail if admin bar menu disabled
    if( (bool) shortlinkspro_get_option( 'disable_admin_bar_menu', false ) ) {
        return;
    }

    $option = shortlinkspro_get_try_option();

    if( ! $option ) return;

    $badge = '<span class="shortlinkspro-admin-menu-badge">' . __( 'New', 'shortlinkspro' ) . '</span>';

    // Try AutomatorWP
    $wp_admin_bar->add_node( array(
        'id'     => 'shortlinkspro-try',
        'title'  => $option['label'] . $badge,
        'parent' => 'shortlinkspro',
        'href'   => 'https://wordpress.org/plugins/' . $option['slug'] . '/'
    ) );

}
add_action( 'admin_bar_menu', 'shortlinkspro_try_admin_bar_submenu', 9999 );


/**
 * Processes all GamiPress actions sent via POST and GET by looking for the 'shortlinkspro-action' request and running do_action() to call the function
 *
 * @since 1.4.8
 */
function shortlinkspro_process_actions() {
    if ( isset( $_POST['shortlinkspro-action'] ) ) {
        do_action( 'shortlinkspro_action_' . $_POST['shortlinkspro-action'], $_POST );
    }

    if ( isset( $_GET['shortlinkspro-action'] ) ) {
        do_action( 'shortlinkspro_action_' . $_GET['shortlinkspro-action'], $_GET );
    }
}
add_action( 'admin_init', 'shortlinkspro_process_actions' );

/**
 * Helper function to register custom meta boxes
 *
 * @since  1.0.8
 *
 * @param string 		$id
 * @param string 		$title
 * @param string|array 	$object_types
 * @param array 		$fields
 * @param array 		$args
 */
function shortlinkspro_add_meta_box( $id, $title, $object_types, $fields, $args = array() ) {

    // ID for hooks
    $hook_id = str_replace( '-', '_', $id );

    /**
     * Filter box fields to allow to extend it
     *
     * @since  1.0.0
     *
     * @param array $fields Box fields
     * @param array $args   Box args
     *
     * @return array
     */
    $fields = apply_filters( "shortlinkspro_{$hook_id}_fields", $fields, $args );

    foreach( $fields as $field_id => $field ) {

        $fields[$field_id]['id'] = $field_id;

        // Support for group fields
        if( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {

            foreach( $field['fields'] as $group_field_id => $group_field ) {

                $fields[$field_id]['fields'][$group_field_id]['id'] = $group_field_id;

            }

        }

    }

    $args = wp_parse_args( $args, array(
        'vertical_tabs' => false,
        'tabs'      	=> array(),
        'context'      	=> 'normal',
        'priority'     	=> 'default',
        'show_on_cb'    => '',
    ) );

    /**
     * Filter box tabs to allow extend it
     *
     * @since  1.0.8
     *
     * @param array $tabs   Box tabs
     * @param array $fields Box fields
     * @param array $args   Box args
     *
     * @return array
     */
    $tabs = apply_filters( "shortlinkspro_{$hook_id}_tabs", $args['tabs'], $fields, $args );

    // Parse tabs
    foreach( $tabs as $tab_id => $tab ) {

        $tabs[$tab_id]['id'] = $tab_id;

    }

    // Setup the final box arguments
    $box = array(
        'id'           	=> $id,
        'title'        	=> $title,
        'object_types' 	=> ! is_array( $object_types) ? array( $object_types ) : $object_types,
        'tabs'      	=> $tabs,
        'vertical_tabs' => $args['vertical_tabs'],
        'context'      	=> $args['context'],
        'priority'     	=> $args['priority'],
        'show_on_cb'    => $args['show_on_cb'],
        'classes'		=> 'shortlinkspro-form shortlinkspro-box-form',
        'fields' 		=> $fields
    );

    /**
     * Filter the final box args that will be passed to CMB2
     *
     * @since  1.0.0
     *
     * @param array 		$box            Final box args
     * @param string 		$id             Box id
     * @param string 		$title          Box title
     * @param string|array 	$object_types   Object types where box will appear
     * @param array 		$fields         Box fields
     * @param array 		$tabs           Box tabs
     * @param array 		$args           Box args
     */
    apply_filters( "shortlinkspro_{$hook_id}_box", $box, $id, $title, $object_types, $fields, $tabs, $args );

    // Instance the CMB2 box
    new_cmb2_box( $box );

}

/**
 * Add custom footer text to the admin dashboard
 *
 * @since	    1.0.0
 *
 * @param       string $footer_text The existing footer text
 *
 * @return      string
 */
function shortlinkspro_admin_footer_text( $footer_text ) {

    global $typenow;

    if (
        ( isset( $_GET['page'] ) && (
                $_GET['page'] === 'shortlinkspro'
                // Check pages that starts with our key
                || ( substr( $_GET['page'], 0, strlen('shortlinkspro_') ) === 'shortlinkspro_' )
                || ( substr( $_GET['page'], 0, strlen('edit_shortlinkspro_') ) === 'edit_shortlinkspro_' )
            )
        )
    ) {

        /* translators: %1$s: URL. %2$s: URL. %3$s: stars emoji. */
        $shortlinkspro_footer_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">ShortLinks Pro</a>! Please leave us a <a href="%2$s" target="_blank">%3$s</a> rating on WordPress.org', 'shortlinkspro' ),
            esc_attr( 'https://shortlinkspro.com' ),
            esc_attr( 'https://wordpress.org/support/plugin/shortlinkspro/reviews/?rate=5#new-post' ),
            '&#9733;&#9733;&#9733;&#9733;&#9733;'
        );

        return str_replace( '</span>', '', $footer_text ) . ' | ' . $shortlinkspro_footer_text . '</span>';

    } else {

        return $footer_text;

    }

}
add_filter( 'admin_footer_text', 'shortlinkspro_admin_footer_text' );