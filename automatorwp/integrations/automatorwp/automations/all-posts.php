<?php
/**
 * All Posts
 *
 * @package     AutomatorWP\Integrations\WordPress\Automations\All_Posts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_All_Posts_Automation extends AutomatorWP_Automation_Loop {

    public $integration = 'automatorwp';
    public $type = 'all-posts';

    public function register() {

        // Automation type args
        $this->args = array(
            'image' => AUTOMATORWP_URL . 'assets/img/automatorwp-all-posts-logo.svg',
            'label' => __( 'All posts', 'automatorwp' ),
            'desc'  => __( 'Run actions on a filtered group of posts.', 'automatorwp' ),
            // Automation loop specific args
            'labels' => array(
                'singular' => __( 'Post', 'automatorwp' ),
                'plural' => __( 'Posts', 'automatorwp' ),
            ),
            'required_trigger' => 'automatorwp_all_posts',
        );
    }

    public function process_items( $items_ids, $automation, $trigger, $trigger_options ) {

        global $automatorwp_event;

        // Set up a false event since the following functions require it
        $automatorwp_event = array(
            'post_id' => 0,
            'user_id' => 0,
        );

        foreach ( $items_ids as $item_id ) {

            $post_id = absint( $item_id );
            $user_id = absint( get_post_field( 'post_author', $post_id ) );
            $automatorwp_event['post_id'] = $post_id;
            $automatorwp_event['user_id'] = $user_id;

            foreach( $trigger_options as $option => $value ) {
                // Replace all tags by their replacements
                $trigger_options[$option] = automatorwp_parse_automation_tags( $automation->id, $user_id, $value );
            }

            // Check if user deserves the trigger filters
            if( ! automatorwp_user_deserves_trigger_filters( $trigger, $user_id, $automatorwp_event, $trigger_options, $automation ) ) {
                continue;
            }

            // Execute all automation actions
            automatorwp_execute_all_automation_actions( $automation, $user_id, $automatorwp_event );

        }

    }

}

new AutomatorWP_All_Posts_Automation();