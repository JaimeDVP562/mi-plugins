<?php
/**
 * Functions
 * * @package AutomatorWP\Security_Optimizer\Functions
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Returns the list of users for AutomatorWP selectors.
 */
function automatorwp_sg_security_options_cb_users() {
    $options = array();
    
    $users = get_users();

    foreach ( $users as $user ) {
        $options[$user->ID] = $user->display_name . ' (' . $user->user_email . ')';
    }

    return $options;
}

/**
 * Gets the SG Security visitor_id for a given user_id.
 */
function automatorwp_sg_security_get_visitor_id( $user_id ) {
    global $wpdb;

    if ( empty( $wpdb->sgs_visitors ) ) {
        return false;
    }

    $visitor = $wpdb->get_row( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->sgs_visitors} WHERE user_id = %d LIMIT 1",
        $user_id
    ) );

    return $visitor ? intval( $visitor->ID ) : false;
}

/**
 * Gets or creates the SG Security visitor_id for a given user_id.
 *
 * If the SG Security activity log helper is available, it uses that helper.
 * Otherwise it inserts a visitor record in the sgs_visitors table with the
 * current IP address.
 *
 * @param int $user_id The WordPress user ID.
 * @return int|false The visitor_id or false if it cannot be resolved.
 */
function automatorwp_sg_security_get_or_create_visitor_id( $user_id ) {
    if ( class_exists( '\SG_Security\Activity_Log\Activity_Log_Helper' ) ) {
        $activity_helper = new \SG_Security\Activity_Log\Activity_Log_Helper();
        return $activity_helper->get_visitor_by_user_id( $user_id );
    }

    global $wpdb;

    if ( empty( $wpdb->sgs_visitors ) ) {
        return false;
    }

    $visitor_id = automatorwp_sg_security_get_visitor_id( $user_id );
    if ( $visitor_id ) {
        return $visitor_id;
    }

    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $wpdb->insert(
        $wpdb->sgs_visitors,
        array(
            'user_id' => intval( $user_id ),
            'ip'      => $ip,
        ),
        array( '%d', '%s' )
    );

    return $wpdb->insert_id ? intval( $wpdb->insert_id ) : false;
}

/**
 * Checks whether the given user is blocked by SG Security.
 *
 * @param int $user_id The WordPress user ID.
 * @return bool True if the user is blocked, false otherwise.
 */
function automatorwp_sg_security_user_is_blocked( $user_id ) {
    $visitor_id = automatorwp_sg_security_get_visitor_id( $user_id );
    if ( ! $visitor_id ) {
        return false;
    }

    global $wpdb;
    $blocked = $wpdb->get_var( $wpdb->prepare(
        "SELECT block FROM {$wpdb->sgs_visitors} WHERE ID = %d LIMIT 1",
        $visitor_id
    ) );

    return 1 === intval( $blocked );
}

/**
 * Authenticate callback to prevent blocked users from logging in.
 *
 * @param WP_User|WP_Error|null $user     The user object or error.
 * @param string               $username Username used for authentication.
 * @param string               $password Password used for authentication.
 * @return WP_User|WP_Error The original user or a WP_Error when blocked.
 */
add_filter( 'authenticate', function( $user, $username, $password ) {
    if ( is_wp_error( $user ) || empty( $user ) ) {
        return $user;
    }

    if ( automatorwp_sg_security_user_is_blocked( $user->ID ) ) {
        return new WP_Error( 'user_blocked', __( 'Tu cuenta ha sido bloqueada por motivos de seguridad.', 'automatorwp' ) );
    }

    return $user;
}, 99, 3 );

/**
 * Redirect callback for blocked users and forced password reset flows.
 *
 * This hooks into template_redirect to log out blocked users, enforce password
 * reset requirements, and redirect users to the login page when sessions have
 * been globally destroyed.
 */
