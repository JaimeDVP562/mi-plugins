<?php

/**
 * Functions
 *
 * @package     GamiPress\Notifications\Functions
 * @since       1.1.3
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check a specific user notifications
 *
 * @since 1.1.3
 *
 * @param int       $user_id        The given user's ID
 * @param bool      $user_points    If true, will return the current user points balances (used on ajax function)
 *
 * @return array                    Format: array( 'notices' => array(), 'last_check' => 0 )
 */
function gamipress_notifications_get_user_notifications($user_id = null, $user_points = false)
{
    // If user ID not passed set the current logged in user
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    $response = array(
        'notices'    => array(),
        'last_check' => 0
    );

    // Just continue if user ID is set
    if ($user_id === 0) {
        return $response;
    }

    // Get last time has been check for notifications
    $last_check = gamipress_notifications_get_user_last_check($user_id);

    // Get life time configured
    $life = absint(gamipress_notifications_get_option('life', 1));

    if ($life === 0) {
        $life = 1;
    }

    $life_timestamp = strtotime("-{$life} day");

    // If not already checked or last check is more than that configured, then use the time from config
    if ($last_check === 0 || $last_check < $life_timestamp) {
        $since_timestamp = $life_timestamp;
    } else {
        $since_timestamp = $last_check + 1;
    }

    /**
     * Limit the number of notifications
     * This prevents large amount of notifications if the site already has a great number of notifications that hasn't been noticed yet
     *
     * @since 1.2.8
     *
     * @param int $notifications_limit
     *
     * @return int
     */
    $notifications_limit = apply_filters('gamipress_notifications_notifications_limit', 10);

    // Ensure int
    $notifications_limit = absint($notifications_limit);

    global $wpdb;

    $table_name = gamipress_notifications_get_notifications_table_name();
    $timestamp_column = gamipress_notifications_get_timestamp_column();
    $read_column      = gamipress_notifications_get_read_column();

    $notifications = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_name}
         WHERE user_id = %d
         AND {$read_column} = 0
         AND {$timestamp_column} > %s
         AND {$timestamp_column} > %s
         ORDER BY {$timestamp_column} DESC
         LIMIT %d",
        $user_id,
        date('Y-m-d H:i:s', $since_timestamp),
        date('Y-m-d H:i:s', $life_timestamp),
        $notifications_limit
    ));

    if (!empty($notifications)) {
        foreach ($notifications as $notification) {
            if (count($response['notices']) >= $notifications_limit) {
                break;
            }

            ct_setup_table('gamipress_user_earnings');
            $earning = ct_get_object($notification->user_earning_id);
            ct_reset_setup_table();

            if (!$earning) {
                continue;
            }

            $post = gamipress_get_post($earning->post_id);

            if (!$post) {
                continue;
            }

            setup_postdata($post);

            $content = '';

            /**
             * Hook to process the notification content
             *
             * @since 1.0.2
             *
             * @param string $content
             * @param object $earning
             * @param WP_Post $post
             */
            $content = apply_filters('gamipress_notification_process_notification_content', $content, $earning, $post);

            if (!empty($content)) {
                $show_sound = gamipress_notifications_get_option('show_sound', '');
                $show_sound = apply_filters('gamipress_notification_show_notification_sound', $show_sound, $earning, $post);

                if (!empty($show_sound)) {
                    $content .= '<div id="gamipress-notification-show-sound" data-src="' . $show_sound . '"></div>';
                }

                $hide_sound = gamipress_notifications_get_option('hide_sound', '');
                $hide_sound = apply_filters('gamipress_notification_hide_notification_sound', $hide_sound, $earning, $post);

                if (!empty($hide_sound)) {
                    $content .= '<div id="gamipress-notification-hide-sound" data-src="' . $hide_sound . '"></div>';
                }

                $response['notices'][] = $content;
            }

            wp_reset_postdata();

            $timestamp_value = isset($notification->{$timestamp_column}) ? strtotime($notification->{$timestamp_column}) : $last_check;
            if ($timestamp_value > $last_check) {
                $last_check = $timestamp_value;
            }
        }
    }

    // Fallback to legacy user earnings check if table has no available notifications
    if ( empty( $notifications ) ) {
        $earnings = gamipress_get_user_achievements(array(
            'user_id' => $user_id,
            'since'   => $since_timestamp,
            'display' => true,
        ));

        if ( count( $earnings ) ) {
            foreach ( $earnings as $earning ) {
                if ( count( $response['notices'] ) >= $notifications_limit ) {
                    break;
                }

                ct_setup_table('gamipress_user_earnings');
                $earning_data = gamipress_get_post($earning->ID);
                ct_reset_setup_table();

                if ( ! $earning_data ) {
                    continue;
                }

                setup_postdata( $earning_data );

                $content = apply_filters( 'gamipress_notification_process_notification_content', '', $earning, $earning_data );

                if ( ! empty( $content ) ) {
                    $response['notices'][] = $content;
                }

                wp_reset_postdata();

                if ( isset( $earning->date_earned ) && $earning->date_earned > $last_check ) {
                    $last_check = $earning->date_earned;
                }
            }
        }
    }

    // Pass the last time notifications has been checked
    $response['last_check'] = $last_check;

    // Pass the updated information of the current user points if is requested
    if ($user_points) {
        $response['user_points'] = array();

        foreach (gamipress_get_points_types_slugs() as $points_type) {
            $response['user_points'][] = array(
                'points_type' => $points_type,
                'points'      => gamipress_get_user_points($user_id, $points_type)
            );
        }
    }

    /**
     * Filter user notifications
     *
     * @since 1.2.0
     *
     * @param array     $response       Array with information about user notifications
     * @param int       $user_id        The given user's ID
     * @param bool      $user_points    If true, will return the current user points balances (used on ajax function)
     *
     * @return array                    Format: array( 'notices' => array(), 'last_check' => 0, 'user_points' => array() )
     */
    return apply_filters('gamipress_notifications_get_user_notifications', $response, $user_id, $user_points);
}

