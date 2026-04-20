<?php
/**
 * Admin
 *
 * @package     BBForms\Admin
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Admin includes
require_once BBFORMS_DIR . 'includes/admin/notices.php';
// Admin pages
require_once BBFORMS_DIR . 'includes/admin/pages/settings.php';
require_once BBFORMS_DIR . 'includes/admin/pages/add-ons.php';

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
function bbforms_get_option( $option_name, $default = false ) {

    if( BBForms()->settings === null ) {
        BBForms()->settings = get_option( 'bbforms_settings' );
    }

    return isset( BBForms()->settings[ $option_name ] ) ? BBForms()->settings[ $option_name ] : $default;

}

/**
 * Admin menus
 *
 * @since   1.0.0
 */
function bbforms_admin_menu() {

    $minimum_role = bbforms_get_manager_capability();

    // Main menu
    add_menu_page( 'BBForms', 'BBForms', $minimum_role, 'bbforms', '', 'dashicons-bbforms', 50 );

}
add_action( 'admin_menu', 'bbforms_admin_menu' );

function bbforms_admin_submenu() {

    $minimum_role = bbforms_get_manager_capability();

    // Add new (link to list + extra parameters)
    add_submenu_page( 'bbforms', __( 'Add New', 'bbforms' ), __( 'Add New', 'bbforms' ), $minimum_role, 'admin.php?page=bbforms_forms#add-new', '', 11 );
}
add_action( 'admin_menu', 'bbforms_admin_submenu', 11 );

/**
 * Admin menus
 *
 * @since   1.0.0
 */
function bbforms_admin_submenu_bottom() {

    $minimum_role = bbforms_get_manager_capability();

    // Add-ons submenu
    add_submenu_page( 'bbforms', __( 'Add-ons', 'bbforms' ), __( 'Add-ons', 'bbforms' ), $minimum_role, 'bbforms_add_ons', 'bbforms_add_ons_page' );

}
add_action( 'admin_menu', 'bbforms_admin_submenu_bottom', 15 );

/**
 * Helper function to get the try of the day
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function bbforms_get_try_option() {

    $options = array();

    if( ! class_exists( 'GamiPress' ) ) {
        $options[] = array(
            'label' => __( 'Try GamiPress!', 'bbforms' ),
            'slug' => 'gamipress'
        );
    }

    if( ! class_exists( 'AutomatorWP' ) ) {
        $options[] = array(
            'label' => __( 'Try AutomatorWP!', 'bbforms' ),
            'slug' => 'automatorwp'
        );
    }

    if( ! class_exists( 'ShortLinksPro' ) ) {
        $options[] = array(
            'label' => __( 'Try ShortLinks Pro!', 'bbforms' ),
            'slug' => 'shortlinkspro'
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
function bbforms_try_admin_submenu() {

    $option = bbforms_get_try_option();

    if( ! $option ) return;

    // Set minimum role for menu
    $minimum_role = bbforms_get_manager_capability();

    $badge = '<span class="bbforms-admin-menu-badge">' . __( 'New', 'bbforms' ) . '</span>';

    add_submenu_page( 'bbforms', $option['label'], $option['label'] . $badge, $minimum_role, 'https://wordpress.org/plugins/' . $option['slug'] . '/', null );
}
add_action( 'admin_menu', 'bbforms_try_admin_submenu', 9999 );

/**
 * Fix admin menu
 *
 * @since   1.0.0
 */
function bbforms_fix_admin_menu() {

    global $menu, $submenu;

    // Actually the page "bbforms" does not exist, so we need to remove it to keep "bbforms_links" as first menu
    // This lets us to keep the parent page "bbforms" but not display it
    unset( $submenu['bbforms'][0] );

}
add_action( 'admin_menu', 'bbforms_fix_admin_menu', 9999 );

/**
 * Add the admin bar menu
 *
 * @since 1.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function bbforms_admin_bar_menu( $wp_admin_bar ) {

    // Bail if current user can't manage
    if ( ! current_user_can( bbforms_get_manager_capability() ) ) {
        return;
    }

    // Bail if admin bar menu disabled
    if( (bool) bbforms_get_option( 'disable_admin_bar_menu', false ) ) {
        return;
    }

    // Main menu
    $wp_admin_bar->add_node( array(
        'id'    => 'bbforms',
        'title'	=>	'<span class="ab-icon"></span>' . 'BBForms',
        'meta'  => array( 'class' => 'bbforms' ),
    ) );

    // Forms
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-forms',
        'title'  => __( 'Forms', 'bbforms' ),
        'parent' => 'bbforms',
        'href'   => admin_url( 'admin.php?page=bbforms_forms' )
    ) );

    // Add New
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-add',
        'title'  => __( 'Add New', 'bbforms' ),
        'parent' => 'bbforms',
        'href'   => admin_url( 'admin.php?page=bbforms_forms#add-new' )
    ) );

    // Submissions
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-submissions',
        'title'  => __( 'Submissions', 'bbforms' ),
        'parent' => 'bbforms',
        'href'   => admin_url( 'admin.php?page=bbforms_submissions' )
    ) );

    // Categories
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-categories',
        'title'  => __( 'Categories', 'bbforms' ),
        'parent' => 'bbforms',
        'href'   => admin_url( 'admin.php?page=bbforms_categories' )
    ) );

    // Tags
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-tags',
        'title'  => __( 'Tags', 'bbforms' ),
        'parent' => 'bbforms',
        'href'   => admin_url( 'admin.php?page=bbforms_tags' )
    ) );

}
add_action( 'admin_bar_menu', 'bbforms_admin_bar_menu', 100 );

/**
 * Add admin bar menu
 *
 * @since 1.0.0
 */
