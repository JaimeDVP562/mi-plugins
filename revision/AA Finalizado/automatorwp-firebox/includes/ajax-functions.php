<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\FireBox\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Ajax function for selecting FireBox popup campaigns (used by form triggers)
 *
 * @since 1.0.0
 */
function automatorwp_firebox_ajax_get_forms()
{
    check_ajax_referer('automatorwp_admin', 'nonce');

    $search = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '';

    $results = array();

    $args = array(
        'post_type'      => 'firebox',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    if (!empty($search)) {
        $args['s'] = $search;
    }

    $posts = get_posts($args);

    foreach ($posts as $post) {
        $results[] = array(
            'id'   => $post->ID,
            'text' => $post->post_title,
        );
    }

    $results = automatorwp_ajax_get_ajax_results_option_none($results);

    wp_send_json_success($results);
    die;
}
add_action('wp_ajax_automatorwp_firebox_get_forms', 'automatorwp_firebox_ajax_get_forms', 5);

/**
 * Ajax handler for the FireBox conversion event.
 *
 * Called from automatorwp-firebox.js when a user clicks a tracked Button or
 * Image block inside a popup. Fires the appropriate AutomatorWP trigger based
 * on whether the visitor is logged in or not.
 *
 * @since 1.0.0
 */
function automatorwp_firebox_ajax_conversion()
{
    check_ajax_referer('automatorwp_firebox', 'nonce');

    $campaign_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;

    if ($campaign_id === 0) {
        wp_send_json_error(array('message' => 'Invalid campaign ID.'));
        return;
    }

    $user_id = get_current_user_id();

    if ($user_id > 0) {
        // Logged-in user conversion
        automatorwp_trigger_event(array(
            'trigger' => 'firebox_conversion',
            'user_id' => $user_id,
            'post_id' => $campaign_id,
        ));
    } else {
        // Guest conversion
        automatorwp_trigger_event(array(
            'trigger' => 'firebox_anonymous_conversion',
            'post_id' => $campaign_id,
        ));
    }

    wp_send_json_success();
    die;
}
add_action('wp_ajax_automatorwp_firebox_conversion',        'automatorwp_firebox_ajax_conversion');
add_action('wp_ajax_nopriv_automatorwp_firebox_conversion', 'automatorwp_firebox_ajax_conversion');
