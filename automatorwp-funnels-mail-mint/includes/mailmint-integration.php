<?php
/**
 * Mail Mint -> AutomatorWP integration
 * Maps Mail Mint plugin hooks to AutomatorWP triggers.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_MailMint_Integration {

    public function __construct() {
        $this->hooks();
    }

    private function hooks() {
        add_action('mailmint_after_form_submit', [$this, 'form_submitted'], 10, 3);
        add_action('mailmint_before_form_submit', [$this, 'before_form_submit'], 10, 2);
        add_action('mailmint_list_applied', [$this, 'list_applied'], 10, 2);
        add_action('mailmint_send_campaign_email', [$this, 'campaign_sent'], 10, 1);
        add_action('mailmint_post_install', [$this, 'post_install'], 10, 1);
    }

    public function form_submitted( $form_id, $contact_id, $contact ) {
        if ( function_exists( 'automatorwp_trigger_event' ) ) {
            automatorwp_trigger_event( 'trigger_mailmint_form_submitted', array(
                'form_id'    => $form_id,
                'contact_id' => $contact_id,
                'contact'    => $contact,
            ) );

            // Backwards/alternative name
            automatorwp_trigger_event( 'trigger_mailmint_after_form_submit', array(
                'form_id'    => $form_id,
                'contact_id' => $contact_id,
                'contact'    => $contact,
            ) );
        }
    }

    public function before_form_submit( $form_id, $contact ) {
        if ( function_exists( 'automatorwp_trigger_event' ) ) {
            automatorwp_trigger_event( 'trigger_mailmint_before_form_submit', array(
                'form_id' => $form_id,
                'contact' => $contact,
            ) );
        }
    }

    public function list_applied( $lists, $contact_id ) {
        if ( function_exists( 'automatorwp_trigger_event' ) ) {
            automatorwp_trigger_event( 'trigger_mailmint_list_applied', array(
                'lists'      => $lists,
                'contact_id' => $contact_id,
            ) );
        }
    }

    public function campaign_sent( $messages ) {
        if ( function_exists( 'automatorwp_trigger_event' ) ) {
            automatorwp_trigger_event( 'trigger_mailmint_campaign_sent', array(
                'messages' => $messages,
            ) );
        }
    }

    public function post_install( $new_version ) {
        if ( function_exists( 'automatorwp_trigger_event' ) ) {
            automatorwp_trigger_event( 'trigger_mailmint_post_install', array(
                'new_version' => $new_version,
            ) );
        }
    }

    // TODO: Scan plugin for additional undocumented hooks:
    // Search patterns: do_action('mailmint_'), apply_filters('mailmint_')
    // Possible hooks to verify: mailmint_tag_applied, mailmint_tag_removed,
    // mailmint_contact_created, mailmint_contact_updated, mailmint_email_opened,
    // mailmint_email_clicked, mailmint_automation_step_completed

}

new AutomatorWP_MailMint_Integration();
