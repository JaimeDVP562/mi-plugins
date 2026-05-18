<?php
/**
 * Get Landing Pages
 *
 * @package     AutomatorWP\Integrations\Smoove\Actions\Get-Landingpage
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Smoove_Get_Landingpage extends AutomatorWP_Integration_Action {

    public $integration = 'smoove';
    public $action = 'smoove_get_landingpage';

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
            'label'             => __( 'Get landing pages', 'automatorwp-smoove' ),
            'select_option'     => __( 'Get <strong>landing pages</strong>', 'automatorwp-smoove' ),
            'edit_label'        => __( 'Retrieve landing pages from Smoove', 'automatorwp-smoove' ),
            'log_label'         => __( 'Retrieved landing pages from Smoove', 'automatorwp-smoove' ),
            'options'           => array(), // No extra options required for this action
        ) );
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
        
        // Bail if Smoove API key is not configured
        if ( ! automatorwp_smoove_get_option( 'api_key' ) ) {
            $this->result = __( 'Smoove integration is not configured in AutomatorWP settings', 'automatorwp-smoove' );
            return;
        }

        // Call API to get landing pages
        $response = automatorwp_smoove_get_landing_pages();

        // Check if API call was successful and catch explicit Smoove errors
        if ( is_wp_error( $response ) ) {
            $error_data = $response->get_error_data( 'api_error' );
            $body       = isset( $error_data['response'] ) ? $error_data['response'] : $response->get_error_message();
            $this->result = sprintf( __( 'Failed to retrieve landing pages. Smoove said: %s', 'automatorwp-smoove' ), sanitize_text_field( $body ) );
        } elseif ( is_array( $response ) ) {
            $this->result = __( 'Landing pages retrieved successfully from Smoove', 'automatorwp-smoove' );
        } else {
            $this->result = __( 'Failed to retrieve landing pages from Smoove. Unknown error.', 'automatorwp-smoove' );
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
new AutomatorWP_Smoove_Get_Landingpage();