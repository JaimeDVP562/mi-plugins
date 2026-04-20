<?php
/**
 * All Users
 *
 * @package     AutomatorWP\Integrations\WordPress\Automations\All_Users
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_All_Users_Automation extends AutomatorWP_Automation_Loop {

    public $integration = 'automatorwp';
    public $type = 'all-users';

    public function register() {

        // Automation type args
        $this->args = array(
            'image' => AUTOMATORWP_URL . 'assets/img/automatorwp-all-users-logo.svg',
            'label' => __( 'All users', 'automatorwp' ),
            'desc'  => __( 'Run actions on a filtered group of users.', 'automatorwp' ),
            // Automation loop specific args
            'labels' => array(
                'singular' => __( 'User', 'automatorwp' ),
                'plural' => __( 'Users', 'automatorwp' ),
            ),
            'required_trigger' => 'automatorwp_all_users',
        );
    }

    public function process_items( $items_ids, $automation, $trigger, $trigger_options ) {

        global $automatorwp_event;

        // Set up a false event since the following functions require it
        $automatorwp_event = array(
            'user_id' => 0
        );

        foreach ( $items_ids as $items_id ) {

            $user_id = absint( $items_id );
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

new AutomatorWP_All_Users_Automation();