<?php
/**
 * Send a welcome email when a lead is captured on Sendpulse
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Actions\Send-Welcome-Email
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Sendpulse_Send_Welcome_Email extends AutomatorWP_Integration_Action {

    public $integration = 'sendpulse';
    public $action = 'sendpulse_send_welcome_email';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action($this->action, array(
            'integration'       => $this->integration,
            'label'             => __('Send a welcome email to a Sendpulse Lead', 'automatorwp-sendpulse'),
            'select_option'     => __('Send a <strong>welcome email</strong> to a Sendpulse Lead', 'automatorwp-sendpulse'),
            'edit_label'        => __('Send a welcome email to {email}', 'automatorwp-sendpulse'),
            'log_label'         => __('Sent welcome email to {email}', 'automatorwp-sendpulse'),
            'options'           => array(
                'lead' => array(
                    'from' => 'lead',
                    'fields' => array(
                        'name' => array(
                            'name'        => __('Lead Name:', 'automatorwp-sendpulse'),
                            'type'        => 'text',
                            'default'     => '',
                            'required'    => true
                        ),
                        'email' => array(
                            'name'        => __('Lead Email:', 'automatorwp-sendpulse'),
                            'type'        => 'email',
                            'default'     => '',
                            'required'    => true
                        ),
                        'subject' => array(
                            'name'        => __('Email Subject:', 'automatorwp-sendpulse'),
                            'type'        => 'text',
                            'default'     => 'Welcome to our community!',
                            'required'    => true
                        ),
                        'message' => array(
                            'name'        => __('Email Message:', 'automatorwp-sendpulse'),
                            'type'        => 'textarea',
                            'default'     => __('Hello {name}, welcome! We are excited to have you.', 'automatorwp-sendpulse'),
                            'required'    => true
                        ),
                    )
                )
            )
        ));
    }

    /**
     * Execute action
     *
     * @since 1.0.0
     */
    public function execute($action, $user_id, $action_options, $automation) {

        $lead_name = sanitize_text_field($action_options['name']);
        $lead_email = sanitize_email($action_options['email']);
        $subject = sanitize_text_field($action_options['subject']);
        $message = wp_kses_post($action_options['message']);

        if (empty($lead_email)) {
            $this->result = __('Error: No email provided.', 'automatorwp-sendpulse');
            return;
        }

        // Reemplazar {name} en el mensaje con el nombre real del lead
        $message = str_replace('{name}', $lead_name, $message);

        // Definir headers para el email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

        // Enviar email
        $sent = wp_mail($lead_email, $subject, nl2br($message), $headers);

        if ($sent) {
            $this->result = __('Welcome email sent successfully.', 'automatorwp-sendpulse');
        } else {
            $this->result = __('Failed to send welcome email.', 'automatorwp-sendpulse');
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        parent::hooks();
    }

    /**
     * Action log metadata
     *
     * @since 1.0.0
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation) {

        if ($action->type !== $this->action) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    /**
     * Action log fields
     *
     * @since 1.0.0
     */
    public function log_fields($log_fields, $log, $object) {

        if ($log->type !== 'action' || $object->type !== $this->action) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __('Result:', 'automatorwp-sendpulse'),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Sendpulse_Send_Welcome_Email();
