<?php
/**
 * Requirements
 *
 * @package     GamiPress\Date_Time_Requirements\Requirements
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
if( ! defined( 'ABSPATH' ) ) exit;

// ------------------------------------------------------------------
// UI
// ------------------------------------------------------------------

/**
 * Render the Date & Time Limits controls inside each requirement row.
 *
 * @since 1.0.0
 * @param int $requirement_id
 * @param int $post_id
 */
function gamipress_date_time_requirements_requirement_ui_fields( $requirement_id, $post_id ) {

    $days      = gamipress_date_time_requirements_get_days( $requirement_id );
    $time_from = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', true );
    $time_to   = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to', true );
    $is_enabled = ( ! empty( $days ) || ! empty( $time_from ) || ! empty( $time_to ) );
    $week_days  = gamipress_date_time_requirements_get_week_days();

    ?>
    <span class="gamipress-date-time-requirements-wrap" style="display:block; margin-top:6px;">

        <label style="font-weight:600;">
            <input type="checkbox"
                   class="gamipress-date-time-requirements-enable"
                   value="1"
                   <?php checked( $is_enabled ); ?> />
            <?php _e( 'Date &amp; Time Limits', 'gamipress-date-time-requirements' ); ?>
        </label>

        <span class="gamipress-date-time-requirements-fields"
              style="display:<?php echo $is_enabled ? 'block' : 'none'; ?>; margin-top:4px; padding-left:20px;">

            <span class="gamipress-date-time-requirements-days-wrap" style="display:block; margin-bottom:4px;">
                <strong><?php _e( 'Day(s) of the week:', 'gamipress-date-time-requirements' ); ?></strong>
                <?php foreach( $week_days as $day_key => $day_label ) : ?>
                    <label style="margin-left:6px;">
                        <input type="checkbox"
                               class="gamipress-date-time-requirements-day"
                               value="<?php echo esc_attr( $day_key ); ?>"
                               <?php checked( in_array( $day_key, $days, false ) ); ?> />
                        <?php echo esc_html( $day_label ); ?>
                    </label>
                <?php endforeach; ?>
            </span>

            <span class="gamipress-date-time-requirements-time-wrap" style="display:block;">
                <strong><?php _e( 'Time:', 'gamipress-date-time-requirements' ); ?></strong>

                <label style="margin-left:8px;">
                    <input type="checkbox"
                           class="gamipress-date-time-requirements-before-enable"
                           value="1"
                           <?php checked( ! empty( $time_to ) ); ?> />
                    <?php _e( 'Before', 'gamipress-date-time-requirements' ); ?>
                </label>
                <input type="time"
                       class="gamipress-date-time-requirements-time-to"
                       value="<?php echo esc_attr( $time_to ); ?>"
                       style="display:<?php echo ! empty( $time_to ) ? 'inline-block' : 'none'; ?>;" />

                <label style="margin-left:8px;">
                    <input type="checkbox"
                           class="gamipress-date-time-requirements-after-enable"
                           value="1"
                           <?php checked( ! empty( $time_from ) ); ?> />
                    <?php _e( 'After', 'gamipress-date-time-requirements' ); ?>
                </label>
                <input type="time"
                       class="gamipress-date-time-requirements-time-from"
                       value="<?php echo esc_attr( $time_from ); ?>"
                       style="display:<?php echo ! empty( $time_from ) ? 'inline-block' : 'none'; ?>;" />

            </span>

        </span>

    </span>
    <?php

}
add_action( 'gamipress_requirement_ui_html_after_achievement_post', 'gamipress_date_time_requirements_requirement_ui_fields', 10, 2 );

// ------------------------------------------------------------------
// Save
// ------------------------------------------------------------------

/**
 * Persist the date & time limit fields when a requirement is saved.
 *
 * @since 1.0.0
 * @param int   $requirement_id
 * @param array $requirement
 */
