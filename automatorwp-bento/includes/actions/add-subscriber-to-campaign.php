<?php
/**
 * Add Subscriber To Campaign
 *
 * @package     AutomatorWP\Integrations\Bento\Actions\Add_Subscriber_To_Campaign
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Bento_Add_Subscriber_To_Campaign extends AutomatorWP_Integration_Action {

    public $integration = 'bento';
    public $action = 'bento_add_subscriber_to_campaign';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add subscriber to campaign', 'automatorwp' ),
            'select_option'     => __( 'Add <strong>subscriber</strong> to <strong>campaign</strong>', 'automatorwp' ),
            /* translators: %1$s: Campaign. */
            'edit_label'        => sprintf( __( 'Add subscriber to %1$s', 'automatorwp' ), '{campaign}' ),
            /* translators: %1$s: Campaign */
            'log_label'         => sprintf( __( 'Add subscriber to %1$s', 'automatorwp' ), '{campaign}' ),
            'options'           => array(
                'campaign' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'campaign',
                    'option_default'    => __( 'Select a campaign', 'automatorwp' ),
                    'name'              => __( 'Campaign:', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_bento_get_campaigns',
                    'options_cb'        => 'automatorwp_bento_options_cb_campaign',
                    'placeholder' => __( 'Select a campaign', 'automatorwp' ),
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
        $user = get_user_by ( 'ID', $user_id );
        $campaign_id = $action_options['campaign'];

        // Bail if Bento not configured
        if( ! automatorwp_bento_get_api() ) {
            $this->result = __( 'Bento integration not configured in AutomatorWP settings.', 'automatorwp' );
            return;
        }

        // Bail if campaign is empty
        if ( empty( $campaign_id ) ) {
            $this->result = __( 'Campaign field is empty.', 'automatorwp' );
            return;
        }

        $subscriber_data = array(
            'email'         => $user->user_email,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name, 
        );

        $response = automatorwp_bento_add_subscriber_campaign( $subscriber_data, $campaign_id );

        if ( $response === 201 ) {

            $this->result = sprintf( __( '%s added to Bento', 'automatorwp' ), $user->user_email );

        } elseif ( $response === 200 ) {

            $this->result = sprintf( __( '%s updated in Bento', 'automatorwp' ), $user->user_email );
            
        } else $this->result = sprintf( __( 'Error adding subscriber %s to campaign %s: ' . $response, 'automatorwp' ), $user->user_email, $campaign_id );

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
        if( ! automatorwp_bento_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Bento settings</a> to get this action to work.', 'automatorwp' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-bento'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp' ),
                    'https://automatorwp.com/docs/bento/'
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
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Bento_Add_Subscriber_To_Campaign();