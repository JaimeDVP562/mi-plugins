<?php
/**
 * Functions
 *
 * @package  AutomatorWP\Dailybot\Functions
 * @since    1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Dailybot API parameters (URL and key)
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_dailybot_get_api() {

    $api_key = automatorwp_dailybot_get_option( 'key', '' );

    if ( empty( $api_key ) ) {
        return false;
    }

    return array(
        'url'     => AUTOMATORWP_DAILYBOT_API_BASE,
        'api_key' => $api_key,
    );
}