function bbforms_admin_bar_menu_bottom( $wp_admin_bar ) {

    // Bail if current user can't manage
    if ( ! current_user_can( bbforms_get_manager_capability() ) ) {
        return;
    }

    // Bail if admin bar menu disabled
    if( (bool) bbforms_get_option( 'disable_admin_bar_menu', false ) ) {
        return;
    }

    // Settings
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-settings',
        'title'  => __( 'Settings', 'bbforms' ),
        'parent' => 'bbforms',
        'href'   => admin_url( 'admin.php?page=bbforms_settings' )
    ) );

    // Settings
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-add-ons',
        'title'  => __( 'Add-ons', 'bbforms' ),
        'parent' => 'bbforms',
        'href'   => admin_url( 'admin.php?page=bbforms_add_ons' )
    ) );

}
add_action( 'admin_bar_menu', 'bbforms_admin_bar_menu_bottom', 999 );

/**
 * Add Try admin bar submenu
 *
 * @since 1.0.0
 *
 * @param object $wp_admin_bar The WordPress toolbar object
 */
function bbforms_try_admin_bar_submenu( $wp_admin_bar ) {

    // Bail if current user can't manage
    if ( ! current_user_can( bbforms_get_manager_capability() ) ) {
        return;
    }

    // Bail if admin bar menu disabled
    if( (bool) bbforms_get_option( 'disable_admin_bar_menu', false ) ) {
        return;
    }

    $option = bbforms_get_try_option();

    if( ! $option ) return;

    $badge = '<span class="bbforms-admin-menu-badge">' . __( 'New', 'bbforms' ) . '</span>';

    // Try AutomatorWP
    $wp_admin_bar->add_node( array(
        'id'     => 'bbforms-try',
        'title'  => $option['label'] . $badge,
        'parent' => 'bbforms',
        'href'   => 'https://wordpress.org/plugins/' . $option['slug'] . '/'
    ) );

}
add_action( 'admin_bar_menu', 'bbforms_try_admin_bar_submenu', 999 );


/**
 * Processes all GamiPress actions sent via POST and GET by looking for the 'bbforms-action' request and running do_action() to call the function
 *
 * @since 1.4.8
 */
function bbforms_process_actions() {
    if ( isset( $_POST['bbforms-action'] ) ) {
        do_action( 'bbforms_action_' . sanitize_text_field( wp_unslash( $_POST['bbforms-action'] ) ), $_POST );
    }

    if ( isset( $_GET['bbforms-action'] ) ) {
        do_action( 'bbforms_action_' . sanitize_text_field( wp_unslash( $_GET['bbforms-action'] ) ), $_GET );
    }
}
add_action( 'admin_init', 'bbforms_process_actions' );

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
function bbforms_add_meta_box( $id, $title, $object_types, $fields, $args = array() ) {

    // ID for hooks
    $hook_id = str_replace( '-', '_', $id );

    /**
     * Filter box fields to allow to extend it
     *
     * @since  1.0.8
     *
     * @param array $fields Box fields
     * @param array $args   Box args
     *
     * @return array
     */
    $fields = apply_filters( "bbforms_{$hook_id}_fields", $fields, $args );

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
        'tabs_speed'    => 'fast',
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
    $tabs = apply_filters( "bbforms_{$hook_id}_tabs", $args['tabs'], $fields, $args );

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
        'tabs_speed'    => $args['tabs_speed'],
        'context'      	=> $args['context'],
        'priority'     	=> $args['priority'],
        'show_on_cb'    => $args['show_on_cb'],
        'classes'		=> 'bbforms-form bbforms-box-form',
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
    apply_filters( "bbforms_{$hook_id}_box", $box, $id, $title, $object_types, $fields, $tabs, $args );

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
function bbforms_admin_footer_text( $footer_text ) {

    global $typenow;

    if ( bbforms_is_page( array(
        'bbforms_forms',
        'edit_bbforms_forms',
        'bbforms_submissions',
        'edit_bbforms_submissions',
        'bbforms_categories',
        'edit_bbforms_categories',
        'bbforms_tags',
        'edit_bbforms_tags',
        'bbforms_settings',
        'bbforms_add_ons',
        'bbforms_tools'
    ) ) ) {

        /* translators: %1$s: URL. %2$s: URL. %3$s: stars emoji. */
        $bbforms_footer_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">BBForms</a>! Please leave us a <a href="%2$s" target="_blank">%3$s</a> rating on WordPress.org', 'bbforms' ),
            esc_attr( 'https://bbforms.com' ),
            esc_attr( 'https://wordpress.org/support/plugin/bbforms/reviews/?rate=5#new-post' ),
            esc_html( '&#9733;&#9733;&#9733;&#9733;&#9733;' )
        );

        return str_replace( '</span>', '', $footer_text ) . ' | ' . $bbforms_footer_text . '</span>';

    } else {

        return $footer_text;

    }

}
add_filter( 'admin_footer_text', 'bbforms_admin_footer_text' );