<?php
/**
 * Plugin Name:           AutomatorWP - WP Options
 * Plugin URI:            https://automatorwp.com/add-ons/wp-options/
 * Description:           Connect AutomatorWP with WordPress Options. Add actions to modify options and tags to get option values.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-wp-options
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\WP_Options
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_WP_Options
{
    /**
     * @var         AutomatorWP_WP_Options $instance The one true AutomatorWP_WP_Options
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_WP_Options self::$instance The one true AutomatorWP_WP_Options
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new AutomatorWP_WP_Options();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
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
    private function constants()
    {
        // Plugin version
        define('AUTOMATORWP_WP_OPTIONS_VER', '1.0.0');

        // Plugin file
        define('AUTOMATORWP_WP_OPTIONS_FILE', __FILE__);

        // Plugin path
        define('AUTOMATORWP_WP_OPTIONS_DIR', plugin_dir_path(__FILE__));

        // Plugin URL
        define('AUTOMATORWP_WP_OPTIONS_URL', plugin_dir_url(__FILE__));
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    public function includes()
    {

        if ($this->meets_requirements()) {

            // Includes
            require_once AUTOMATORWP_WP_OPTIONS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_WP_OPTIONS_DIR . 'includes/tags.php';

            // Actions
            require_once AUTOMATORWP_WP_OPTIONS_DIR . 'includes/actions/update-option.php';
            require_once AUTOMATORWP_WP_OPTIONS_DIR . 'includes/actions/delete-option.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks()
    {
         $this->register_integration();

    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration()
    {

        automatorwp_register_integration('wp_options', array(
            'label' => 'WP Options',
            'icon'  => AUTOMATORWP_WP_OPTIONS_URL . 'assets/wp-options.svg',
        ));

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices()
    {

        if (!$this->meets_requirements() && !defined('AUTOMATORWP_ADMIN_NOTICES')): ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __('AutomatorWP - WP Options requires %s in order to work. Please install and activate it.', 'automatorwp-wp-options'),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
                    ); ?>
                </p>
            </div>

            <?php define('AUTOMATORWP_ADMIN_NOTICES', true); ?>

        <?php endif;

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements()
    {

        if (!class_exists('AutomatorWP')) {
            return false;
        }

        return true;

    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain()
    {

        // Set filter for language directory
        $lang_dir = AUTOMATORWP_WP_OPTIONS_DIR . '/languages/';
        $lang_dir = apply_filters('automatorwp_wp_options_languages_directory', $lang_dir);

        // Traditional WordPress plugin locale filter
        $locale = apply_filters('plugin_locale', get_locale(), 'automatorwp-wp-options');
        $mofile = sprintf('%1$s-%2$s.mo', 'automatorwp-wp-options', $locale);

        // Setup paths to current locale file
        $mofile_local = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-wp-options/' . $mofile;

        if (file_exists($mofile_global)) {
            // Look in global /wp-content/languages/automatorwp-wp-options/ folder
            load_textdomain('automatorwp-wp-options', $mofile_global);
        } elseif (file_exists($mofile_local)) {
            // Look in local /wp-content/plugins/automatorwp-wp-options/languages/ folder
            load_textdomain('automatorwp-wp-options', $mofile_local);
        } else {
            // Load the default language files
            load_plugin_textdomain('automatorwp-wp-options', false, $lang_dir);
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_WP_Options instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_WP_Options The one true AutomatorWP_WP_Options
 */
function AutomatorWP_WP_Options()
{
    return AutomatorWP_WP_Options::instance();
}

add_action('automatorwp_init', 'AutomatorWP_WP_Options', 20);
