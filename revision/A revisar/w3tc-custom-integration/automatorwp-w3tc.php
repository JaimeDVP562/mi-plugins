<?php
/**
 * Plugin Name:           AutomatorWP - W3 Total Cache
 * Plugin URI:            https://automatorwp.com/add-ons/w3-total-cache/
 * Description:           Connect AutomatorWP with W3 Total Cache. Purge caches automatically through automations.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-w3tc
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\W3TC
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_W3TC
{

    /**
     * @var         AutomatorWP_W3TC $instance The one true AutomatorWP_W3TC
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_W3TC self::$instance The one true AutomatorWP_W3TC
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new AutomatorWP_W3TC();
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
    private function constants()
    {
        // Plugin version
        define('AUTOMATORWP_W3TC_VER', '1.0.0');

        // Plugin file
        define('AUTOMATORWP_W3TC_FILE', __FILE__);

        // Plugin path
        define('AUTOMATORWP_W3TC_DIR', plugin_dir_path(__FILE__));

        // Plugin URL
        define('AUTOMATORWP_W3TC_URL', plugin_dir_url(__FILE__));
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
            require_once AUTOMATORWP_W3TC_DIR . 'includes/functions.php';

            // Actions
            require_once AUTOMATORWP_W3TC_DIR . 'includes/actions/purge-all.php';
            require_once AUTOMATORWP_W3TC_DIR . 'includes/actions/purge-post.php';
            require_once AUTOMATORWP_W3TC_DIR . 'includes/actions/purge-url.php';
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

        automatorwp_register_integration(
            'w3_total_cache',
            array(
                'label' => 'W3 Total Cache',
                'icon' => AUTOMATORWP_W3TC_URL . 'assets/w3-total-cache.svg',
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

        $meta_boxes['automatorwp-w3tc-license'] = array(
            'title' => 'W3 Total Cache',
            'fields' => array(
                'automatorwp_w3tc_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_W3TC_FILE,
                    'item_name' => 'W3 Total Cache',
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
                        __('AutomatorWP - W3 Total Cache requires %s and %s in order to work. Please install and activate them.', 'automatorwp-w3tc'),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/w3-total-cache/" target="_blank">W3 Total Cache</a>'
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

        // Check if W3 Total Cache is active
        if (!function_exists('w3tc_flush_all')) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_W3TC instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_W3TC The one true AutomatorWP_W3TC
 */
function AutomatorWP_W3TC()
{
    return AutomatorWP_W3TC::instance();
}
add_action('plugins_loaded', 'AutomatorWP_W3TC');
