<?php
/**
 * Post Added
 *
 * @package     AutomatorWP\Integrations\FluentCommunity\Triggers\Post_Added
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCommunity_Post_Added extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentcommunity';
    public $trigger = 'fluentcommunity_post_added';
    
    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {
    
        automatorwp_register_trigger($this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User writes a post in a space', 'automatorwp' ),
            'select_option'     => __( 'User writes a <strong>post</strong> in a space', 'automatorwp' ),
            /* translators: %1$s: Space title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User writes a post in %1$s %2$s time(s)', 'automatorwp' ), '{space}', '{times}' ),
            /* translators: %1$s: Space title. */
            'log_label'         => sprintf( __( 'User writes a post in %1$s', 'automatorwp' ), '{space}' ),
            'action'            => 'fluent_community/feed/created',
            'function'          => array($this, 'listener'),
            'priority'          => 10,
            'accepted_args'     => 1,
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
     * @param object    $feed   
     */
    public function listener( $feed ) {

        $user_id = get_current_user_id();
 
        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        $feed_data = $feed->getOriginal();
 
        // Trigger event when user writes a post in a space
        automatorwp_trigger_event(array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'comment'       => $feed_data['message'],
            'comment_id'    => $feed_data['id'],
            'space_id'      => $feed_data['space_id'],
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

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['comment'] = ( isset( $event['comment'] ) ? $event['comment'] : array() );

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an trigger
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        // Bail if trigger type don't match this trigger
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['comment'] = array(
            'name' => __( 'Post content', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_FluentCommunity_Post_Added();