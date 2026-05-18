<?php
/**
 * Send email
 *
 * @package     AutomatorWP\Integrations\Brevo\Actions\Send email
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_Brevo_Send_Email extends AutomatorWP_Integration_Action
{

    public $integration = 'brevo';
    public $action = 'brevo_send_email';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_action(
            $this->action,
            array(
                'integration' => $this->integration,
                'label' => __('Send a transactional email', 'automatorwp-brevo'),
                'select_option' => __('<strong>Send</strong> a transactional <strong>email</strong>', 'automatorwp-brevo'),
                /* translators: %1$s: Contact. */
                'edit_label' => sprintf(__('Send %1$s', 'automatorwp-brevo'), '{email}'),
                /* translators: %1$s: Contact. */
                'log_label' => sprintf(__('Send %1$s', 'automatorwp-brevo'), '{email}'),
                'options' => array(
                    'email' => array(
                        'from' => 'email',
                        'default' => __('email', 'automatorwp-brevo'),
                        'fields' => array(
                            'sender_email' => array(
                                'name' => __('From:', 'automatorwp-brevo'),
                                'desc' => __('The sender email.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'reply_email' => array(
                                'name' => __('Reply to email:', 'automatorwp-brevo'),
                                'desc' => __('The reply to email.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'receiver_email' => array(
                                'name' => __('To:', 'automatorwp-brevo'),
                                'desc' => __('The receiver email.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'subject' => array(
                                'name' => __('Email subject:', 'automatorwp-brevo'),
                                'desc' => __('The email subject.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'textContent' => array(
                                'name' => __('Email content:', 'automatorwp-brevo'),
                                'desc' => __('The sender name.', 'automatorwp-brevo'),
                                'type' => 'textarea',
                                'required' => false,
                                'default' => ''
                            ),
                        )
                    )
                )
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
    public function execute($action, $user_id, $action_options, $automation)
    {
        
        // TODO: make scheduled date optional (scheduledAt)
        // TODO: make template usage optional (templateId)

        $email_data = array(
            'sender' => array(
                'email' => $action_options['sender_email'] ?? ''
            ),
            'replyTo' => array(
                'email' => $action_options['reply_email'] ?? ''
            ),
            'to' => array(
                array(
                    'email' => $action_options['receiver_email'] ?? ''
                )
            ),
            'subject' => $action_options['subject'] ?? '',
            'textContent' => $action_options['textContent'] ?? ''
        );


        // Bail if user email is empty
        if ( empty( $email_data['sender']['email'] ) ) {
            $this->result = __('Sender email field is empty.', 'automatorwp-brevo');
            return;
        }

        $this->result = '';

        // Bail if brevo not configured
        if (!automatorwp_brevo_get_api()) {
            $this->result = __('Brevo integration not configured in AutomatorWP settings.', 'automatorwp-brevo');
            return;
        }

        $response = automatorwp_brevo_send_email($email_data);

        if ($response === 201 || $response === 202) {
            $this->result = sprintf(__('Email from %s sended', 'automatorwp-brevo'), $action_options['sender_email']);
        } else {
            $this->result = __('The email could not be sended', 'automatorwp-brevo');
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {

        // Configuration notice
        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);

        // Log meta data
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);

        // Log fields
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

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
    public function configuration_notice($object, $item_type)
    {

        // Bail if action type don't match this action
        if ($item_type !== 'action') {
            return;
        }

        if ($object->type !== $this->action) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if (!automatorwp_brevo_get_api()): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __('You need to configure the <a href="%s" target="_blank">brevo settings</a> to get this action to work.', 'automatorwp-brevo'),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-brevo'
                ); ?>
                <?php echo sprintf(
                    __('<a href="%s" target="_blank">Documentation</a>', 'automatorwp-brevo'),
                    'https://automatorwp.com/docs/brevo/'
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
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {

        // Bail if action type don't match this action
        if ($action->type !== $this->action) {
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
    public function log_fields($log_fields, $log, $object)
    {
        // Bail if log is not assigned to an action
        if ($log->type !== 'action') {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if ($object->type !== $this->action) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __('Result:', 'automatorwp-brevo'),
            'type' => 'text',
        );
        return $log_fields;
    }

}

new AutomatorWP_Brevo_Send_Email();