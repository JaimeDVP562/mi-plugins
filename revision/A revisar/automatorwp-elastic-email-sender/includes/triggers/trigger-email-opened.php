<?php
/**
 * Email Opened Trigger
 *
 * @package     AutomatorWP\Integrations\ElasticMailSender\Triggers\Email_Opened
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ElasticMailSender_Trigger_Email_Opened extends AutomatorWP_Integration_Trigger {

    public $integration = 'elasticmailsender';
    public $trigger     = 'elasticmailsender_email_opened';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User opens an email sent via Elastic Email', 'automatorwp-elasticmailsender' ),
            'select_option'     => __( 'User opens an <strong>email</strong>', 'automatorwp-elasticmailsender' ),
            'action'            => 'elasticmailsender_webhook_event_opened', // <--- Escucha al router
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

new AutomatorWP_ElasticMailSender_Trigger_Email_Opened();