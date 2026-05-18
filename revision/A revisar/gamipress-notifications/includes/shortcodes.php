<?php
/**
 * Shortcodes
 *
 * @package     GamiPress\Notifications\Shortcodes
 * @since       1.6.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin shortcodes
 *
 * @since 1.6.0
 */
function gamipress_notifications_register_shortcodes() {

    // Notifications shortcode
    gamipress_register_shortcode( 'gamipress_notifications', array(
        'name'              => __( 'Notifications', 'gamipress-notifications' ),
        'description'       => __( 'Display user notifications.', 'gamipress-notifications' ),
        'icon'              => 'dashicons-bell',
        'group'             => 'gamipress',
        'output_callback'   => 'gamipress_notifications_shortcode',
        'fields'            => array(
            'limit' => array(
                'name'              => __( 'Limit', 'gamipress-notifications' ),
                'description'       => __( 'Number of notifications to display. Leave empty to show all.', 'gamipress-notifications' ),
                'type'              => 'text',
                'default'           => '50',
                'attributes'        => array(
                    'type' => 'number',
                    'min' => '1',
                ),
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_notifications_register_shortcodes' );

/**
 * Handle fallback URL for notifications page
 *
 * @since 1.6.0
 */
function gamipress_notifications_handle_fallback_url() {
    if ( isset( $_GET['gamipress_notifications'] ) && is_front_page() ) {
        $user_id = get_current_user_id();

        if ( $user_id === 0 ) {
            wp_redirect( wp_login_url( home_url( '?gamipress_notifications=1' ) ) );
            exit;
        }

        // Display notifications directly
        echo '<div class="gamipress-notifications-page">';
        echo gamipress_notifications_shortcode( array( 'limit' => 50 ) );
        echo '</div>';

        exit;
    }
}
add_action( 'template_redirect', 'gamipress_notifications_handle_fallback_url' );

/**
 * Notifications Shortcode
 *
 * @since 1.6.0
 *
 * @param array $atts Shortcode attributes
 *
 * @return string
 */
function gamipress_notifications_shortcode( $atts = array() ) {
    $atts = shortcode_atts( array( 'limit' => 50 ), $atts, 'gamipress_notifications' );
    $user_id = get_current_user_id();
    if ( $user_id === 0 ) return '';

    global $wpdb;
    $table_notifications = $wpdb->prefix . 'gamipress_notifications';
    $table_meta = $wpdb->prefix . 'gamipress_notifications_meta';

    if ( isset( $_GET['gamipress_notifications_mark_all_read'] ) ) {
        if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'gamipress_notifications_mark_all_read' ) ) {
            $wpdb->update(
                $table_notifications,
                array( 'read' => 1 ),
                array( 'user_id' => $user_id ),
                array( '%d' ),
                array( '%d' )
            );
            wp_safe_redirect( remove_query_arg( array( 'gamipress_notifications_mark_all_read', '_wpnonce' ) ) );
            exit;
        }
    }

    $notifications = $wpdb->get_results( $wpdb->prepare(
        "SELECT n.notification_id, n.user_earning_id, n.timestamp, n.read, 
                m1.meta_value as achievement_title, 
                m2.meta_value as item_type, 
                m3.meta_value as achievement_id 
         FROM $table_notifications n
         LEFT JOIN $table_meta m1 ON n.notification_id = m1.notification_id AND m1.meta_key = 'achievement_title'
         LEFT JOIN $table_meta m2 ON n.notification_id = m2.notification_id AND m2.meta_key = 'item_type'
         LEFT JOIN $table_meta m3 ON n.notification_id = m3.notification_id AND m3.meta_key = 'achievement_id'
         WHERE n.user_id = %d 
         ORDER BY n.timestamp DESC 
         LIMIT %d",
        $user_id,
        absint( $atts['limit'] )
    ) );

    if ( empty( $notifications ) ) {
        return '<div class="gamipress-notifications-list"><p>No tienes notificaciones todavía.</p></div>';
    }

    $unread = array();
    $read = array();
    foreach ( $notifications as $n ) {
        if ( $n->read ) { $read[] = $n; } else { $unread[] = $n; }
    }

    ob_start();
    echo '<div class="gamipress-notifications-list" style="max-width: 600px; margin: 0 auto; color: #000;">';

    if ( ! empty( $unread ) ) {
        $mark_all_url = add_query_arg( array(
            'gamipress_notifications_mark_all_read' => 1,
            '_wpnonce' => wp_create_nonce( 'gamipress_notifications_mark_all_read' ),
        ) );
        echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">';
        echo '<h3 style="margin:0; color: #000;">No leídas</h3>';
        echo '<a href="' . esc_url( $mark_all_url ) . '" style="padding: 5px 10px; background: #000; color: #fff; border-radius: 4px; text-decoration: none; font-size: 12px;">Marcar todas como leídas</a>';
        echo '</div>';
        foreach ( $unread as $notif ) { render_notification_item_html( $notif ); }
        echo '<hr style="border:0; border-top:1px solid #eee; margin: 30px 0;">';
    }

    if ( ! empty( $read ) ) {
        echo '<h3 style="margin-bottom:15px; color: #000;">Leídas</h3>';
        foreach ( $read as $notif ) { render_notification_item_html( $notif ); }
    }

    echo '</div>';
    return ob_get_clean();
}





add_shortcode( 'gamipress_notifications', 'gamipress_notifications_shortcode' );

/**
 * Render notification item HTML
 *
 * @since 1.6.0
 *
 * @param object $notif The notification object
 */
function render_notification_item_html( $notif ) {
    $fecha = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $notif->timestamp ) );
    $titulo = esc_html( $notif->achievement_title );

    // Detectar si es Rango, Logro o Puntos
    $item_type = isset( $notif->item_type ) ? sanitize_text_field( $notif->item_type ) : '';
    $achievement_id = isset( $notif->achievement_id ) ? intval( $notif->achievement_id ) : 0;

    $rank_types = gamipress_get_rank_types_slugs();
    $achievement_types = gamipress_get_achievement_types_slugs();

    
    $es_puntos = ( $item_type === 'points-type' );
    
    if ( $es_puntos ) {
        global $wpdb;
        $table = $wpdb->prefix . 'gamipress_user_earnings';
        $earning = $wpdb->get_row( $wpdb->prepare(
            "SELECT points FROM ${table} WHERE user_earning_id = %d",
            $notif->user_earning_id
        ) );
        
        $points = $earning ? intval( $earning->points ) : 0;
        
        
        $unidad = ( abs( $points ) == 1 ) ? 'punto' : 'puntos';
        
        if ( $points > 0 ) {
            $frase = '¡Felicidades! Has ganado <strong style="color:#000;">' . $points . ' ' . $unidad . '</strong>';
            $color_borde = $notif->read ? '#eeeeee' : '#000000';
        } elseif ( $points < 0 ) {
            $frase = '¡Oh no! Has perdido <strong style="color:#000;">' . abs( $points ) . ' ' . $unidad . '</strong>';
            $color_borde = $notif->read ? '#eeeeee' : '#000000';
        } else {
            $frase = 'Ajuste de puntos: <strong style="color:#000;">' . $titulo . '</strong>';
            $color_borde = $notif->read ? '#eeeeee' : '#000000';
        }
    } else {
       
        $es_rango = false;

        if ( ! empty( $item_type ) ) {
            // Check if item_type is in rank types
            if ( in_array( $item_type, $rank_types, true ) ) {
                $es_rango = true;
            } elseif ( in_array( $item_type, $achievement_types, true ) ) {
                $es_rango = false;
            } else {
                // If item_type is not in either array, check the post object
                if ( ! empty( $achievement_id ) ) {
                    $post_type = get_post_type( $achievement_id );
                    $es_rango = in_array( $post_type, $rank_types, true );
                } else {
                    // Default to false if we can't determine
                    $es_rango = false;
                }
            }
        } elseif ( $achievement_id ) {
            $post_type = get_post_type( $achievement_id );
            if ( in_array( $post_type, $rank_types, true ) ) {
                $es_rango = true;
            }
        }

        if ( $es_rango ) {
            $frase = '¡Increíble! Has ascendido al rango: <strong style="color:#000;">' . $titulo . '</strong>';
            $color_borde = $notif->read ? '#eeeeee' : '#000000';
        } else {
            $frase = '¡Felicidades! Has desbloqueado el logro: <strong style="color:#000;">' . $titulo . '</strong>';
            $color_borde = $notif->read ? '#eeeeee' : '#000000';
        }
    }

    echo '<div style="padding:15px; margin-bottom:10px; background:#fff; border: 1px solid ' . esc_attr( $color_borde ) . ';">';
    echo '<div style="font-size:15px; color:#000;">' . $frase . '</div>';
    echo '<div style="font-size:11px; color:#666; margin-top:8px;">' . $fecha . '</div>';
    echo '</div>';
}

add_shortcode( 'gamipress_notifications', 'gamipress_notifications_shortcode' );

/**
 * Render notification list HTML
 *
 * @since 1.6.0
 *
 * @param array $notifications The notifications to render
 *
 * @return string
 */
function gamipress_notifications_render_list( $notifications ) {
    $html = '';

    foreach( $notifications as $notification ) {

        $classes = array( 'gamipress-notification-item' );

        $read_flag = isset( $notification->read ) ? $notification->read : ( isset( $notification->is_read ) ? $notification->is_read : 0 );

        if ( $read_flag ) {
            $classes[] = 'gamipress-notification-read';
        } else {
            $classes[] = 'gamipress-notification-unread';
        }

        $html .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' ;

        // Content from notification table (legacy push)
        $is_legacy_title = ! empty( $notification->title ) && strpos( $notification->title, 'Congratulations' ) !== false;
        $is_legacy_message = ! empty( $notification->message ) && strpos( $notification->message, 'You unlocked' ) !== false;

        $content_from_db = '';
        if ( ! empty( $notification->title ) && ! $is_legacy_title ) {
            $content_from_db .= '<div class="gamipress-notification-title">' . esc_html( $notification->title ) . '</div>';
        }

        if ( ! empty( $notification->message ) && ! $is_legacy_message ) {
            $content_from_db .= '<div class="gamipress-notification-message">' . esc_html( $notification->message ) . '</div>';
        }

        // Fallback to GamiPress earning content and to force Spanish for legacy English messages
        if ( ( empty( $content_from_db ) || $is_legacy_title || $is_legacy_message ) && ! empty( $notification->user_earning_id ) ) {
            ct_setup_table( 'gamipress_user_earnings' );
            $earning = ct_get_object( $notification->user_earning_id );
            ct_reset_setup_table();

            if ( $earning ) {
                $post = gamipress_get_post( $earning->post_id );

                if ( $post ) {
                    setup_postdata( $post );

                    $content_from_db = apply_filters( 'gamipress_notification_process_notification_content', '', $earning, $post );

                    wp_reset_postdata();
                }
            }
        }

        $html .= $content_from_db;

        // Original DB values are omitted if legacy; otherwise, if there was no generated content yet,
        // keep the default title/message on the old path.
        if ( empty( $content_from_db ) ) {
            if ( ! empty( $notification->title ) ) {
                $html .= '<div class="gamipress-notification-title">' . esc_html( $notification->title ) . '</div>';
            }

            if ( ! empty( $notification->message ) ) {
                $html .= '<div class="gamipress-notification-message">' . esc_html( $notification->message ) . '</div>';
            }
        }

        $timestamp_field = isset( $notification->timestamp ) ? $notification->timestamp : ( isset( $notification->created_at ) ? $notification->created_at : '' );
        $formatted_date = $timestamp_field ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $timestamp_field ) ) ) : '';

        $html .= '<div class="gamipress-notification-date">' . $formatted_date . '</div>';
        $html .= '</div>';

    }

    return $html;
}

