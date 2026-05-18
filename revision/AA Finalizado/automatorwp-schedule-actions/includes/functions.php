<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Schedule_Actions\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the singular label of a period
 *
 * @since 1.0.0
 *
 * @param string $period
 *
 * @return string
 */
function automatorwp_schedule_actions_get_period_singular( $period ) {

    $singular_labels = array(
        'seconds'   => __( 'Second', 'automatorwp-schedule-actions' ),
        'minutes'   => __( 'Minute', 'automatorwp-schedule-actions' ),
        'hours'     => __( 'Hour', 'automatorwp-schedule-actions' ),
        'days'      => __( 'Day', 'automatorwp-schedule-actions' ),
        'weeks'     => __( 'Week', 'automatorwp-schedule-actions' ),
        'months'    => __( 'Month', 'automatorwp-schedule-actions' ),
        'years'     => __( 'Year', 'automatorwp-schedule-actions' ),
    );

    return isset( $singular_labels[$period] ) ? $singular_labels[$period] : $period;

}

/**
 * Get the scheduled datetime from a specific amount and period
 *
 * @since 1.0.0
 *
 * @param int $amount
 * @param string $period
 *
 * @return int
 */
function automatorwp_schedule_actions_get_scheduled_datetime( $amount, $period, $gmt = false ) {

    $amount = absint( $amount );
    $now    = current_time( 'timestamp', $gmt );
    $to     = strtotime( "+{$amount}{$period}", $now );

    return $to;
}

/**
 * Helper function to get the date format
 *
 * @since 1.0.0
 *
 * @param array $allowed_formats
 *
 * @return string
 */
function automatorwp_schedule_actions_get_date_format( $allowed_formats = array() ) {

    $format = 'Y-m-d';

    $option = get_option( 'date_format' );

    if( empty( $allowed_formats ) ) {
        $allowed_formats = array( 'Y-m-d', 'm/d/Y', 'd/m/Y' );
    }

    if( in_array( $option, $allowed_formats ) ) {
        $format = $option;
    }

    return $format;

}

/**
 * Helper function to get the time format
 *
 * @since 1.0.0
 *
 * @param array $allowed_formats
 *
 * @return string
 */
function automatorwp_schedule_actions_get_time_format( $allowed_formats = array() ) {

    $format = 'H:i';

    $option = get_option( 'time_format' );

    if( empty( $allowed_formats ) ) {
        $allowed_formats = array( 'g:i a', 'g:i A', 'H:i' );
    }

    if( in_array( $option, $allowed_formats ) ) {
        $format = $option;
    }

    return $format;

}

/**
 * Helper function to get the time format
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_schedule_actions_get_datetime_format() {

    return automatorwp_schedule_actions_get_date_format() . ' ' . automatorwp_schedule_actions_get_time_format();

}