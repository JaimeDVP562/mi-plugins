<?php
/**
 * WP Funnels -> AutomatorWP integration
 * Maps common WP Funnels hooks to the triggers registered by this add-on.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_WPFunnels_Integration {

    public function __construct() {
        $this->hooks();
    }

    private function hooks() {
        // Listen to several common hook name variants used by different WP Funnels installs
        $step_hooks = array(
            'wpfunnels_step_submitted',
            'wpfunnels_after_step_submit',
            'wpfunnels_step_submit',
            'wpfunnels_step_completed',
        );

        foreach ( $step_hooks as $hook ) {
            add_action( $hook, array( $this, 'step_submitted' ), 10, 3 );
        }

        $funnel_hooks = array(
            'wpfunnels_funnel_completed',
            'wpfunnels_after_funnel_complete',
            'wpfunnels_funnel_complete',
        );

        foreach ( $funnel_hooks as $hook ) {
            add_action( $hook, array( $this, 'funnel_completed' ), 10, 2 );
        }
    }

    public function step_submitted() {
        $args = func_get_args();

        $user_id = 0;
        $step_id = 0;

        // Try to extract common shapes: ( $step_id, $user_id ) or ( $user_id, $step_id ) or arrays
        foreach ( $args as $a ) {
            if ( is_int( $a ) && $a > 0 && $user_id === 0 ) {
                $user_id = $a;
            }
            if ( is_array( $a ) ) {
                if ( isset( $a['step_id'] ) ) {
                    $step_id = (int) $a['step_id'];
                }
                if ( isset( $a['user_id'] ) && $user_id === 0 ) {
                    $user_id = (int) $a['user_id'];
                }
            }
        }

        if ( $step_id === 0 && isset( $args[0] ) && is_numeric( $args[0] ) ) {
            $step_id = (int) $args[0];
        }
        if ( $user_id === 0 && isset( $args[1] ) && is_numeric( $args[1] ) ) {
            $user_id = (int) $args[1];
        }

        if ( function_exists( 'automatorwp_trigger_event' ) ) {
            automatorwp_trigger_event( 'mailmint', 'wpfunnels_step_submitted', $user_id, array( 'step_id' => $step_id, 'raw_args' => $args ) );
        }
    }

    public function funnel_completed() {
        $args = func_get_args();

        $user_id = 0;
        $funnel_id = 0;

        foreach ( $args as $a ) {
            if ( is_int( $a ) && $a > 0 && $user_id === 0 ) {
                $user_id = $a;
            }
            if ( is_array( $a ) && isset( $a['funnel_id'] ) ) {
                $funnel_id = (int) $a['funnel_id'];
            }
        }

        if ( $funnel_id === 0 && isset( $args[0] ) && is_numeric( $args[0] ) ) {
            $funnel_id = (int) $args[0];
        }
        if ( $user_id === 0 && isset( $args[1] ) && is_numeric( $args[1] ) ) {
            $user_id = (int) $args[1];
        }

        if ( function_exists( 'automatorwp_trigger_event' ) ) {
            automatorwp_trigger_event( 'mailmint', 'wpfunnels_funnel_completed', $user_id, array( 'funnel_id' => $funnel_id, 'raw_args' => $args ) );
        }
    }
}

new AutomatorWP_WPFunnels_Integration();
