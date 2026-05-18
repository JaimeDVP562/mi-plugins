<?php
/**
 * Scripts
 *
 * @package     GamiPress\Daily_Login_Rewards\Scripts
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
function gamipress_daily_login_rewards_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style(
        'gamipress-daily-login-rewards-css',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/css/gamipress-daily-login-rewards' . $suffix . '.css',
        array(),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        'all'
    );

    // Scripts
    wp_register_script(
        'gamipress-daily-login-rewards-js',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/js/gamipress-daily-login-rewards' . $suffix . '.js',
        array( 'jquery' ),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        true
    );

}
add_action( 'init', 'gamipress_daily_login_rewards_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_daily_login_rewards_enqueue_scripts( $hook = null ) {

    wp_localize_script( 'gamipress-daily-login-rewards-js', 'gamipress_daily_login_rewards', array(
        'ajaxurl' => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'   => wp_create_nonce( 'gamipress_daily_login_rewards' ),
    ) );

    wp_enqueue_style( 'gamipress-daily-login-rewards-css' );
    wp_enqueue_script( 'gamipress-daily-login-rewards-js' );

}
add_action( 'wp_enqueue_scripts', 'gamipress_daily_login_rewards_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_daily_login_rewards_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Admin stylesheet
    wp_register_style(
        'gamipress-daily-login-rewards-admin-css',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/css/gamipress-daily-login-rewards-admin' . $suffix . '.css',
        array(),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        'all'
    );

    // General admin script
    wp_register_script(
        'gamipress-daily-login-rewards-admin-js',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/js/gamipress-daily-login-rewards-admin' . $suffix . '.js',
        array( 'jquery', 'jquery-ui-sortable' ),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        true
    );

    // Rewards UI (edit.php?post_type=rewards-calendar)
    wp_register_script(
        'gamipress-daily-login-rewards-rewards-ui-js',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/js/gamipress-daily-login-rewards-rewards-ui' . $suffix . '.js',
        array( 'jquery', 'jquery-ui-sortable' ),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        true
    );

    // Widgets area
    wp_register_script(
        'gamipress-daily-login-rewards-admin-widgets-js',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/js/gamipress-daily-login-rewards-admin-widgets' . $suffix . '.js',
        array( 'jquery', 'gamipress-select2-js' ),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        true
    );

    // Shortcode editor (TinyMCE / classic editor)
    wp_register_script(
        'gamipress-daily-login-rewards-shortcode-editor-js',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/js/gamipress-daily-login-rewards-shortcode-editor' . $suffix . '.js',
        array( 'jquery', 'gamipress-select2-js' ),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        true
    );

    // Calendar generator — respects SCRIPT_DEBUG like all other scripts
    wp_register_script(
        'gamipress-daily-login-rewards-calendar-generator-js',
        GAMIPRESS_DAILY_LOGIN_REWARDS_URL . 'assets/js/gamipress-daily-login-rewards-calendar-generator' . $suffix . '.js',
        array( 'jquery', 'gamipress-select2-js' ),
        GAMIPRESS_DAILY_LOGIN_REWARDS_VER,
        true
    );

}
add_action( 'admin_init', 'gamipress_daily_login_rewards_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_daily_login_rewards_admin_enqueue_scripts( $hook ) {

    global $post_type;

    // Admin stylesheet — enqueue on all admin screens
    wp_enqueue_style( 'gamipress-daily-login-rewards-admin-css' );

    // General admin script
    wp_localize_script( 'gamipress-daily-login-rewards-admin-js', 'gamipress_daily_login_rewards_admin', array(
        'nonce'                        => gamipress_get_admin_nonce(),
        'rewards_calendar_placeholder' => __( 'Select Calendar(s)', 'gamipress-daily-login-rewards' ),
    ) );
    wp_enqueue_script( 'gamipress-daily-login-rewards-admin-js' );

    // --- Rewards UI (single rewards-calendar edit screen) ---
    if( $post_type === 'rewards-calendar' ) {

        wp_enqueue_media();

        wp_localize_script( 'gamipress-daily-login-rewards-rewards-ui-js', 'gamipress_daily_login_rewards_rewards_ui', array(
            'nonce'                    => gamipress_get_admin_nonce(),
            'achievement_placeholder'  => __( 'Select an Achievement', 'gamipress-daily-login-rewards' ),
            'rank_placeholder'         => __( 'Select a Rank',         'gamipress-daily-login-rewards' ),
            'media_title'              => __( 'Reward Image',           'gamipress-daily-login-rewards' ),
        ) );
        wp_enqueue_script( 'gamipress-daily-login-rewards-rewards-ui-js' );

    }

    // --- Calendar Generator (rewards-calendar list screen) ---
    if( $hook === 'edit.php' && isset( $post_type ) && $post_type === 'rewards-calendar' ) {

        wp_localize_script( 'gamipress-daily-login-rewards-calendar-generator-js', 'gamipress_calendar_generator', array(
            'nonce'                   => gamipress_get_admin_nonce(),
            'achievement_placeholder' => __( 'Select an Achievement',                              'gamipress-daily-login-rewards' ),
            'rank_placeholder'        => __( 'Select a Rank',                                      'gamipress-daily-login-rewards' ),
            'generate_calendar_text'  => __( 'Generate Calendar',                                  'gamipress-daily-login-rewards' ),
            'random_achievement_text' => __( 'Random Achievement',                                 'gamipress-daily-login-rewards' ),
            'random_rank_text'        => __( 'Random Rank',                                        'gamipress-daily-login-rewards' ),
            'empty_day_text'          => __( 'Empty Day',                                          'gamipress-daily-login-rewards' ),
            'no_days_text'            => __( 'Configure your rewards and click Reorder to see a preview.', 'gamipress-daily-login-rewards' ),
            'no_calendar_text'        => __( 'Please configure at least one reward before generating.',    'gamipress-daily-login-rewards' ),
            'error_text'              => __( 'An error occurred. Please try again.',               'gamipress-daily-login-rewards' ),
        ) );
        wp_enqueue_script( 'gamipress-daily-login-rewards-calendar-generator-js' );

    }

    // --- Widgets area ---
    if( $hook === 'widgets.php' ) {

        wp_localize_script( 'gamipress-daily-login-rewards-admin-widgets-js', 'gamipress_daily_login_rewards_admin_widgets', array(
            'nonce'                        => gamipress_get_admin_nonce(),
            'rewards_calendar_placeholder' => __( 'Select a Calendar', 'gamipress-daily-login-rewards' ),
        ) );
        wp_enqueue_script( 'gamipress-daily-login-rewards-admin-widgets-js' );

    }

    // --- Shortcode editor (classic editor post/page screens) ---
    if( ( $hook === 'post.php' || $hook === 'post-new.php' ) && post_type_supports( $post_type, 'editor' ) ) {

        wp_localize_script( 'gamipress-daily-login-rewards-shortcode-editor-js', 'gamipress_daily_login_rewards_shortcode_editor', array(
            'nonce'                        => gamipress_get_admin_nonce(),
            'rewards_calendar_placeholder' => __( 'Select a Calendar', 'gamipress-daily-login-rewards' ),
        ) );
        wp_enqueue_script( 'gamipress-daily-login-rewards-shortcode-editor-js' );

    }

}
add_action( 'admin_enqueue_scripts', 'gamipress_daily_login_rewards_admin_enqueue_scripts', 100 );