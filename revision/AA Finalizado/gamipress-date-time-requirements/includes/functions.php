<?php
/**
 * Functions
 *
 * @package     GamiPress\Date_Time_Requirements\Functions
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get week days as abbreviated labels (used in UI checkboxes).
 * Order respects the "Week Starts On" WordPress setting.
 *
 * @since 1.0.0
 * @return array [ day_index (0=Sun…6=Sat) => abbrev_label ]
 */
function gamipress_date_time_requirements_get_week_days() {

    global $wp_locale;

    $days = array();

    for ( $i = 0; $i < 7; $i++ ) {
        $day_index        = ( $i + (int) get_option( 'start_of_week', 0 ) ) % 7;
        $days[$day_index] = $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $day_index ) );
    }

    return $days;

}

/**
 * Get week days as full names (used when building the requirement title).
 *
 * @since 1.0.0
 * @return array [ day_index => full_name ]
 */
function gamipress_date_time_requirements_get_week_days_full() {

    global $wp_locale;

    $days = array();

    for ( $i = 0; $i < 7; $i++ ) {
        $day_index        = ( $i + (int) get_option( 'start_of_week', 0 ) ) % 7;
        $days[$day_index] = $wp_locale->get_weekday( $day_index );
    }

    return $days;

}

/**
 * Get saved day indexes for a requirement.
 *
 * @since 1.0.0
 * @param int $requirement_id
 * @return array  Numeric day indexes (0=Sunday…6=Saturday).
 */
function gamipress_date_time_requirements_get_days( $requirement_id ) {

    $days = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_days', true );

    if ( empty( $days ) || ! is_array( $days ) ) {
        return array();
    }

    return array_map( 'intval', $days );

}

/**
 * Check if a requirement passes the configured date and time limits.
 * Uses current_time() so the WordPress timezone setting is respected.
 *
 * @since 1.0.0
 * @param int $requirement_id
 * @return bool
 */
function gamipress_date_time_requirements_meets_limits( $requirement_id ) {

    $days      = gamipress_date_time_requirements_get_days( $requirement_id );
    $time_from = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', true );
    $time_to   = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to', true );

    if ( empty( $days ) && empty( $time_from ) && empty( $time_to ) ) {
        return true;
    }

    $current_day  = (int) current_time( 'w' );
    $current_time = current_time( 'H:i' );

    if ( ! empty( $days ) && ! in_array( $current_day, $days, true ) ) {
        return false;
    }

    if ( ! empty( $time_from ) && $current_time < $time_from ) {
        return false;
    }

    if ( ! empty( $time_to ) && $current_time > $time_to ) {
        return false;
    }

    return true;

}