<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Trigger_Email_Spam extends AutomatorWP_Integration_Trigger {

    public $integration = 'elasticmailsender';
    public $trigger     = 'elasticmailsender_email_spam';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User marks an email as Spam', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'User marks an email as <strong>Spam</strong>', 'automatorwp-elasticmailsender' ),
            'action'            => 'elasticmailsender_webhook_event_spam',
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

new AutomatorWP_ElasticMailSender_Trigger_Email_Spam();