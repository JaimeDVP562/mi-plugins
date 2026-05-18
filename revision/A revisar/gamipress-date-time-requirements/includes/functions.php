<?php
/**
 * Functions
 *
 * @package     GamiPress\Date_Time_Requirements\Functions
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the days of the week
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_date_time_requirements_get_week_days() {

    global $wp_locale;

    $days = array();

    for( $i = 0; $i < 7; $i++ ) {
        $day_index = ( $i + get_option( 'start_of_week' ) ) % 7;
        $days[$day_index] = $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $day_index ) );
    }

    return $days;

}

/**
 * Get the saved days for a requirement
 *
 * @since 1.0.0
 *
 * @param int $requirement_id
 *
 * @return array
 */
function gamipress_date_time_requirements_get_days( $requirement_id ) {

    $days = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_days', true );

    if( empty( $days ) || ! is_array( $days ) ) {
        return array();
    }

    return $days;

}

/**
 * Check if a requirement passes the date and time limits
 *
 * @since 1.0.0
 *
 * @param int $requirement_id
 *
 * @return bool
 */
function gamipress_date_time_requirements_meets_limits( $requirement_id ) {

    $days      = gamipress_date_time_requirements_get_days( $requirement_id );
    $time_from = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', true );
    $time_to   = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to', true );

    if( empty( $days ) && empty( $time_from ) && empty( $time_to ) ) {
        return true;
    }

    $current_day  = absint( date( 'w' ) );
    $current_time = date( 'H:i' );

    if( ! empty( $days ) && ! in_array( $current_day, $days ) ) {
        return false;
    }

    if( ! empty( $time_from ) && $current_time < $time_from ) {
        return false;
    }

    if( ! empty( $time_to ) && $current_time > $time_to ) {
        return false;
    }

    return true;

}