/**
 * Get previous time user has check for new notifications
 *
 * @since 1.3.3
 *
 * @param int       $user_id    The given user's ID
 *
 * @return int
 */
function gamipress_notifications_get_user_previous_check($user_id = null)
{
    // If user ID not passed set the current logged in user
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    $previous_check = absint(get_user_meta($user_id, '_gamipress_notifications_previous_check', true));

    /**
     * Filter previous time user has check for new notifications
     *
     * @since 1.2.1
     *
     * @param int       $previous_check  The given user's previous check time
     * @param int       $user_id        The given user's ID
     *
     * @return int
     */
    return apply_filters('gamipress_notifications_get_user_previous_check', $previous_check, $user_id);
}

/**
 * Set previous time user has check for new notifications
 *
 * @since 1.3.3
 *
 * @param int       $user_id    The given user's ID
 * @param int       $previous_check Last check timestamp
 */
function gamipress_notifications_set_user_previous_check($user_id = null, $previous_check = 0)
{
    // If user ID not passed set the previous logged in user
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    update_user_meta($user_id, '_gamipress_notifications_previous_check', $previous_check);

    /**
     * Action to meet when previous time user has being set
     *
     * @since 1.2.1
     *
     * @param int       $user_id        The given user's ID
     * @param int       $previous_check Previous check timestamp
     */
    do_action('gamipress_notifications_set_user_previous_check', $user_id, $previous_check);
}

/**
 * Get last time user has check for new notifications
 *
 * @since 1.1.3
 *
 * @param int       $user_id    The given user's ID
 *
 * @return int
 */
function gamipress_notifications_get_user_last_check($user_id = null)
{
    // If user ID not passed set the current logged in user
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    $last_check = absint(get_user_meta($user_id, '_gamipress_notifications_last_check', true));

    /**
     * Filter last time user has check for new notifications
     *
     * @since 1.2.1
     *
     * @param int       $last_check     The given user's last check time
     * @param int       $user_id        The given user's ID
     *
     * @return int
     */
    return apply_filters('gamipress_notifications_get_user_last_check', $last_check, $user_id);
}

/**
 * Get notifications table name
 *
 * @since 1.6.0
 *
 * @return string
 */
function gamipress_notifications_get_notifications_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'gamipress_notifications';
}

