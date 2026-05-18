<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Trigger_Email_Bounced extends AutomatorWP_Integration_Trigger {

    public $integration = 'elasticmailsender';
    public $trigger     = 'elasticmailsender_email_bounced';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'Email sent to user bounces', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'Email sent to user <strong>bounces</strong>', 'automatorwp-elasticmailsender' ),
            'action'            => 'elasticmailsender_webhook_event_bounced',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(),
        ) );
    }

    public function listener( $email ) {
        $user = get_user_by( 'email', $email );
        if ( $user ) {
            automatorwp_trigger_event( array(
                'trigger' => $this->trigger,
                'user_id' => $user->ID,
            ) );
        }
    }
}

new AutomatorWP_ElasticMailSender_Trigger_Email_Bounced();