add_action( 'template_redirect', function() {
    if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }

    if ( is_user_logged_in() ) {
        $current_user_id = get_current_user_id();
        
        if ( automatorwp_sg_security_user_is_blocked( $current_user_id ) ) {
            wp_logout();
            wp_safe_redirect( wp_login_url() );
            exit;
        }
        
        if ( get_transient( '_sgs_force_password_reset' ) && ! isset( $_COOKIE['automatorwp_sgs_force_password_reset_logged_in'] ) ) {
            if ( user_can( $current_user_id, 'manage_options' ) ) {
                return;
            }
            wp_logout();
            wp_safe_redirect( wp_login_url() );
            exit;
        }
        return;
    }

    $has_login_cookie = defined( 'LOGGED_IN_COOKIE' ) && isset( $_COOKIE[ LOGGED_IN_COOKIE ] );
    $has_auth_cookie = defined( 'AUTH_COOKIE' ) && isset( $_COOKIE[ AUTH_COOKIE ] );

    if ( ! $has_login_cookie && ! $has_auth_cookie ) {
        return;
    }

    if ( ! get_transient( '_sgs_all_sessions_destroyed' ) && ! get_transient( '_sgs_force_password_reset' ) ) {
        return;
    }

    global $pagenow;
    if ( isset( $pagenow ) && 'wp-login.php' === $pagenow ) {
        return;
    }

    if ( get_transient( '_sgs_force_password_reset' ) ) {
        wp_safe_redirect( add_query_arg( 'sgs_force_password_reset', '1', wp_login_url() ) );
    } else {
        wp_safe_redirect( add_query_arg( 'sgs_all_sessions_destroyed', '1', wp_login_url() ) );
    }

    exit;
} );

add_action( 'wp_login', function( $user_login, $user ) {
    if ( ! get_transient( '_sgs_force_password_reset' ) ) {
        return;
    }

    if ( headers_sent() ) {
        return;
    }

    $cookie_path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
    $cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

    setcookie(
        'automatorwp_sgs_force_password_reset_logged_in',
        '1',
        time() + MINUTE_IN_SECONDS * 10,
        $cookie_path,
        $cookie_domain,
        is_ssl(),
        true
    );
}, 10, 2 );

/**
 * Permisos de ejecución para las acciones de Security Optimizer.
 * Solo el administrador o el usuario objetivo pueden ejecutar bloqueo/desbloqueo de usuario.
 */
add_filter( 'automatorwp_can_execute_action', function( $execute, $action, $user_id, $event, $action_options, $automation ) {
    if ( ! is_object( $action ) ) {
        return $execute;
    }

    if ( in_array( $action->type, array( 'sg_security_block_user', 'sg_security_unblock_user' ), true ) ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        if ( ! empty( $action_options['user_id'] ) && intval( $action_options['user_id'] ) === intval( $user_id ) ) {
            return true;
        }

        return false;
    }

    if ( in_array( $action->type, array( 'sg_security_force_logout_all', 'sg_security_force_reset_passwords' ), true ) ) {
        return current_user_can( 'manage_options' );
    }

    return $execute;
}, 10, 6 );

/**
 * Displays a message on the login screen when the administrator forces logout for all users.
 */
add_filter( 'login_message', function( $message ) {
    $notice_message = '';

    if ( isset( $_GET['sgs_all_sessions_destroyed'] ) ) {
        $notice_message = esc_html__( 'El administrador ha cerrado sesión en todas las cuentas.', 'automatorwp' );
    } elseif ( isset( $_GET['sgs_force_password_reset'] ) ) {
        $notice_message = esc_html__( 'Se ha forzado el reinicio de contraseña para todos los usuarios. Por favor, inicie sesión de nuevo.', 'automatorwp' );
    }

    if ( ! $notice_message ) {
        return $message;
    }

    $notice = '<div class="message updated notice is-dismissible">' . $notice_message . '</div>';

    return $message . $notice;
} );