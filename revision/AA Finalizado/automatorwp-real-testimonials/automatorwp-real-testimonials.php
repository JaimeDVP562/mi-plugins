<?php
/**
 * Plugin Name:           AutomatorWP - WP Real Testimonials integration
 * Description:           Integración de AutomatorWP con Real Testimonials.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-real-testimonials
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\RealTestimonials
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_RealTestimonials {

    /**
     * @var         AutomatorWP_Integration_RealTestimonials $instance The one true AutomatorWP_Integration_RealTestimonials
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_RealTestimonials self::$instance The one true AutomatorWP_Integration_RealTestimonials
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Integration_RealTestimonials();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_REAL_TESTIMONIALS_VERSION', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_REALTESTIMONIALS', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_REALTESTIMONIALS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_REALTESTIMONIALS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( class_exists( 'AutomatorWP' ) && ! $this->pro_installed() ) {

            // Triggers
            require_once AUTOMATORWP_REALTESTIMONIALS_DIR . 'includes/triggers/submit-testimonial.php';

            // Includes
            require_once AUTOMATORWP_REALTESTIMONIALS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_REALTESTIMONIALS_DIR . 'includes/functions.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'RealTestimonials', array(
            'label' => 'WP Real Testimonials',
            /*'icon'  => plugin_dir_url( __FILE__ ) . 'assets/RealTestimonials.svg',*/
        ) );

    }

    /**
     * Check if the pro version of this integration is installed
     *
     * @since  1.0.0
     *
     * @return bool True if pro version installed
     */
    private function pro_installed() {
        return class_exists( 'AutomatorWP_RealTestimonials_Pro' );
    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_RealTestimonials instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Integration_RealTestimonials The one true AutomatorWP_Integration_RealTestimonials
 */
function AutomatorWP_Integration_RealTestimonials() {
    return AutomatorWP_Integration_RealTestimonials::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Integration_RealTestimonials' );
