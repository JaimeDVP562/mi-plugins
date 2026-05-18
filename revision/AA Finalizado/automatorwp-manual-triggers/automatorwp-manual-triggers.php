<?php
/**
 * Plugin Name:           AutomatorWP - Manual Triggers
 * Plugin URI:            https://automatorwp.com/add-ons/manual-triggers/
 * Description:           Connect AutomatorWP with Manual Triggers. Launch automations manually via code, shortcodes or the admin panel.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-manual-triggers
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Manual_Triggers
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Manual_Triggers
{

    /**
     * @var         AutomatorWP_Manual_Triggers $instance The one true AutomatorWP_Manual_Triggers
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Manual_Triggers self::$instance The one true AutomatorWP_Manual_Triggers
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new AutomatorWP_Manual_Triggers();
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
        define('AUTOMATORWP_MANUAL_TRIGGERS_VER', '1.0.0');

        // Plugin file
        define('AUTOMATORWP_MANUAL_TRIGGERS_FILE', __FILE__);

        // Plugin path
        define('AUTOMATORWP_MANUAL_TRIGGERS_DIR', plugin_dir_path(__FILE__));

        // Plugin URL
        define('AUTOMATORWP_MANUAL_TRIGGERS_URL', plugin_dir_url(__FILE__));
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
            require_once AUTOMATORWP_MANUAL_TRIGGERS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_MANUAL_TRIGGERS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_MANUAL_TRIGGERS_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_MANUAL_TRIGGERS_DIR . 'includes/shortcodes.php';

            // Triggers
            require_once AUTOMATORWP_MANUAL_TRIGGERS_DIR . 'includes/triggers/manual-launch.php';

            // Anonymous Triggers
            require_once AUTOMATORWP_MANUAL_TRIGGERS_DIR . 'includes/triggers/anonymous-manual-launch.php';

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

        automatorwp_register_integration('manual_triggers', array(
            'label' => 'Manual Triggers',
            'icon'  => AUTOMATORWP_MANUAL_TRIGGERS_URL . 'assets/manual-triggers.svg',
        ));

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

        $meta_boxes['automatorwp-manual-triggers-license'] = array(
            'title' => 'Manual Triggers',
            'fields' => array(
                'automatorwp_manual_triggers_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_MANUAL_TRIGGERS_FILE,
                    'item_name' => 'Manual Triggers',
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
                        __('AutomatorWP - Manual Triggers requires %s in order to work. Please install and activate it.', 'automatorwp-manual-triggers'),
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
        $lang_dir = AUTOMATORWP_MANUAL_TRIGGERS_DIR . '/languages/';
        $lang_dir = apply_filters('automatorwp_manual_triggers_languages_directory', $lang_dir);

        // Traditional WordPress plugin locale filter
        $locale = apply_filters('plugin_locale', get_locale(), 'automatorwp-manual-triggers');
        $mofile = sprintf('%1$s-%2$s.mo', 'automatorwp-manual-triggers', $locale);

        // Setup paths to current locale file
        $mofile_local = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-manual-triggers/' . $mofile;

        if (file_exists($mofile_global)) {
            // Look in global /wp-content/languages/automatorwp-manual-triggers/ folder
            load_textdomain('automatorwp-manual-triggers', $mofile_global);
        } elseif (file_exists($mofile_local)) {
            // Look in local /wp-content/plugins/automatorwp-manual-triggers/languages/ folder
            load_textdomain('automatorwp-manual-triggers', $mofile_local);
        } else {
            // Load the default language files
            load_plugin_textdomain('automatorwp-manual-triggers', false, $lang_dir);
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Manual_Triggers instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Manual_Triggers The one true AutomatorWP_Manual_Triggers
 */
function AutomatorWP_Manual_Triggers()
{
    return AutomatorWP_Manual_Triggers::instance();
}
add_action('plugins_loaded', 'AutomatorWP_Manual_Triggers');
