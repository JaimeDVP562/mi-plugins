<?php
/**
 * Date
 *
 * @package     ShortLinksPro\Date
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Gets registered time periods
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_get_time_periods() {

    /**
     * Filter registered time periods
     *
     * @since 1.0.0
     *
     * @param array $time_periods
     *
     * @return array
     */
    return apply_filters( 'shortlinkspro_get_time_periods', array(
        'this-week'     => __( 'Current Week', 'shortlinkspro' ),
        'past-week'     => __( 'Past Week', 'shortlinkspro' ),
        'this-month'    => __( 'Current Month', 'shortlinkspro' ),
        'past-month'    => __( 'Past Month', 'shortlinkspro' ),
        'this-year'     => __( 'Current Year', 'shortlinkspro' ),
        'past-year'     => __( 'Past Year', 'shortlinkspro' ),
        'custom'        => __( 'Custom', 'shortlinkspro' ),
    ) );

}

/**
 * Get a specific period date range
 *
 * @see shortlinkspro_get_time_periods()
 *
 * @since 1.0.0
 *
 * @param string $period
 *
 * @return array
 */
function shortlinkspro_get_period_range( $period = '' ) {

    // Setup date range var
    $date_range = array(
        'start' => '',
        'end'   => '',
    );

    if( $period !== '' ) {

        switch( $period ) {
            case 'today':
                $date_range = array(
                    'start' => gmdate( 'Y-m-d 00:00:00', current_time( 'timestamp' ) ),
                    'end' => '',
                );
                break;
            case 'yesterday':
                $date_range = array(
                    'start' => gmdate( 'Y-m-d 00:00:00', strtotime( '-1 day', current_time( 'timestamp' ) ) ),
                    'end' => gmdate( 'Y-m-d 23:59:59', strtotime( '-1 day', current_time( 'timestamp' ) ) ),
                );
                break;
            case 'current-week':
            case 'this-week':
                $date_range = shortlinkspro_get_date_range( 'week' );
                break;
            case 'past-week':
                $previous_week = strtotime( '-1 week', current_time( 'timestamp' ) );
                $date_range = shortlinkspro_get_date_range( 'week', $previous_week );
                break;
            case 'current-month':
            case 'this-month':
                $date_range = shortlinkspro_get_date_range( 'month' );
                break;
            case 'past-month':
                $previous_month = strtotime( '-1 month', current_time( 'timestamp' ) );
                $date_range = shortlinkspro_get_date_range( 'month', $previous_month );
                break;
            case 'current-year':
            case 'this-year':
                $date_range = shortlinkspro_get_date_range( 'year' );
                break;
            case 'past-year':
                $previous_year = strtotime( '-1 year', current_time( 'timestamp' ) );
                $date_range = shortlinkspro_get_date_range( 'year', $previous_year );
                break;
            default:
                // For custom ranges use 'shortlinkspro_get_period_range' filter
                break;
        }
    }

    /**
     * Filter the period date range
     *
     * @since 1.6.9
     *
     * @param array     $date_range An array with period date range
     * @param string    $period     Given period, see shortlinkspro_get_time_periods()
     */
    return $date_range = apply_filters( 'shortlinkspro_get_period_range', $date_range, $period );
}

/**
 * Helper function to get a range date based on a given date
 *
 * @since 1.0.0
 *
 * @param string            $range (week|month|year)
 * @param integer|string    $date
 *
 * @return array
 */
function shortlinkspro_get_date_range( $range = '', $date = 0 ) {

    if( gettype( $date ) === 'string' ) {
        $date = strtotime( $date );
    }

    if( ! $date ) {
        $date = current_time( 'timestamp' );
    }

    $start_date = 0;
    $end_date = 0;

    switch( $range ) {
        case 'week':

            // Weekly range
            $start_date    = strtotime( 'monday this week', $date );
            $end_date      = strtotime( 'midnight', strtotime( 'sunday this week', $date ) );

            break;
        case 'month':

            // Monthly range
            $start_date    = strtotime( gmdate( 'Y-m-01', $date ) );
            $end_date      = strtotime( 'midnight', strtotime( 'last day of this month', $date ) );

            break;
        case 'year':

            // Yearly range
            $start_date    = strtotime( gmdate( 'Y-01-01', $date ) );
            $end_date      = strtotime( gmdate( 'Y-12-31', $date ) );

            break;
    }

    return array(
        'start'    => gmdate( 'Y-m-d 00:00:00', $start_date ),
        'end'      => gmdate( 'Y-m-d 23:59:59', $end_date )
    );

}

/**
 * Helper function to get a date range period
 *
 * @since 1.0.0
 *
 * @param array     $date_range
 * @param string    $interval   (d|m)
 * @param string    $format     Date format
 *
 * @return array                    Array with the full dates range in Y-m-d format
 */
function shortlinkspro_get_range_period( $date_range, $interval = 'd', $format = 'Y-m-d' ) {

    try{
        $period_obj = new DatePeriod(
            new DateTime( $date_range['start'] ),
            new DateInterval( ( $interval === 'd' ? 'P1D' : 'P1M' ) ),
            new DateTime( $date_range['end'] )
        );
    } catch(Exception $e) {
        // If there is any exception return the date range
        return $date_range;
    }

    $period = array();

    foreach ($period_obj as $key => $value) {
        $period[] = $value->format($format);
    }

    //$period[] = $date_range['end'];

    return $period;

}