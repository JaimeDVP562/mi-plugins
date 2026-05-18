<?php
/**
 * Create Card
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Create-Card
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Keap_Change_Card_List extends AutomatorWP_Integration_Action {

    public $integration = 'keap';
    public $action = 'keap_change_card_list';
    
    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Change a card list', 'automatorwp-keap'),
            'select_option'     => __( 'Change a card <strong>list</strong>', 'automatorwp-keap'),
            /* translators: %1$s: Card. */
            'edit_label'        => sprintf( __( 'Change %1$s list', 'automatorwp-keap' ), '{card}' ),
            /* translators: %1$s: Card. */
            'log_label'         => sprintf( __( 'Change %1$s list', 'automatorwp-keap' ) , '{card}'),
            'options'           => array(
                'card' => array(
                    'from' => 'card',
                    'default' => __('card', 'automatorwp-keap'),
                    'fields' => array(
                        'board' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __('Board: ', 'automatorwp-keap'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_keap_get_boards',
                            'options_cb'        => 'automatorwp_keap_options_cb_board',
                            'placeholder'       => 'Select a board',
                            'default'           => '',
                            'required'          => true
                        )),
                        'list' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __('List: ', 'automatorwp-keap'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_keap_get_lists_from_board',
                            'options_cb'        => 'automatorwp_keap_options_cb_list',
                            'placeholder'       => 'Select a list',
                            'default'           => '',
                            'required'          => true
                        )),
                        'card' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __('Card: ', 'automatorwp-keap'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_keap_get_cards_from_list',
                            'options_cb'        => 'automatorwp_keap_options_cb_card',
                            'placeholder'       => 'Select a Card',
                            'default'           => '',
                            'required'          => true
                        )),
                        'new_list' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __('New list: ', 'automatorwp-keap'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_keap_get_lists_from_board',
                            'options_cb'        => 'automatorwp_keap_options_cb_list',
                            'default'           => '',
                            'placeholder'       => 'Select a new list',
                            'desc'              => 'Select a new list for the selected card.',
                            'required'          => true
                        ))
                    )
                ))

            ),
        );
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
        $card_id = $action_options['card'];
        $new_list_id = $action_options['new_list'];
        
        // Bail if card_id is empty
        if ( empty ( $card_id ) ) {
            return;
        }

        // Bail if new_list_id is empty
        if ( empty ( $new_list_id ) ) {
            return;
        }

        // Bail if Keap not configured
        if( ! automatorwp_keap_get_api() ) {
            $this->result = __( 'Keap integration is not configured in AutomatorWP settings', 'automatorwp-keap' );
            return;
        }

        $response = automatorwp_keap_change_card_list( $card_id, $new_list_id );

        if( $response === 200 ) {
            $this->result = __( 'Changed card list', 'autoamtorwp-keap' );
        }else {
            $this->result = __( 'The card list could not be changed', 'automatorwp-keap' );
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
        if( ! automatorwp_keap_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Keap settings</a> to get this action to work.', 'automatorwp-trello' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-keap'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-keap' ),
                    'https://api.infusionsoft.com/crm/rest/v2/automations'
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
            'name' => __( 'Result:', 'automatorwp-keap' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}
new AutomatorWP_Keap_Change_Card_List();