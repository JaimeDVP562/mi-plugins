<?php
/**
 * Scripts
 *
 * @package     GamiPress\Notifications\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_notifications_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Libraries
    wp_register_script( 'gamipress-notifications-notify-js', GAMIPRESS_NOTIFICATIONS_URL . 'assets/libs/notify/notify' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_NOTIFICATIONS_VER, true );

    // Stylesheets
    wp_register_style( 'gamipress-notifications-css', GAMIPRESS_NOTIFICATIONS_URL . 'assets/css/gamipress-notifications' . $suffix . '.css', array( ), GAMIPRESS_NOTIFICATIONS_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-notifications-js', GAMIPRESS_NOTIFICATIONS_URL . 'assets/js/gamipress-notifications' . $suffix . '.js', array( 'jquery', 'gamipress-notifications-notify-js' ), GAMIPRESS_NOTIFICATIONS_VER, true );

}
add_action( 'init', 'gamipress_notifications_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_notifications_enqueue_scripts( $hook = null ) {

    $current_user = get_current_user_id();

    // Notifications for guests are not supported
    if( $current_user === 0 ) {
        return;
    }

    $excluded_urls = array();

    /**
     * Filter to let plugins exclude URLs from the notifications check
     *
     * @since 1.4.0
     *
     * @param array $excluded_urls
     *
     * @return array
     */
    $excluded_urls = apply_filters( 'gamipress_notifications_excluded_urls', $excluded_urls );

    $excluded_data = array();

    /**
     * Filter to let plugins exclude request data from the notifications check
     *
     * @since 1.4.0
     *
     * @param array $excluded_data
     *
     * @return array
     */
    $excluded_data = apply_filters( 'gamipress_notifications_excluded_data', $excluded_data );

    $excluded_ajax_actions = array(
        // Congratulations Popups support
        'gamipress_congratulations_popups_get_popups',
        'gamipress_congratulations_popups_popup_shown',
        // Notifications support
        'gamipress_notifications_get_notices',
        // AutomatorWP compatibility
        'automatorwp_check_for_redirect',
        // WooCommerce support
        'woocommerce_load_products',
        // BuddyBoss support
        'buddyboss_load_user_profile',
        'buddyboss_theme_get_header_unread_messages',
        'buddyboss_theme_get_header_notifications',
        // WordPress support
        'heartbeat',
    );

    /**
     * Filter to let plugins exclude ajax actions from the notifications check
     *
     * @since 1.4.0
     *
     * @param array $excluded_ajax_actions
     *
     * @return array
     */
    $excluded_ajax_actions = apply_filters( 'gamipress_notifications_excluded_ajax_actions', $excluded_ajax_actions );

    // Localize scripts
    wp_localize_script( 'gamipress-notifications-js', 'gamipress_notifications', array(
        'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'                 => wp_create_nonce( 'gamipress_notifications' ),
        'position'              => gamipress_notifications_get_option( 'position', 'bottom right' ),
        'disable_live_checks'   => (bool) gamipress_notifications_get_option( 'disable_live_checks', false ),
        'last_check_delay'      => apply_filters( 'gamipress_notifications_last_check_delay', 5000 ),
        'ajax_check_delay'      => apply_filters( 'gamipress_notifications_ajax_check_delay', 200 ),
        'click_to_hide'         => (bool) gamipress_notifications_get_option( 'click_to_hide', false ),
        'auto_hide'             => (bool) gamipress_notifications_get_option( 'auto_hide', false ),
        'auto_hide_delay'       => gamipress_notifications_get_option( 'auto_hide_delay', 5000 ),
        'mark_as_read_delay'    => absint( gamipress_notifications_get_option( 'mark_as_read_delay', 0 ) ),
        'excluded_urls'         => $excluded_urls,
        'excluded_data'         => $excluded_data,
        'excluded_ajax_actions' => $excluded_ajax_actions,
    ) );

    // Enqueue assets
    wp_enqueue_style( 'gamipress-notifications-css' );
    wp_enqueue_script( 'gamipress-notifications-js' );

    // Setup dynamic CSS rules
    $css = '';

    $width              = gamipress_notifications_get_option( 'width', '' );
    $background_color   = gamipress_notifications_get_option( 'background_color', '' );
    $title_color        = gamipress_notifications_get_option( 'title_color', '' );
    $title_font_size    = absint( gamipress_notifications_get_option( 'title_font_size', '' ) );
    $text_color         = gamipress_notifications_get_option( 'text_color', '' );
    $text_font_size     = absint( gamipress_notifications_get_option( 'text_font_size', '' ) );
    $link_color         = gamipress_notifications_get_option( 'link_color', '' );
    $border_width       = absint( gamipress_notifications_get_option( 'border_width', '' ) );
    $border_color       = gamipress_notifications_get_option( 'border_color', '' );
    $border_radius      = absint( gamipress_notifications_get_option( 'border_radius', '' ) );

    if( ! empty( $width ) )
        $css .= ".gamipress-notification { width: {$width}px; }";

    if( ! empty( $background_color ) )
        $css .= ".gamipress-notification { background-color: {$background_color}; }";

    if( ! empty( $title_color ) )
        $css .= ".gamipress-notification .gamipress-notification-title { color: {$title_color}; }";

    if( $title_font_size > 0 )
        $css .= ".gamipress-notification .gamipress-notification-title { font-size: {$title_font_size}px; }";

    if( ! empty( $text_color ) )
        $css .= ".gamipress-notification { color: {$text_color}; }";

    if( $text_font_size > 0 )
        $css .= ".gamipress-notification .gamipress-notification-description { font-size: {$text_font_size}px; }";

    if( ! empty( $link_color ) )
        $css .= ".gamipress-notification a { color: {$link_color}; }";

    if( $border_width > 0 )
        $css .= ".gamipress-notification { border: {$border_width}px solid {$border_color}; }";

    if( $border_radius > 0 )
        $css .= ".gamipress-notification { border-radius: {$border_radius}px; }";

    /**
     * Filters notifications dynamic CSS generated from settings
     *
     * @since 1.0.4
     *
     * @param string $css
     *
     * @return string
     */
    $css = apply_filters( 'gamipress_notifications_dynamic_css', $css );

    if( ! empty( $css ) )
        wp_add_inline_style( 'gamipress-notifications-css', esc_html( $css ) );

}
add_action( 'wp_enqueue_scripts', 'gamipress_notifications_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_notifications_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-notifications-admin-css', GAMIPRESS_NOTIFICATIONS_URL . 'assets/css/gamipress-notifications-admin' . $suffix . '.css', array( ), GAMIPRESS_NOTIFICATIONS_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-notifications-admin-js', GAMIPRESS_NOTIFICATIONS_URL . 'assets/js/gamipress-notifications-admin' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_NOTIFICATIONS_VER, true );

}
add_action( 'admin_init', 'gamipress_notifications_admin_register_scripts' );
add_action( 'wp_enqueue_scripts', 'gamipress_notifications_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_notifications_admin_enqueue_scripts( $hook ) {

    //Stylesheets
    wp_enqueue_style( 'gamipress-notifications-admin-css' );

    //Scripts
    wp_enqueue_script( 'gamipress-notifications-admin-js' );

    wp_localize_script( 'gamipress-notifications-admin-js', 'gamipress_notifications_admin', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'gamipress_notifications' ),
        'text_no_notifications' => __( 'No notifications found.', 'gamipress-notifications' ),
        'text_marked_all_read' => __( 'All notifications marked as read.', 'gamipress-notifications' ),
        'text_error' => __( 'An error occurred.', 'gamipress-notifications' ),
    ) );

}
add_action( 'admin_enqueue_scripts', 'gamipress_notifications_admin_enqueue_scripts', 100 );

/**
 * Enqueue frontend scripts
 *
 * @since       1.6.0
 * @return      void
 */
function gamipress_notifications_frontend_enqueue_scripts() {

    //Stylesheets
    wp_enqueue_style( 'gamipress-notifications-admin-css' );

    //Scripts
    wp_enqueue_script( 'gamipress-notifications-admin-js' );

    wp_localize_script( 'gamipress-notifications-admin-js', 'gamipress_notifications_admin', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'gamipress_notifications' ),
        'text_no_notifications' => __( 'No notifications found.', 'gamipress-notifications' ),
        'text_marked_all_read' => __( 'All notifications marked as read.', 'gamipress-notifications' ),
        'text_error' => __( 'An error occurred.', 'gamipress-notifications' ),
    ) );

}
add_action( 'wp_enqueue_scripts', 'gamipress_notifications_frontend_enqueue_scripts', 1 );