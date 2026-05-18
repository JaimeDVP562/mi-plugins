<?php
/**
 * Requirements
 *
 * @package     GamiPress\Date_Time_Requirements\Requirements
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add date and time fields to the requirement UI
 *
 * @since 1.0.0
 *
 * @param int $requirement_id
 * @param int $post_id
 */
function gamipress_date_time_requirements_requirement_ui_fields( $requirement_id, $post_id ) {

    $days       = gamipress_date_time_requirements_get_days( $requirement_id );
    $time_from  = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', true );
    $time_to    = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to', true );

    $week_days = gamipress_date_time_requirements_get_week_days();

    ?>
    <span class="gamipress-date-time-requirements-wrap">
        <label>
            <input type="checkbox" class="gamipress-date-time-requirements-enable" value="1" <?php checked( ! empty( $days ) || ! empty( $time_from ) || ! empty( $time_to ) ); ?> />
            <?php _e( 'Date & Time Limits', 'gamipress-date-time-requirements' ); ?>
        </label>

        <span class="gamipress-date-time-requirements-fields" style="<?php echo ( empty( $days ) && empty( $time_from ) && empty( $time_to ) ) ? 'display:none;' : ''; ?>">

            <span class="gamipress-date-time-requirements-days-wrap">
                <strong><?php _e( 'Day(s) of the week:', 'gamipress-date-time-requirements' ); ?></strong>
                <?php foreach( $week_days as $day_key => $day_label ) : ?>
                    <label>
                        <input type="checkbox"
                               class="gamipress-date-time-requirements-day"
                               name="gamipress_date_time_requirements_days[]"
                               value="<?php echo esc_attr( $day_key ); ?>"
                            <?php checked( in_array( $day_key, $days ) ); ?> />
                        <?php echo esc_html( $day_label ); ?>
                    </label>
                <?php endforeach; ?>
            </span>

            <span class="gamipress-date-time-requirements-time-wrap">
                <strong><?php _e( 'Time:', 'gamipress-date-time-requirements' ); ?></strong>

                <label>
                    <input type="checkbox"
                           class="gamipress-date-time-requirements-before-enable"
                           value="1"
                        <?php checked( ! empty( $time_to ) ); ?> />
                    <?php _e( 'Before', 'gamipress-date-time-requirements' ); ?>
                </label>
                <input type="time"
                       class="gamipress-date-time-requirements-time-to"
                       value="<?php echo esc_attr( $time_to ); ?>"
                       style="<?php echo empty( $time_to ) ? 'display:none;' : ''; ?>" />

                <label>
                    <input type="checkbox"
                           class="gamipress-date-time-requirements-after-enable"
                           value="1"
                        <?php checked( ! empty( $time_from ) ); ?> />
                    <?php _e( 'After', 'gamipress-date-time-requirements' ); ?>
                </label>
                <input type="time"
                       class="gamipress-date-time-requirements-time-from"
                       value="<?php echo esc_attr( $time_from ); ?>"
                       style="<?php echo empty( $time_from ) ? 'display:none;' : ''; ?>" />

            </span>

        </span>
    </span>
    <?php
}
add_action( 'gamipress_requirement_ui_html_after_achievement_post', 'gamipress_date_time_requirements_requirement_ui_fields', 10, 2 );

/**
 * Save date and time fields when a requirement is updated
 *
 * @since 1.0.0
 *
 * @param int   $requirement_id
 * @param array $requirement
 */
function gamipress_date_time_requirements_update_requirement( $requirement_id, $requirement ) {

    $days      = isset( $requirement['date_time_requirements_days'] ) ? array_map( 'sanitize_text_field', $requirement['date_time_requirements_days'] ) : array();
    $time_from = isset( $requirement['date_time_requirements_time_from'] ) ? sanitize_text_field( $requirement['date_time_requirements_time_from'] ) : '';
    $time_to   = isset( $requirement['date_time_requirements_time_to'] ) ? sanitize_text_field( $requirement['date_time_requirements_time_to'] ) : '';

    update_post_meta( $requirement_id, '_gamipress_date_time_requirements_days', $days );
    update_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', $time_from );
    update_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to', $time_to );

}
add_action( 'gamipress_update_requirement', 'gamipress_date_time_requirements_update_requirement', 10, 2 );
add_action( 'gamipress_ajax_update_requirement', 'gamipress_date_time_requirements_update_requirement', 10, 2 );

/**
 * Add date and time info to the requirement title
 *
 * @since 1.0.0
 *
 * @param string    $title
 * @param int       $requirement_id
 * @param array     $requirement
 *
 * @return string
 */
function gamipress_date_time_requirements_requirement_title( $title, $requirement_id, $requirement ) {

    $days      = gamipress_date_time_requirements_get_days( $requirement_id );
    $time_from = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_from', true );
    $time_to   = get_post_meta( $requirement_id, '_gamipress_date_time_requirements_time_to', true );

    if( empty( $days ) && empty( $time_from ) && empty( $time_to ) ) {
        return $title;
    }

    $date_time_str = '';

    if( ! empty( $days ) ) {

        $week_days  = gamipress_date_time_requirements_get_week_days();
        $day_labels = array();

        foreach( $days as $day_key ) {
            if( isset( $week_days[$day_key] ) ) {
                $day_labels[] = $week_days[$day_key];
            }
        }

        if( count( $day_labels ) === 1 ) {
            $date_time_str .= sprintf( __( 'on %s', 'gamipress-date-time-requirements' ), $day_labels[0] );
        } else {
            $last = array_pop( $day_labels );
            $date_time_str .= sprintf( __( 'on %s and %s', 'gamipress-date-time-requirements' ), implode( ', ', $day_labels ), $last );
        }

    }

    if( ! empty( $time_from ) && ! empty( $time_to ) ) {
        $date_time_str .= ' ' . sprintf( __( 'between %s and %s', 'gamipress-date-time-requirements' ), $time_from, $time_to );
    } elseif( ! empty( $time_from ) ) {
        $date_time_str .= ' ' . sprintf( __( 'after %s', 'gamipress-date-time-requirements' ), $time_from );
    } elseif( ! empty( $time_to ) ) {
        $date_time_str .= ' ' . sprintf( __( 'before %s', 'gamipress-date-time-requirements' ), $time_to );
    }

    if( ! empty( $date_time_str ) ) {
        $title .= ' ' . $date_time_str;
    }

    return $title;

}
add_filter( 'gamipress_requirement_post_title', 'gamipress_date_time_requirements_requirement_title', 10, 3 );