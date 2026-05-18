<?php
/**
 * Scripts
 *
 * @package     GamiPress\Date_Time_Requirements\Scripts
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register and enqueue admin scripts
 *
 * @since 1.0.0
 */
function gamipress_date_time_requirements_admin_scripts( $hook ) {

    if( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    wp_enqueue_script(
        'gamipress-date-time-requirements',
        GAMIPRESS_DATE_TIME_REQUIREMENTS_URL . 'assets/js/gamipress-date-time-requirements.js',
        array( 'jquery' ),
        GAMIPRESS_DATE_TIME_REQUIREMENTS_VER,
        true
    );

}
add_action( 'admin_enqueue_scripts', 'gamipress_date_time_requirements_admin_scripts' );
