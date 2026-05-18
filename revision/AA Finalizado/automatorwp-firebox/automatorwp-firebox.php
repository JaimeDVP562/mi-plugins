<?php
/**
 * Plugin Name:           AutomatorWP - FireBox
 * Plugin URI:            https://automatorwp.com/add-ons/firebox/
 * Description:           Connect AutomatorWP with FireBox.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-firebox
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\FireBox
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_FireBox
{

    /**
     * @var         AutomatorWP_FireBox $instance The one true AutomatorWP_FireBox
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_FireBox self::$instance The one true AutomatorWP_FireBox
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new AutomatorWP_FireBox();
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
        define('AUTOMATORWP_FIREBOX_VER', '1.0.0');

        // Plugin file
        define('AUTOMATORWP_FIREBOX_FILE', __FILE__);

        // Plugin path
        define('AUTOMATORWP_FIREBOX_DIR', plugin_dir_path(__FILE__));

        // Plugin URL
        define('AUTOMATORWP_FIREBOX_URL', plugin_dir_url(__FILE__));
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes()
    {

        if ($this->meets_requirements()) {

            // Includes
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/scripts.php';

            // Triggers — logged-in users
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/open-event.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/close-event.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/conversion.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/successful-form-submission.php';

            // Triggers — anonymous (guests)
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/anonymous-open-event.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/anonymous-close-event.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/anonymous-conversion.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/triggers/anonymous-successful-form-submission.php';

            // Actions
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/actions/enable-popup.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/actions/disable-popup.php';
            require_once AUTOMATORWP_FIREBOX_DIR . 'includes/actions/show-popup.php';

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

        add_action('automatorwp_init', array($this, 'register_integration'));

        add_filter('automatorwp_licenses_meta_boxes', array($this, 'license'));

        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration()
    {

        automatorwp_register_integration('firebox', array(
            'label' => 'FireBox',
            'icon' => AUTOMATORWP_FIREBOX_URL . 'assets/firebox.svg',
        )
        );

    }

    /**
     * Licensing
     *
     * @since 1.0.0
     *
     * @param array $meta_boxes
     *
     * @return array
     */
    function license($meta_boxes)
    {

        $meta_boxes['automatorwp-firebox-license'] = array(
            'title' => 'FireBox',
            'fields' => array(
                'automatorwp_firebox_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_FIREBOX_FILE,
                    'item_name' => 'FireBox',
                ),
            )
        );

        return $meta_boxes;

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
                        __('AutomatorWP - FireBox requires %s and %s in order to work. Please install and activate them.', 'automatorwp-firebox'),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/firebox/" target="_blank">FireBox</a>'
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

        // FBOX_VERSION was removed in FireBox 3.x; skip that constant check.

        // Verify FireBox 3.x is active by checking its CPT registration class.
        if (!class_exists('FireBox\Core\Admin\Includes\Cpts\Firebox')) {
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
        $lang_dir = AUTOMATORWP_FIREBOX_DIR . '/languages/';
        $lang_dir = apply_filters('automatorwp_firebox_languages_directory', $lang_dir);

        // Traditional WordPress plugin locale filter
        $locale = apply_filters('plugin_locale', get_locale(), 'automatorwp-firebox');
        $mofile = sprintf('%1$s-%2$s.mo', 'automatorwp-firebox', $locale);

        // Setup paths to current locale file
        $mofile_local = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-firebox/' . $mofile;

        if (file_exists($mofile_global)) {
            // Look in global /wp-content/languages/automatorwp-firebox/ folder
            load_textdomain('automatorwp-firebox', $mofile_global);
        } elseif (file_exists($mofile_local)) {
            // Look in local /wp-content/plugins/automatorwp-firebox/languages/ folder
            load_textdomain('automatorwp-firebox', $mofile_local);
        } else {
            // Load the default language files
            load_plugin_textdomain('automatorwp-firebox', false, $lang_dir);
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_FireBox instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_FireBox The one true AutomatorWP_FireBox
 */
function AutomatorWP_FireBox()
{
    return AutomatorWP_FireBox::instance();
}
add_action('plugins_loaded', 'AutomatorWP_FireBox');