/**
 * Get notifications table timestamp column name
 *
 * @since 1.6.0
 *
 * @return string
 */
function gamipress_notifications_get_timestamp_column() {
    global $wpdb;

    static $column = null;

    if ( $column !== null ) {
        return $column;
    }

    $table = gamipress_notifications_get_notifications_table_name();

    if ( $wpdb->get_var( "SHOW COLUMNS FROM {$table} LIKE 'timestamp'" ) ) {
        $column = 'timestamp';
    } elseif ( $wpdb->get_var( "SHOW COLUMNS FROM {$table} LIKE 'created_at'" ) ) {
        $column = 'created_at';
    } else {
        $column = 'timestamp';
    }

    return $column;
}

/**
 * Get notifications table read column name
 *
 * @since 1.6.0
 *
 * @return string
 */
function gamipress_notifications_get_read_column() {
    global $wpdb;

    static $column = null;

    if ( $column !== null ) {
        return $column;
    }

    $table = gamipress_notifications_get_notifications_table_name();

    if ( $wpdb->get_var( "SHOW COLUMNS FROM {$table} LIKE 'read'" ) ) {
        $column = 'read';
    } elseif ( $wpdb->get_var( "SHOW COLUMNS FROM {$table} LIKE 'is_read'" ) ) {
        $column = 'is_read';
    } else {
        $column = 'read';
    }

    return $column;
}

/**
 * Set last time user has check for new notifications
 *
 * @since 1.1.3
 *
 * @param int       $user_id    The given user's ID
 * @param int       $last_check Last check timestamp
 */
function gamipress_notifications_set_user_last_check($user_id = null, $last_check = 0)
{
    // If user ID not passed set the current logged in user
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    update_user_meta($user_id, '_gamipress_notifications_last_check', $last_check);

    /**
     * Action to meet when last time user has being set
     *
     * @since 1.2.1
     *
     * @param int       $user_id    The given user's ID
     * @param int       $last_check Last check timestamp
     */
    do_action('gamipress_notifications_set_user_last_check', $user_id, $last_check);
}

/**
 * Insert a notification into the custom table
 *
 * @since 1.6.0
 *
 * @param int       $user_id            The user's ID
 * @param int       $user_earning_id    The user earning ID
 * @param string    $timestamp          The timestamp (optional, defaults to current time)
 *
 * @return int|bool                     The notification ID on success, false on failure
 */
function gamipress_notifications_insert_notification($user_id, $user_earning_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'gamipress_notifications';

   
    $earning = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gamipress_user_earnings WHERE user_earning_id = %d",
        $user_earning_id
    ));

    $post_type = ($earning) ? $earning->post_type : 'achievement';
    
    $item_type = ($post_type === 'rank' || $post_type === 'rank-requirement') ? 'rank' : 'achievement';

    $timestamp_column = 'timestamp';
    if ($wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'created_at'")) {
        $timestamp_column = 'created_at';
    }

    $read_column = 'read';
    if ($wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'is_read'")) {
        $read_column = 'is_read';
    }

    $data = array(
        'user_id'         => $user_id,
        'user_earning_id' => $user_earning_id,
        $timestamp_column => current_time('mysql'),
        $read_column      => 0,
    );

    $achievement = ($earning && isset($earning->post_id)) ? gamipress_get_post($earning->post_id) : null;
    $achievement_title = '';

    if ($achievement && !empty($achievement->post_title)) {
        $achievement_title = $achievement->post_title;
    } elseif ($earning && !empty($earning->title)) {
        $achievement_title = $earning->title;
    }

    $result = $wpdb->insert($table_name, $data);

    if ($result) {
        $notification_id = $wpdb->insert_id;
        
      
        $tipo_real = 'achievement';
        if ($earning && isset($earning->post_id)) {
            $tipo_real = get_post_type($earning->post_id); 
        }

        ct_setup_table('gamipress_notifications');
        ct_update_object_meta($notification_id, 'achievement_id', $earning ? $earning->post_id : 0);
        ct_update_object_meta($notification_id, 'achievement_title', $achievement_title);
        ct_update_object_meta($notification_id, 'item_type', $tipo_real); 
        ct_reset_setup_table();

        return $notification_id;
    }else {
        return false;
    }
}

