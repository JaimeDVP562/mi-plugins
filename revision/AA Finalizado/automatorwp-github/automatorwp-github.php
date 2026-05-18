<?php
/**
 * Plugin Name:           AutomatorWP - GitHub
 * Plugin URI:            https://automatorwp.com/add-ons/github/
 * Description:           Connect AutomatorWP with Github.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-github
 * Domain Path:           languages/
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\GitHub
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Github{


/**
 * @var    AutomatorWP_Github $instance The one true AutomatorWP_Github
 * @since  1.0.0
 */
private static $instance;

/**
 * Get active instance
 * 
 * @access public
 * @since  1.0.0
 * @return AutomatorWP_Github self::$instance The one true AutomatorWP_Github
 */
public static function instance(){

    if(!self::$instance){
        self::$instance = new AutomatorWP_Github();
        self::$instance->constants();
        self::$instance->includes();
        self::$instance->hooks();
    }

    return self::$instance;

}

/**
 * Setup plugins constants
 * 
 * @access private
 * @since 1.0.0
 * @return void
 */
private function constants(){

    //Plugin version
    define('AUTOMATORWP_GITHUB_VER','1.0.0');

    //Plugin file
    define('AUTOMATORWP_GITHUB_FILE',__FILE__);

    //Plugin path
    define('AUTOMATORWP_GITHUB_DIR',plugin_dir_path(__FILE__));

    //Plugin url
    define('AUTOMATORWP_GITHUB_URL',plugin_dir_url(__FILE__));

    //Text domain
    define('AUTOMATORWP_GITHUB_TEXT_DOMAIN','automatorwp-github');

    //API url
    define('AUTOMATORWP_GITHUB_API','https://api.github.com');

    //API Timeout
    define('AUTOMATORWP_GITHUB_API_TIMEOUT',15);



}


/**
 * Instance includes function
 * 
 * @access private
 * @since   1.0.0
 * @return  void
 */
private function includes(){
    if($this->meets_requirements()){

        require_once AUTOMATORWP_GITHUB_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_GITHUB_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_GITHUB_DIR . 'includes/scripts.php';
        require_once AUTOMATORWP_GITHUB_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_GITHUB_DIR . 'includes/rest-api.php';
       

         
        // Load triggers dynamically
            $this->load_files_from_directory('includes/triggers');

            // Load actions dynamically
            $this->load_files_from_directory('includes/actions');
    

    }
}


/**
 * Dinamic load function
 * 
 * @access  private
 * @since   1.0.0
 * @param   string $dir_path Create the correct path in each case
 * @return  void
 */
private function load_files_from_directory($directory)
    {
        $dir_path = AUTOMATORWP_GITHUB_DIR . $directory;

        if (!is_dir($dir_path)) {
            return;
        }

        $files = glob($dir_path . '/*.php');

        if (is_array($files)) {
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }

/**
 * Setup plugin hooks
 * 
 * @access  private
 * @since   1.0.0
 * @return  void
 */
private function hooks() {
    
    add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
}


/**
 * Registers this integration
 * 
 * @since   1.0.0
 * @return  void
 */
public function register_integration() {
    automatorwp_register_integration( 'github', array(
        'label' => __( 'GitHub', 'automatorwp-github' ),
        'icon'  => AUTOMATORWP_GITHUB_URL . 'assets/images/github.svg',
    ) );
}




/**
 * Check AutomatorWP exists
 * 
 * @since  1.0.0
 * @return bool True if installations meets all requirements
 */
private function meets_requirements(){
    if(class_exists('AutomatorWP')){
        return true;
    }
    return false;
}

}



/**
 * The main function responsible for active the one true AutomatorWP_Github instance to functions everywhere
 * 
 * @since 1.0.0
 * @return \AutomatorWP_Github the one true Automator_Github
 */
function AutomatorWP_Github(){
    return AutomatorWP_Github::instance();
}
add_action('plugins_loaded','AutomatorWP_Github');
