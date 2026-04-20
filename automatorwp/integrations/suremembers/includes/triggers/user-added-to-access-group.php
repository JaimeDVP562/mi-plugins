<?php
/**
 * Access granted to a group
 *
 * @package     AutomatorWP\Integrations\SureMembers\Triggers\User_Added_To_Access_Group
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SureMembers_User_Added_To_Access_Group extends AutomatorWP_Integration_Trigger {

    public $integration = 'suremembers';
    public $trigger = 'suremembers_user_added_to_access_group';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User added to an access group', 'automatorwp' ),
            'select_option'     => __( 'User added to <strong>an acccess group</strong>', 'automatorwp' ),
            /* translators: %1$s: Group title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User added to %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Group title. */
            'log_label'         => sprintf( __( 'User added to %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'suremembers_after_access_grant', 
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Group:', 'automatorwp' ),
                    'option_none_label' => __( 'any group', 'automatorwp' ),
                    'post_type' => 'wsm_access_group'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags( __( 'Group', 'automatorwp' ) ),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $user_id
     * @param array $access_group_ids
     */
    public function listener( $user_id, $access_group_ids ) {

        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        foreach ( $access_group_ids as $key => $group_id ) {
            
            // Used for hook
            $group = array( $key => $group_id );

            /**
             * Excluded groups event by filter
             *
             * @since 1.0.0
             *
             * @param bool      $exclude        Whatever to exclude or not, by default false
             * @param string    $key            Key
             * @param mixed     $group_id       Group ID
             * @param array     $group          Group setup array
             */
            if( apply_filters( 'automatorwp_suremembers_exclude_group', false, $key, $group_id, $group ) ) {
                continue;
            }
            
            // Trigger submit form event
            automatorwp_trigger_event( array(
                'trigger'       => $this->trigger,
                'user_id'       => $user_id,
                'groups_id'     => $access_group_ids,
                'post_id'       => $group_id
            ) );
        }


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

        // Don't deserve if group is not received
        if( ! isset( $event['post_id'] ) ) {
            return false;
        }

      // Bail if group doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $event['post_id'] ) !== absint( $trigger_options['post'] ) ) {
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

        $log_meta['group_title'] = ( isset( $event['post_id'] ) ? $event['post_id'] : array() );

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

        $log_fields['group_title'] = array(
            'name' => __( 'Group Title', 'automatorwp' ),
            'desc' => __( 'Information about the group Title', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_SureMembers_User_Added_To_Access_Group();