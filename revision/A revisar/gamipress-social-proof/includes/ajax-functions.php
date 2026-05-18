<?php
/**
 * AJAX functions
 * 
 * @author GamiPress
 * @since  1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

    /**
     * Message sender to AJAX
     * 
     * @since 1.0.0
     * @return void 
     */
    function gamipress_social_proof_fomo_send_message_AJAX(){
        // Get message
        $message=gamipress_social_proof_fomo_general_function();
        // Return JSON
        wp_send_json([
            'message' => $message
        ]);
    }
add_action('wp_ajax_gamipress_social_proof_fomo_send_message_AJAX', 'gamipress_social_proof_fomo_send_message_AJAX');
add_action('wp_ajax_nopriv_gamipress_social_proof_fomo_send_message_AJAX', 'gamipress_social_proof_fomo_send_message_AJAX');