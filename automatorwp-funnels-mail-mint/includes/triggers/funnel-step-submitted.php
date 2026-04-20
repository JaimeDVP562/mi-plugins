<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'AutomatorWP_Integration_Trigger' ) ) {
    class AutomatorWP_Mailmint_Trigger_Step_Submitted extends AutomatorWP_Integration_Trigger {
        public $integration = 'mailmint';
        public $trigger = 'wpfunnels_step_submitted';

        public function register() {
            automatorwp_register_trigger( $this->integration, $this->trigger, array(
                'label' => __( 'WP Funnels - Step submitted', 'automatorwp-funnels-mail-mint' ),
                'action_fields' => array(
                    'step_id' => array( 'label' => __( 'Step', 'automatorwp-funnels-mail-mint' ) )
                ),
                'function' => array( $this, 'listener' )
            ) );
        }

        public function listener( $user_id, $step_id, $args ) {
            automatorwp_trigger_event( $this->integration, $this->trigger, $user_id, array( 'step_id' => $step_id ) );
        }

        public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
            // Keep default behaviour: allow the trigger to run.
            return $deserves_trigger;
        }
    }

    add_action( 'automatorwp_register_triggers', function() {
        $t = new AutomatorWP_Mailmint_Trigger_Step_Submitted();
        $t->register();
    } );
}
