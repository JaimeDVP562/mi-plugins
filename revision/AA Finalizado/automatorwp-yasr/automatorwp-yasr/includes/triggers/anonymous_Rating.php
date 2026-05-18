<?php
/**
 * Trigger for YASR (Yet Another Stars Rating), redirects anonymous user
 *
 * @package     AutomatorWP\Integrations\YASR
 * @since       1.0.0
 */
class AutomatorWP_YASR_Anonymous_Rating extends AutomatorWP_Integration_Trigger {

public $integration = 'yasr';
public $trigger = 'yasr_anonymous_rating';

/**
 * Register the trigger
 *
 * @since 1.0.0
 */
public function register() {
    automatorwp_register_trigger( $this->trigger, array(
        'integration'       => $this->integration,
        'anonymous'         => true,
        'label'             => __( 'Anonymous user submits a rating with YASR', 'automatorwp-yasr' ),
        'select_option'     => __( 'Anonymous user submits a rating with YASR', 'automatorwp-yasr' ),
        'edit_label'        => __( 'Anonymous user submits a rating with YASR', 'automatorwp-yasr' ),
        'log_label'         => __( 'Anonymous user submits a rating with YASR', 'automatorwp-yasr' ),
        'action'            => 'yasr/after_save_comment',
        'function'          => array( $this, 'listener' ),
        'priority'          => 10,
        'accepted_args'     => 2,
        'options'           => array(
            // No options needed
        )
    ) );
}

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int    $comment_id    ID of the comment
 * @param object $comment       Comment object
 */
public function listener( $comment_id, $comment ) {
    // Check if the user is anonymous
    if ( 0 === $comment->user_id ) {
        // Perform actions to request user identification
        // For example, you can redirect the user to the login or registration page
        wp_redirect( wp_login_url( get_permalink() ) );
        exit;
    }
}
}

new AutomatorWP_YASR_Anonymous_Rating();