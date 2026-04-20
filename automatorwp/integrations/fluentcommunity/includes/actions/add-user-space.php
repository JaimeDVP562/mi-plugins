<?php
/**
 * Add User Space
 *
 * @package     AutomatorWP\Integrations\FluentCommunity\Actions\Add_User_Space
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCommunity_Add_User_Space extends AutomatorWP_Integration_Action {

    public $integration = 'fluentcommunity';
    public $action = 'fluentcommunity_add_user_space';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add user to a space', 'automatorwp' ),
            'select_option'     => __( 'Add user to a <strong>space</strong>', 'automatorwp' ),
            /* translators: %1$s: Space. */
            'edit_label'        => sprintf( __( 'Add user to %1$s', 'automatorwp' ), '{space}' ),
            /* translators: %1$s: Space. */
            'log_label'         => sprintf( __( 'Add user to %1$s', 'automatorwp' ), '{space}' ),
            'options'           => array(
                'space' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'space',
                    'option_default'    => __( 'space', 'automatorwp' ),
                    'name'              => __( 'Space:', 'automatorwp' ),
                    'option_none'       => false,
                    'action_cb'         => 'automatorwp_fluentcommunity_get_spaces',
                    'options_cb'        => 'automatorwp_fluentcommunity_options_cb_space',
                    'placeholder'       => 'Select a space',
                    'default'           => ''
                ) ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $space_id = $action_options['space'];
        $this->result = '';

        // Bail if empty space
        if ( empty( $space_id ) )
            return;

        try {
            \FluentCommunity\App\Services\Helper::addToSpace( $space_id, $user_id, 'member', 'by_admin' );
        }
        catch (\Exception $e) {
            $this->result = __( 'The user could not be added to space.', 'automatorwp' );
            return;
        }

        $this->result = __( 'User added to space successfully.', 'automatorwp' );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }    

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

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

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_FluentCommunity_Add_User_Space();