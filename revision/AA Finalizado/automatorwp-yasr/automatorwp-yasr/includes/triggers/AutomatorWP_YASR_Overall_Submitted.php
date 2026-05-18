<?php
/**
 * Trigger for YASR (Yet Another Stars Rating), sends an email to the user
 * after submitting a rating.
 * 
 * @package     AutomatorWP\Integrations\YASR
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_YASR_Overall_Submitted extends AutomatorWP_Integration_Trigger {

    public $integration = 'yasr';
    public $trigger = 'yasr_overall_rating_submitted';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger($this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __('An overall rating is loaded', 'automatorwp-yasr'),
            'select_option' => __('An overall rating is loaded', 'automatorwp-yasr'),
            'edit_label'    => __('An overall rating is loaded', 'automatorwp-yasr'),
            'log_label'     => __('An overall rating is loaded', 'automatorwp-yasr'),
            'action'        => 'yasr_action_on_overall_rating', 
            'function'      => array($this, 'listener'),
            'priority'      => 10,
            'accepted_args' => 2,
            
        ));
    }

    /**
     * Listener for the trigger
     *
     * @since 1.0.0
     *
     * @param int    $post_id    ID of the post being rated
     * @param float $rating       The rating value
     */
    public function listener($post_id, $rating) {
        // Obtiene el ID del usuario que envió la calificación
        $user_id = get_current_user_id();

        // Obtiene el correo electrónico del usuario
        $user_email = get_userdata($user_id)->user_email;

        // Prepara el contenido del correo electrónico
        $subject = 'Your calification has been received';
        $message = 'Thank you for submitting your rating. Your rating has been received successfully.';

        // Envía el correo electrónico
        wp_mail($user_email, $subject, $message);

        //log
        
        error_log('Correo electrónico enviado al usuario con ID: ' . $user_id);

              // Trigger the event
        automatorwp_trigger_event(array(
                'trigger'     => $this->trigger,
                'user_id'     => $user_id,
                'rating'      => $rating,
                'post_id'  => $post_id
            ));
    }

    /**
 * User deserves check
 *
 * @since 1.0.0
 *
 * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
 * @param stdClass  $trigger            The trigger object
 * @param int       $user_id            The user ID
 * @param array     $event              Event information
 * @param array     $trigger_options    The trigger's stored options
 * @param stdClass  $automation         The trigger's automation object
 *
 * @return bool                          True if user deserves trigger, false otherwise
 */

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        // Verificar si el evento tiene un comment_id
        if( ! isset( $event['post_id'] ) ) {
            error_log('No hemos podido encontrar el post_id en el evento');
            return false;
        }
    

    
        return $deserves_trigger;
    }


}

// Registra el trigger
new AutomatorWP_YASR_Overall_Submitted();
