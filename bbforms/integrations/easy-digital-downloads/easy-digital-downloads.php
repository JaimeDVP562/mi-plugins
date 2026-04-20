<?php
final class BBForms_Integration_Easy_Digital_Downloads {

    /**
     * @var         BBForms_Integration_Easy_Digital_Downloads $instance The one true instance
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      BBForms_Integration_Easy_Digital_Downloads self::$instance The one true instance
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new BBForms_Integration_Easy_Digital_Downloads();

            if( ! self::$instance->pro_installed() ) {

                self::$instance->constants();
                self::$instance->includes();

            }

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
        define( 'BBFORMS_EASY_DIGITAL_DOWNLOADS_VER', '1.0.0' );

        // Plugin file
        define( 'BBFORMS_EASY_DIGITAL_DOWNLOADS_FILE', __FILE__ );

        // Plugin path
        define( 'BBFORMS_EASY_DIGITAL_DOWNLOADS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'BBFORMS_EASY_DIGITAL_DOWNLOADS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            // Triggers
            require_once BBFORMS_EASY_DIGITAL_DOWNLOADS_DIR . 'includes/tags.php';

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

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
            return false;
        }

        return true;

    }

    /**
     * Check if the pro version of this integration is installed
     *
     * @since  1.0.0
     *
     * @return bool True if pro version installed
     */
    private function pro_installed() {

        if ( ! class_exists( 'BBForms_Easy_Digital_Downloads' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true BBForms_Integration_Easy_Digital_Downloads instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \BBForms_Integration_Easy_Digital_Downloads The one true BBForms_Integration_Easy_Digital_Downloads
 */
function BBForms_Integration_Easy_Digital_Downloads() {
    return BBForms_Integration_Easy_Digital_Downloads::instance();
}
add_action( 'bbforms_pre_init', 'BBForms_Integration_Easy_Digital_Downloads' );