/**
 * Get user unread notifications from custom table
 *
 * @since 1.6.0
 *
 * @param int       $user_id        The given user's ID
 * @param int       $last_check     Last check timestamp
 * @param string    $life           Life datetime string
 * @param int       $limit          Maximum number of notifications to return
 *
 * @return array
 */
function gamipress_notifications_get_user_unread_notifications($user_id, $last_check, $life, $limit = 10)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'gamipress_notifications';
    $last_check_datetime = date('Y-m-d H:i:s', $last_check);

    $timestamp_column = 'timestamp';
    if ($wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'created_at'")) {
        $timestamp_column = 'created_at';
    }

    $read_column = 'read';
    if ($wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'is_read'")) {
        $read_column = 'is_read';
    }

    // Query for unread notifications newer than last check and within life period
    $query = $wpdb->prepare(
        "SELECT * FROM {$table_name}
         WHERE user_id = %d
         AND {$read_column} = 0
         AND {$timestamp_column} > %s
         AND {$timestamp_column} > %s
         ORDER BY {$timestamp_column} DESC
         LIMIT %d",
        $user_id,
        $last_check_datetime,
        $life,
        $limit
    );

    $notifications = $wpdb->get_results($query);

    return $notifications;
}

/**
 * Mark notification as read
 *
 * @since 1.6.0
 *
 * @param int $notification_id The notification ID
 * @return bool True on success, false on failure
 */
function gamipress_notifications_mark_as_read($notification_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'gamipress_notifications';

    $read_column = 'read';
    if ($wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'is_read'")) {
        $read_column = 'is_read';
    }

    $result = $wpdb->update(
        $table_name,
        array($read_column => 1),
        array('notification_id' => $notification_id),
        array('%d'),
        array('%d')
    );

    return $result !== false;
}

/**
 * Mark all user notifications as read
 *
 * @since 1.6.0
 *
 * @param int|null $user_id The user's ID
 * @return int|false Number of rows updated or false on failure
 */
function gamipress_notifications_mark_all_as_read($user_id = null)
{
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    if ($user_id === 0) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gamipress_notifications';

    $read_column = 'read';
    if ($wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'is_read'")) {
        $read_column = 'is_read';
    }

    $result = $wpdb->update(
        $table_name,
        array($read_column => 1),
        array('user_id' => $user_id),
        array('%d'),
        array('%d')
    );

    return $result;
}

/**
 * Get count of unread notifications for a user
 *
 * @since 1.6.0
 *
 * @param int $user_id The user's ID
 * @return int
 */
function gamipress_notifications_get_unread_count($user_id = null)
{
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    if ($user_id === 0) {
        return 0;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gamipress_notifications';

    $has_read_column = $wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'read'");
    $has_is_read_column = $wpdb->get_var("SHOW COLUMNS FROM {$table_name} LIKE 'is_read'");

    if ( ! $has_read_column && ! $has_is_read_column ) {
        return 0;
    }

    $conditions = array();

    if ( $has_read_column ) {
        $conditions[] = "`read` = 0";
    }

    if ( $has_is_read_column ) {
        $conditions[] = "`is_read` = 0";
    }

    $condition_sql = implode( ' OR ', $conditions );

    $query = $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name}
         WHERE user_id = %d AND ({$condition_sql})",
        $user_id
    );

    $count = $wpdb->get_var( $query );

    return absint( $count );
}

/**
 * Connect user earning to custom notifications table
 *
 * @since 1.6.0
 *
 * @param int $earning_id The user earning ID
 * @param array $user_earning_data The user earning data
 */
function gamipress_notifications_connect_earning_to_custom_table($earning_id, $user_earning_data)
{
    $user_id = isset($user_earning_data['user_id']) ? $user_earning_data['user_id'] : 0;

    if ($user_id > 0 && $earning_id > 0) {
        gamipress_notifications_insert_notification($user_id, $earning_id);
    }
}

add_action('gamipress_insert_user_earning', 'gamipress_notifications_connect_earning_to_custom_table', 10, 2);