function gamipress_date_time_requirements_update_requirement( $requirement_id, $requirement ) {

    $days = array();
    if ( isset( $requirement['date_time_requirements_days'] ) && is_array( $requirement['date_time_requirements_days'] ) ) {
        foreach ( $requirement['date_time_requirements_days'] as $d ) {
            $days[] = absint( $d );
        }
    }

    $time_from = '';
    if ( ! empty( $requirement['date_time_requirements_time_from'] ) ) {
        $raw = sanitize_text_field( $requirement['date_time_requirements_time_from'] );
        if ( preg_match( '/^\d{2}:\d{2}$/', $raw ) ) {
            $time_from = $raw;
        }
    }

    $time_to = '';
    if ( ! empty( $requirement['date_time_requirements_time_to'] ) ) {
        $raw = sanitize_text_field( $requirement['date_time_requirements_time_to'] );
        if ( preg_match( '/^\d{2}:\d{2}$/', $raw ) ) {
            $time_to = $raw;
        }
    }

    update_post_meta( $requirement_id, '_gamipress_date_time_requirements_days',      $days );
    update_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', $time_from );
    update_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to',   $time_to );

}
add_action( 'gamipress_update_requirement',      'gamipress_date_time_requirements_update_requirement', 10, 2 );
add_action( 'gamipress_ajax_update_requirement', 'gamipress_date_time_requirements_update_requirement', 10, 2 );

// ------------------------------------------------------------------
// Title
// ------------------------------------------------------------------

/**
 * Append the date & time limit summary to the auto-generated requirement title.
 *
 * Patterns:
 *   on Monday
 *   on Monday, Tuesday and Wednesday
 *   on Monday before 12:00
 *   on Monday after 08:00
 *   on Monday between 08:00 and 12:00
 *   before 12:00  /  after 08:00  /  between 08:00 and 12:00  (no days)
 *
 * @since 1.0.0
 * @param string $title
 * @param int    $requirement_id
 * @param array  $requirement
 * @return string
 */
function gamipress_date_time_requirements_requirement_title( $title, $requirement_id, $requirement ) {

    $days      = gamipress_date_time_requirements_get_days( $requirement_id );
    $time_from = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', true );
    $time_to   = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to', true );

    if ( empty( $days ) && empty( $time_from ) && empty( $time_to ) ) {
        return $title;
    }

    $suffix = '';

    // Days fragment.
    if ( ! empty( $days ) ) {

        $full_names = gamipress_date_time_requirements_get_week_days_full();
        $day_labels = array();

        sort( $days );

        foreach ( $days as $day_key ) {
            if ( isset( $full_names[$day_key] ) ) {
                $day_labels[] = $full_names[$day_key];
            }
        }

        if ( count( $day_labels ) === 1 ) {
            /* translators: %s = day name */
            $suffix .= sprintf( __( 'on %s', 'gamipress-date-time-requirements' ), $day_labels[0] );
        } else {
            $last = array_pop( $day_labels );
            /* translators: %1$s = comma-separated days, %2$s = last day */
            $suffix .= sprintf( __( 'on %1$s and %2$s', 'gamipress-date-time-requirements' ), implode( ', ', $day_labels ), $last );
        }

    }

    // Time fragment.
    $time_suffix = '';

    if ( ! empty( $time_from ) && ! empty( $time_to ) ) {
        /* translators: %1$s = start time, %2$s = end time */
        $time_suffix = sprintf( __( 'between %1$s and %2$s', 'gamipress-date-time-requirements' ), $time_from, $time_to );
    } elseif ( ! empty( $time_from ) ) {
        /* translators: %s = time */
        $time_suffix = sprintf( __( 'after %s', 'gamipress-date-time-requirements' ), $time_from );
    } elseif ( ! empty( $time_to ) ) {
        /* translators: %s = time */
        $time_suffix = sprintf( __( 'before %s', 'gamipress-date-time-requirements' ), $time_to );
    }

    if ( ! empty( $suffix ) && ! empty( $time_suffix ) ) {
        $suffix .= ' ' . $time_suffix;
    } elseif ( ! empty( $time_suffix ) ) {
        $suffix = $time_suffix;
    }

    if ( ! empty( $suffix ) ) {
        $title .= ' ' . $suffix;
    }

    return $title;

}
add_filter( 'gamipress_requirement_post_title', 'gamipress_date_time_requirements_requirement_title', 10, 3 );

// ------------------------------------------------------------------
// Rules engine
// ------------------------------------------------------------------

/**
 * Block a requirement from being met when outside the configured date/time window.
 *
 * @since 1.0.0
 * @param bool    $return
 * @param int     $user_id
 * @param WP_Post $requirement
 * @return bool
 */
function gamipress_date_time_requirements_user_meets_requirement( $return, $user_id, $requirement ) {

    if ( ! $return ) {
        return $return;
    }

    if ( ! gamipress_date_time_requirements_meets_limits( $requirement->ID ) ) {
        return false;
    }

    return $return;

}
add_filter( 'gamipress_user_meets_requirement', 'gamipress_date_time_requirements_user_meets_requirement', 10, 3 );