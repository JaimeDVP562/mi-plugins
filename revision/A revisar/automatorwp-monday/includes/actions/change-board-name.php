<?php
/**
 * Add checklist item to a card
 *
 * @package     AutomatorWP\Integrations\Monday\Actions\Add-Label
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

 // Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Monday_Change_Board_Name extends AutomatorWP_Integration_Action {

    public $integration = 'monday';
    public $action = 'monday_change_board_name';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Change board name', 'automatorwp-monday'),
            'select_option'     => __( 'Change a board\'s name', 'automatorwp-monday'),
            /* translators: %1$s: Card. */
            'edit_label'        => sprintf( __( 'Change %1$s', 'automatorwp-monday' ), '{board}' ),
            /* translators: %1$s: Card. */
            'log_label'         => sprintf( __( 'Change %1$s', 'automatorwp-monday' ), '{board}' ),
            'options'           => array(
                'board' => array(
                    'from' => 'board',
                    'default' => __('board', 'automatorwp-monday'),
                    'fields' => array(
                        'board' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __('Board: ', 'automatorwp-monday'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_monday_get_boards',
                            'options_cb'        => 'automatorwp_monday_options_cb_board',
                            'placeholder'       => 'Select a board',
                            'default'           => '',
                            'required'          => true
                        )),
                        'new_name' => array(
                            'name' => __('New board name:', 'automatorwp-monday'),
                            'type' => 'text',
                            'default' => '',
                            'required' => true
                        ),
                    )
                )
            )
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
        
        // Bail if Monday not configured
        if( ! automatorwp_monday_get_api() ) {
            $this->result = __( 'Monday integration is not configured in AutomatorWP settings', 'automatorwp-monday' );
            return;
        }

        $board_id = $action_options['board'];
        $new_name = $action_options['new_name'];

        $response = automatorwp_monday_change_board_name( $board_id, $new_name );
        
        if( $response === 200 ) {
            $this->result = __( 'Changed board name', 'autoamtorwp-monday' );
        }else {
            $this->result = __( 'Could not change board name', 'automatorwp-monday' );
        }

    }

    
    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    
     /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {

        // Bail if action type don't match this action
        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if( ! automatorwp_monday_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Monday settings</a> to get this action to work.', 'automatorwp-monday' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-monday'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-monday' ),
                    'https://automatorwp.com/docs/monday/'
                ); ?>
            </div>
            <?php endif;
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

        // Store the action's result
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
            'name' => __( 'Result:', 'automatorwp-monday' ),
            'type' => 'text',
        );

        return $log_fields;
    }
    
}
new AutomatorWP_Monday_Change_Board_Name();