<?php
/**
 * Join Space
 *
 * @package     AutomatorWP\Integrations\FluentCommunity\Triggers\Join_Space
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCommunity_Join_Space extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentcommunity';
    public $trigger = 'fluentcommunity_join_space';
    
    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {
    
        automatorwp_register_trigger($this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User joins a space', 'automatorwp' ),
            'select_option'     => __( 'User joins a <strong>space</strong>', 'automatorwp' ),
            /* translators: %1$s: Space title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User joins %1$s %2$s time(s)', 'automatorwp' ), '{space}','{times}' ),
            /* translators: %1$s: Space title. */
            'log_label'         => sprintf( __( 'User joins %1$s', 'automatorwp' ), '{space}','{times}' ),
            'action'            => 'fluent_community/space/joined',
            'function'          => array($this, 'listener'),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'space' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'space',
                    'name'              => __( 'Space:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any space', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_fluentcommunity_get_spaces',
                    'options_cb'        => 'automatorwp_fluentcommunity_options_cb_space',
                    'default'           => 'any'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_times_tag()
            )
        ));
    }
    
    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param object    $space
     * @param int       $userId    
     * @param string    $by   Self, by_admin, by_automation
     */
    public function listener( $space, $userId, $by ) {

        $user_id = get_current_user_id();
 
        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        $space_data = $space->getOriginal();

        // Trigger event when user performs a comment action
        automatorwp_trigger_event(array(
            'trigger'   => $this->trigger,
            'user_id'   => $user_id,
            'space_id'  => $space_data['id'],
        ));
    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */


     public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post is not received
        if( ! isset( $event['space_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( $trigger_options['space'] !== 'any' &&  absint( $trigger_options['space'] ) !== absint( $event['space_id'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_FluentCommunity_Join_Space();