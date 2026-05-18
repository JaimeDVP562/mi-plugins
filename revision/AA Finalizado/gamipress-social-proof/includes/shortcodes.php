<?php
/**
 * ShortCodes
 * 
 * @author GamiPress
 * @since  1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Create FOMO ShortCode [FOMO]
 * 
 * @since 1.0.0
 * @return string HTML
 */
function gamipress_social_proof_create_fomo_shortcode(){
    return "<div class='container-message' id='message-container'></div>";
}
add_shortcode('FOMO','gamipress_social_proof_create_fomo_shortcode');





   

    

   
    