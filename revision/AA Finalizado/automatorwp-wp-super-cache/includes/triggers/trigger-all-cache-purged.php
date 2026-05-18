<?php
/**
 * Trigger: All caches are purged
 */

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WP_Super_Cache_All_Cache_Purged extends AutomatorWP_Integration_Trigger {

    public $integration = 'wp_super_cache';
    public $trigger     = 'wp_super_cache_all_cache_purged';

    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'All caches are purged', 'automatorwp-wp-super-cache' ),
            'select_option' => __( '<strong>All caches</strong> are purged', 'automatorwp-wp-super-cache' ),
            'edit_label'    => sprintf( __( 'All caches are purged %1$s time(s)', 'automatorwp-wp-super-cache' ), '{times}' ),
            'log_label'     => __( 'All caches are purged', 'automatorwp-wp-super-cache' ),
            'action'        => 'wp_cache_cleared', 
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    public function listener() {
        
        $user_id = get_current_user_id();

        if ( empty( $user_id ) ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => absint( $user_id ),
        ) );

    }
}

new AutomatorWP_WP_Super_Cache_All_Cache_Purged();