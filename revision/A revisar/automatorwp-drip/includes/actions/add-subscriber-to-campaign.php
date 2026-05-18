<?php
/**
 * Add Subscriber To Campaign
 *
 * @package     AutomatorWP\Integrations\Drip\Actions\Add_Subscriber_To_Campaign
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Add_Subscriber_To_Campaign extends AutomatorWP_Integration_Action {

    public $integration = 'drip';
    public $action      = 'drip_add_subscriber_to_campaign';
    public $result      = '';

    /**
     * Register action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add subscriber to campaign', 'automatorwp-drip' ),
            'select_option' => __( 'Add <strong>subscriber</strong> to campaign', 'automatorwp-drip' ),
            /* translators: %1$s: Email. */
            'edit_label'    => sprintf( __( 'Add %1$s to campaign', 'automatorwp-drip' ), '{email}' ) ?: '',
            /* translators: %1$s: Email. */
            'log_label'     => sprintf( __( 'Add %1$s to campaign', 'automatorwp-drip' ), '{email}' ) ?: '',
            'options'       => array(
                'email' => array(
                    'from'    => 'email',
                    'default' => __( 'subscriber', 'automatorwp-drip' ),
                    'fields'  => array(
                        'email' => array(
                            'name'       => __( 'Email:', 'automatorwp-drip' ),
                            'desc'       => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'sample@email.com or use the tag selector', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
                        ),
                        'campaign' => automatorwp_utilities_ajax_selector_field( array(
                            'name'       => __( 'Campaign:', 'automatorwp-drip' ),
                            'desc'       => __( 'Select the campaign in your Drip account.', 'automatorwp-drip' ),
                            'type'       => 'select',
                            'field'      => 'campaign',
                            'action_cb'  => 'automatorwp_drip_get_campaigns',
                            'options_cb' => 'automatorwp_drip_options_cb_campaign',
                            'attributes' => array(
                                'placeholder' => __( 'Select campaign', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
                        ) ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Bail if Drip not configured
        if ( ! automatorwp_drip_get_api() ) {
            $this->result = __( 'Drip integration not configured.', 'automatorwp-drip' );
            return;
        }

        // Get Email (fallback to the user who triggers the automation)
        $email = isset( $action_options['email'] ) && ! empty( $action_options['email'] )
            ? sanitize_email( $action_options['email'] )
            : '';

        $user = null;

        if ( empty( $email ) ) {
            $user  = get_user_by( 'ID', $user_id );
            $email = $user ? $user->user_email : '';
        }

        $campaign_id = isset( $action_options['campaign'] ) ? sanitize_text_field( $action_options['campaign'] ) : '';

        if ( empty( $email ) || empty( $campaign_id ) ) {
            $this->result = __( 'No email or campaign ID provided.', 'automatorwp-drip' );
            return;
        }

        // Resolve WP user metadata for subscriber name fields
        if ( ! $user ) {
            $user = get_user_by( 'email', $email );
        }

        $subscriber_data = array(
            'email'      => $email,
            'first_name' => $user ? $user->first_name : '',
            'last_name'  => $user ? $user->last_name  : '',
        );

        $response = automatorwp_drip_add_subscriber_campaign( $subscriber_data, $campaign_id );

        if ( $response['code'] === 201 || $response['code'] === 200 ) {
            $this->result = sprintf( __( 'Subscriber %1$s added to campaign %2$s.', 'automatorwp-drip' ), $email, $campaign_id );
        } else {
            $this->result = sprintf( __( 'Drip API error: HTTP %d', 'automatorwp-drip' ), $response['code'] );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

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

        if ( $item_type !== 'action' || $object->type !== $this->action ) return;

        if ( ! automatorwp_drip_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Drip settings</a> to get this action to work.', 'automatorwp-drip' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-drip'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-drip' ),
                    'https://automatorwp.com/docs/drip/'
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
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) return $log_meta;

        $log_meta['result'] = (string) $this->result;

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

        if ( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;

        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-drip' ), 'type' => 'text' );

        return $log_fields;

    }

}

new AutomatorWP_Drip_Add_Subscriber_To_Campaign();