/**
 * Debug function to test notifications functionality
 *
 * @since 1.6.0
 */
function gamipress_notifications_debug_info() {
    if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['gamipress_notifications_debug'] ) ) {
        return;
    }

    echo '<h2>GamiPress Notifications Debug Info</h2>';

    $user_id = get_current_user_id();
    echo '<p><strong>Current User ID:</strong> ' . $user_id . '</p>';

    global $wpdb;
    $table_name = $wpdb->prefix . 'gamipress_notifications';

    // Check if table exists
    $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
    echo '<p><strong>Notifications Table Exists:</strong> ' . ( $table_exists ? 'Yes' : 'No' ) . '</p>';

    if ( $table_exists ) {
        // Get table structure
        $columns = $wpdb->get_results( "DESCRIBE {$table_name}" );
        echo '<p><strong>Table Columns:</strong></p><ul>';
        foreach ( $columns as $column ) {
            echo '<li>' . $column->Field . ' (' . $column->Type . ')</li>';
        }
        echo '</ul>';

        // Count notifications
        $total_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d", $user_id ) );
        echo '<p><strong>Total Notifications for Current User:</strong> ' . $total_count . '</p>';

        $unread_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND (read = 0 OR is_read = 0)", $user_id ) );
        echo '<p><strong>Unread Notifications for Current User:</strong> ' . $unread_count . '</p>';
    }

    // Check page
    $page_id = get_option( 'gamipress_notifications_page_id' );
    echo '<p><strong>Notifications Page ID:</strong> ' . ( $page_id ? $page_id : 'Not set' ) . '</p>';

    if ( $page_id ) {
        $page = get_post( $page_id );
        echo '<p><strong>Page Status:</strong> ' . ( $page ? $page->post_status : 'Page not found' ) . '</p>';
        echo '<p><strong>Page URL:</strong> ' . gamipress_notifications_get_page_url() . '</p>';
    }

    echo '<hr>';
}
// Uncomment the line below to enable debug info
add_action( 'wp_footer', 'gamipress_notifications_debug_info' );

/**
 * Get user notifications for display (both read and unread)
 *
 * @since 1.6.0
 *
 * @param int $user_id The user's ID
 * @param int $limit Maximum number of notifications to return
 * @param int $offset Offset for pagination
 * @return array
 */
function gamipress_notifications_get_user_notifications_for_display( $user_id = null, $limit = 20, $offset = 0 ) {

    if ( $user_id === null ) {
        $user_id = get_current_user_id();
    }

    if ( $user_id === 0 ) {
        return array();
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gamipress_notifications';

    $timestamp_column = 'timestamp';
    if ( $wpdb->get_var( "SHOW COLUMNS FROM {$table_name} LIKE 'created_at'" ) ) {
        $timestamp_column = 'created_at';
    }

    $query = $wpdb->prepare(
        "SELECT * FROM {$table_name}
         WHERE user_id = %d
         ORDER BY {$timestamp_column} DESC
         LIMIT %d OFFSET %d",
        $user_id,
        $limit,
        $offset
    );

    $notifications = $wpdb->get_results( $query );

    return $notifications;
}