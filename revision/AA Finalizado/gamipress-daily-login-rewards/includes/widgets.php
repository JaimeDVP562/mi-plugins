<?php
/**
 * Widgets
 *
 * @package     GamiPress\Daily_Login_Rewards\Widgets
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR .'includes/widgets/rewards-calendar-widget.php';
require_once GAMIPRESS_DAILY_LOGIN_REWARDS_DIR .'includes/widgets/rewards-calendar-current-day-widget.php';

// Register plugin widgets
function gamipress_daily_login_rewards_register_widgets() {

    register_widget( 'gamipress_rewards_calendar_widget' );
    register_widget( 'gamipress_rewards_calendar_current_day_widget' );

}
add_action( 'widgets_init', 'gamipress_daily_login_rewards_register_widgets' );