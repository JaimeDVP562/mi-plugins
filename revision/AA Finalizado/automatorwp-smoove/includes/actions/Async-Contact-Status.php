<?php
/**
 * Get Async Contact Status
 *
 * @package     AutomatorWP\Integrations\Smoove\Actions\Get-Async-Contact-Status
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Smoove_Get_Async_Contact_Status extends AutomatorWP_Integration_Action {

    public $integration = 'smoove';
    public $action = 'smoove_get_async_contact_status';

    /**
     * Register the action
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Get async contact status', 'automatorwp-smoove'),
            'select_option'     => __( 'Get <strong>async contact status</strong>', 'automatorwp-smoove'),
            'edit_label'        => sprintf( __( 'Retrieve async contact status %1$s', 'automatorwp-smoove' ), '{operation}' ),
            'log_label'         => sprintf( __( 'Retrieved async contact status %1$s', 'automatorwp-smoove' ), '{operation}' ),
            'options'           => array(
                'operation' => array(
                    'from' => 'operation',
                    'default' => __('Operation ID', 'automatorwp-smoove'),
                    'fields' => array(
                        'operation_id' => array(
                            'name'          => __('Operation ID:', 'automatorwp-smoove'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options
     * @param stdClass  $automation     The action's automation object
     *
     * @return void
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        $operation_id = $action_options['operation_id'];
        
        // Bail if required fields are empty
        if ( empty( $operation_id ) ) {
            $this->result = __( 'Operation ID is empty', 'automatorwp-smoove' );
            return;
        }

        // Bail if Smoove API key is not configured
        if ( ! automatorwp_smoove_get_option( 'api_key' ) ) {
            $this->result = __( 'Smoove integration is not configured in AutomatorWP settings', 'automatorwp-smoove' );
            return;
        }

        // Call API
        $response = automatorwp_smoove_get_async_contact_status( $operation_id );
        
        // Check if API call was successful and response contains a status
        if ( $response !== false && isset( $response['status'] ) ) {
            // Include the exact status from Smoove in the AutomatorWP log result
            $this->result = sprintf( __( 'Async contact status retrieved successfully. Status: %s', 'automatorwp-smoove' ), sanitize_text_field( $response['status'] ) );
        } else {
            $this->result = __( 'Failed to retrieve async contact status or invalid ID', 'automatorwp-smoove' );
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     *
     * @return void
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
     *
     * @return void
     */
    public function configuration_notice( $object, $item_type ) {
        // Bail if action type don't match this action
        if ( $item_type !== 'action' || $object->type !== $this->action ) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if ( ! automatorwp_smoove_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Smoove settings</a> to get this action to work.', 'automatorwp-smoove' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-smoove'
                ); ?>
            </div>
        <?php endif;
    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta       Log meta data
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options
     * @param stdClass  $automation     The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        // Bail if action type don't match this action
        if ( $action->type !== $this->action ) {
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
        if ( $log->type !== 'action' || $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-smoove' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}
new AutomatorWP_Smoove_Get_Async_Contact_Status();