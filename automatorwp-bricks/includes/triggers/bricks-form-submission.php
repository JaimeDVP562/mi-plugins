<?php
/**
 * Trigger: Bricks Form Submission
 *
 * @package      AutomatorWP\Bricks\Triggers\Form-Submission
 * @since        1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the trigger
 */
function automatorwp_bricks_register_form_submission_trigger() {

    if ( ! function_exists( 'automatorwp_register_trigger' ) ) {
        return;
    }

    automatorwp_register_trigger( 'bricks_form_submission', array(
        'integration'   => 'bricks',
        'label'         => __( 'A user submits a form', 'automatorwp-bricks' ),
        'select_option' => __( 'A user submits <strong>a form</strong>', 'automatorwp-bricks' ),
        
        /* translators: %1$s: Form ID */
        'edit_label'    => sprintf( __( 'A user submits form %1$s', 'automatorwp-bricks' ), '{form}' ),
        'log_label'     => sprintf( __( 'Submitted form %1$s', 'automatorwp-bricks' ), '{form}' ),
        
        
        'action'        => 'automatorwp_bricks_form_submitted_event', 
        'priority'      => 10,
        'accepted_args' => 3, 
        
        'options'       => array(
            'form' => array(
                'name' => __( 'Form ID:', 'automatorwp-bricks' ),
                'desc' => __( 'Enter the Bricks form element ID (e.g. "brxe-abcd") or leave empty for any form.', 'automatorwp-bricks' ),
                'type' => 'text',
                'default' => '',
            ),
        ),

        'tags'          => array(
            'form_id'    => array(
                'label' => __( 'Form ID', 'automatorwp-bricks' ),
                'type'  => 'text',
            ),
            'form_fields' => array(
                'label' => __( 'Form Fields (All)', 'automatorwp-bricks' ),
                'type'  => 'text',
            ),
        ),
        
        'supports'      => array( 'automation', 'user', 'anonymous' ),
    ) );
}
add_action( 'automatorwp_init', 'automatorwp_bricks_register_form_submission_trigger' );

/**
 * Trigger event execution
 * * @param int   $user_id The user ID
 * @param string $form_id The Bricks Form ID
 * @param array  $fields  The form submitted fields
 */
function automatorwp_bricks_form_submission_event_trigger( $user_id, $form_id, $fields ) {
    
    automatorwp_trigger_event( array(
        'trigger' => 'bricks_form_submission', 
        'user_id' => $user_id,
        'fields'  => array(
            'form'    => $form_id, 
            'form_id' => $form_id, 
            'fields'  => json_encode( $fields ), 
        ),
    ) );
}
add_action( 'automatorwp_bricks_form_submitted_event', 'automatorwp_bricks_form_submission_event_trigger', 10, 3 );