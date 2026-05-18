<?php
/**
 * Plugin Name:     GamiPress - Social Proof
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-social-fake-events
 * Description:     GamiPress add-on that display FOMO events. 
 * Version:         1.0.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-social-proof
 * Domain Path:     /languages
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\gamipress-social-proof
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;


final class GamiPress_Integration_Social_Proof {

/**
 * @var   GamiPress_Integration_Social_Proof $instance The one true GamiPress_Integration_Social_Proof
 * @since 1.0.0
 */
private static $instance;

/**
 * Get active instance
 * 
 * @access public
 * @since  1.0.0
 * @return Gamipress_Integration_Social_Proof 
 */
public static function instance(){
    if(!self::$instance){
        self::$instance = new Gamipress_Integration_Social_Proof;
        self::$instance->constants();
        self::$instance->includes();
        self::$instance->hooks();
    }
    return self::$instance;
}

private function constants(){
    //Plugin version
    define('GAMIPRESS_SOCIAL_PROOF_VER','1.0.0');
    
    //Plugin file
    define('GAMIPRESS_SOCIAL_PROOF_FILE',__FILE__);
    
    //Plugin path
    define('GAMIPRESS_SOCIAL_PROOF_DIR',plugin_dir_path(__FILE__));
    
    //Plugin url
    define('GAMIPRESS_SOCIAL_PROOF_URL',plugin_dir_url(__FILE__));

    //Plugin text domain
    define('GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN','gamipress-social-proof');
}

/**
 * Include pligin files
 * 
 * @access private
 * @since  1.0.0
 * @return void
 */
private function includes(){
    if($this->meets_requirements()){
         require_once GAMIPRESS_SOCIAL_PROOF_DIR . 'includes/admin.php';
         require_once GAMIPRESS_SOCIAL_PROOF_DIR . 'includes/shortcodes.php';
         require_once GAMIPRESS_SOCIAL_PROOF_DIR . 'includes/functions.php';
         require_once GAMIPRESS_SOCIAL_PROOF_DIR . 'includes/ajax-functions.php';
         require_once GAMIPRESS_SOCIAL_PROOF_DIR . 'includes/scripts.php';

    }
}

/**
 * Setup plugin hooks
 * 
 * @access  private
 * @since   1.0.0
 * @return  void
 */
private function hooks(){
    add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
}

/**
 * Load text domain
 * 
 * @since 1.0.0
 * @return void
 */
public function load_textdomain() {
    load_plugin_textdomain(
        GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN,
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}

/**
 * Check if there are all plugin requirements
 * 
 * @since 1.0.0
 * @return bool  True if installations meets all requirements
 */
private function meets_requirements(){
    if( ! class_exists( 'GamiPress' ) )
    return false;

    return true;
}



}

/**
 * The main function responsible for active the one true GamiPress_Integration_Social_Proof
 * 
 * @since 1.0.0
 * @return \GamiPress_Integration_Social_Proof
 */
function GamiPress_Integration_Social_Proof(){
    return GamiPress_Integration_Social_Proof::instance();
}
add_action('gamipress_pre_init','GamiPress_Integration_Social_Proof');

/**
 * No detect GamiPress notice
 * 
 * @since 1.0.0
 * @return void
 */
function gamipress_social_proof_notice(){
    if( ! class_exists( 'GamiPress' ) ) { ?>
        <div class="notice notice-error">
            <p><?php _e('You need ','gamipress-social-proof')?><a href="https://gamipress.com/">GamiPress</a><?php _e(' instalation to use ( GamiPress Social Proof ).','gamipress-social-proof') ?></p> 
        </div>
<?php    }
}
add_action('admin_notices','gamipress_social_proof_notice');