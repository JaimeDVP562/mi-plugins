<?php
/**
 * User Completes Quiz Trigger
 *
 * @package     AutomatorWP\Integrations\PressPrimer\Triggers\User_Completes_Quiz
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_PressPrimer_User_Completes_Quiz extends AutomatorWP_Integration_Trigger {

    /**
     * Integration identifier
     * @var string
     */
    public $integration = 'pressprimer';

    /**
     * Trigger identifier
     * @var string
     */
    public $trigger = 'pressprimer_user_completes_quiz';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     * @return void
     */
    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A user completes a quiz', 'automatorwp-pressprimer' ),
            'select_option' => __( 'A user completes a <strong>quiz</strong>', 'automatorwp-pressprimer' ),
            /* translators: %1$s: Quiz. */
            'edit_label'    => sprintf( __( 'A user completes %1$s', 'automatorwp-pressprimer' ), '{quiz}' ),
            /* translators: %1$s: Quiz. */
            'log_label'     => sprintf( __( 'A user completes %1$s', 'automatorwp-pressprimer' ), '{quiz}' ),
            'options'       => array(
                
                // Strict 3-level structure for UI rendering
                'quiz' => array(
                    'from'    => 'quiz',
                    'default' => __( 'any quiz', 'automatorwp-pressprimer' ),
                    'fields'  => array(
                        'quiz' => array(
                            'name'    => __( 'Quiz:', 'automatorwp-pressprimer' ),
                            'type'    => 'select',
                            'default' => 'any',
                            'options' => $this->get_quizzes(),
                        ),
                    ),
                ),

            ),
        ) );
    }

    /**
     * Get all quizzes for the select option
     *
     * @since 1.0.0
     * @return array
     */
    public function get_quizzes() {
        global $wpdb;

        $options = array(
            'any' => __( 'Any quiz', 'automatorwp-pressprimer' )
        );

        $table = $wpdb->prefix . 'ppq_quizzes';

        // Check if table exists to prevent errors
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) != $table ) {
            return $options;
        }

        // Fetch published quizzes directly from the custom database table
        $quizzes = $wpdb->get_results( "SELECT id, title FROM {$table} WHERE status = 'published' ORDER BY title ASC" );

        if ( ! empty( $quizzes ) ) {
            foreach ( $quizzes as $quiz ) {
                $options[ (string) $quiz->id ] = $quiz->title;
            }
        }

        return $options;
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     * @return void
     */
    public function hooks() {
        add_action( 'pressprimer_quiz_quiz_completed', array( $this, 'listener' ), 10, 2 );
    }

    /**
     * Trigger listener function
     *
     * @since 1.0.0
     * @param PressPrimer_Quiz_Attempt $attempt The attempt object.
     * @param PressPrimer_Quiz_Quiz    $quiz    The quiz object.
     * @return void
     */
    public function listener( $attempt, $quiz ) {
        
        // Bail if attempt has no user (AutomatorWP requires registered users).
        if ( empty( $attempt->user_id ) ) {
            return;
        }

        $user_id = absint( $attempt->user_id );
        $quiz_id = absint( $quiz->id );

        // Fire the trigger in AutomatorWP.
        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'options' => array(
                'quiz' => $quiz_id, // This key must match the 'from' key in the UI dropdown.
            ),
            'tags' => array(
                'quiz_name'  => sanitize_text_field( $quiz->title ),
                'quiz_score' => sanitize_text_field( $attempt->score ),
            ),
        ) );
    }
}

new AutomatorWP_PressPrimer_User_Completes_Quiz();