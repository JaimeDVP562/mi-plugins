<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Trigger class for Funnel Completed
if ( class_exists( 'AutomatorWP_Integration_Trigger' ) ) {
    class AutomatorWP_Mailmint_Trigger_Funnel_Completed extends AutomatorWP_Integration_Trigger {
        public $integration = 'mailmint';
        public $trigger = 'wpfunnels_funnel_completed';

        public function register() {
            automatorwp_register_trigger( $this->integration, $this->trigger, array(
                'label' => __( 'WP Funnels - Funnel completed', 'automatorwp-funnels-mail-mint' ),
                'action_fields' => array(
                    'funnel_id' => array( 'label' => __( 'Funnel', 'automatorwp-funnels-mail-mint' ) )
                ),
                'function' => array( $this, 'listener' )
            ) );
        }

        public function listener( $user_id, $funnel_id, $args ) {
            // Called when WP Funnels fires the event — map $args and call AutomatorWP trigger
            automatorwp_trigger_event( $this->integration, $this->trigger, $user_id, array( 'funnel_id' => $funnel_id ) );
        }

        public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
            // Keep default behaviour: allow the trigger to run.
            // Implement custom checks here if needed using the provided parameters.
            return $deserves_trigger;
        }
    }

    add_action( 'automatorwp_register_triggers', function() {
        $t = new AutomatorWP_Mailmint_Trigger_Funnel_Completed();
        $t->register();
    } );
}
