<?php
/**
 * Tags
 *  
 * @since    1.0.0
 * @package  AutomatorWP\Dailybot\Tags
 * @author   AutomatorWP
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function automatorwp_dailybot_get_webhook_tags() {

    return array(
        'webhook_url' => array(
            'label'     => __( 'Webhook URL', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
            'type'      => 'text',
            'preview'   => 'Webhook URL'
        ),
        'event_type' => array(
            'label'     => __( 'Event type', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN),
            'type'      => 'text',
            'preview'   => 'The event type performed'
        )
    );
}