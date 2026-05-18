<?php
/**
 * Add subaccount
 *
 * @package     AutomatorWP\Integrations\MemberPress\Triggers\Add_Subaccount
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Add_Subaccount extends AutomatorWP_Integration_Trigger {

    public $integration = 'memberpress';
    public $trigger = 'memberpress_add_subaccount';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'A sub account is added to a parent account', 'automatorwp-memberpress' ),
            'select_option'     => __( 'A <strong>sub account</strong> is added to a parent account', 'automatorwp-memberpress' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'A sub account is added to %1$s %2$s time(s)', 'automatorwp-memberpress' ), '{parent}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'A sub account is added to %1$s', 'automatorwp-memberpress' ), '{parent}' ),
            'action'            => 'mpca_add_sub_account',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'parent' => array(
                    'from' => 'parent',
                    'default' => __( 'parent', 'automatorwp-memberpress' ),
                    'fields' => array(
                        'parent' => array(
                            'name' => __( 'Parent ID:', 'automatorwp-memberpress' ),
                            'desc' => __( 'The parent account ID.', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'required'  => true,
                            'default' => 'any'
                        ),
                     ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_memberpress_member_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @param $txn_id
	 * @param $parent_txn_id
     * 
     * @since 1.0.0
     */
    public function listener( $txn_id, $parent_txn_id ) {

        // Get the parent ID
		$parent_user_obj =  new \MeprTransaction( $parent_txn_id );
        $parent_user = $parent_user_obj->user()->ID;

        // Get the subaccount ID
        $child_user_obj =  new \MeprTransaction( $txn_id );
        $child_user = $child_user_obj->user()->ID;

        // Get Membership ID and Title
        $membership_id = $child_user_obj->product_id;
        $membership_title = get_the_title( $membership_id );

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $child_user,
            'parent_id' => $parent_user,
            'membership_id' => $membership_id,
            'membership_title' => $membership_title,
        ) );

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

        // Don't deserve if parent is not received
        if( ! isset( $event['parent_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( $trigger_options['parent'] !== 'any' && absint( $trigger_options['parent'] ) !== absint( $event['parent_id'] ) ) {
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

        $log_meta['user_id'] = ( isset( $event['user_id'] ) ? $event['user_id'] : 0 );
        $log_meta['parent_id'] = ( isset( $event['parent_id'] ) ? $event['parent_id'] : 0 );
        $log_meta['membership_id'] = ( isset( $event['membership_id'] ) ? $event['membership_id'] : 0 );
        $log_meta['membership_title'] = ( isset( $event['membership_title'] ) ? $event['membership_title'] : '' );

        return $log_meta;

    }

}

new AutomatorWP_MemberPress_Add_Subaccount();