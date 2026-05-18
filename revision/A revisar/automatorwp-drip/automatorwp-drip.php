<?php
/**
 * Plugin Name:           AutomatorWP - Drip
 * Plugin URI:            https://wordpress.org/plugins/automatorwp-drip/
 * Description:           Connect AutomatorWP with Drip.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-drip
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Drip
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_Drip {

    /**
     * @var         AutomatorWP_Integration_Drip $instance The one true AutomatorWP_Integration_Drip
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_Drip self::$instance
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Drip();
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
        define( 'AUTOMATORWP_DRIP_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_DRIP_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_DRIP_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_DRIP_URL', plugin_dir_url( __FILE__ ) );

    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if ( ! $this->meets_requirements() ) {
            return;
        }

        // Core
        require_once AUTOMATORWP_DRIP_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/scripts.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/rest-api.php';

        // Triggers — subscriber lifecycle
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/subscriber-created.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/subscriber-deleted.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/subscriber-reactivated.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/subscribed-to-email-marketing.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/unsubscribed-all.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/updated-alias.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/updated-email-address.php';

        // Triggers — tags
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/applied-tag.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/removed-tag.php';

        // Triggers — email engagement
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/received-email.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/opened-email.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/clicked-email.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/clicked-trigger-link.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/bounced.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/complained.php';

        // Triggers — campaigns
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/subscribed-to-campaign.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/completed-campaign.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/unsubscribed-from-campaign-trigger.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/removed-from-campaign.php';

        // Triggers — data changes
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/updated-custom-field.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/updated-lifetime-value.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/updated-time-zone.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/updated-lead-score.php';

        // Triggers — behavioral
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/performed-custom-event.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/visited-page.php';

        // Triggers — lead scoring / deliverability
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/became-lead.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/became-non-prospect.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/marked-as-deliverable.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/triggers/marked-as-undeliverable.php';

        // Actions — subscriber management
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/create-update-subscriber.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/remove-subscriber.php';

        // Actions — tags
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/add-tag-to-subscriber.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/remove-tag-from-subscriber.php';

        // Actions — campaigns
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/add-subscriber-to-campaign.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/unsubscribe-from-campaign.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/unsubscribe-all.php';

        // Actions — events & workflows
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/record-event.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/enroll-workflow.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/remove-from-workflow.php';

        // Actions — shopper activity
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/create-update-order.php';
        require_once AUTOMATORWP_DRIP_DIR . 'includes/actions/create-update-cart.php';

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
     * Registers this integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration() {

        automatorwp_register_integration( 'drip', array(
            'label' => 'Drip',
            'icon'  => AUTOMATORWP_DRIP_URL . 'assets/drip-icon-64.png',
        ) );

    }

    /**
     * Check plugin requirements
     *
     * @since 1.0.0
     *
     * @return bool
     */
    private function meets_requirements() {

        return class_exists( 'AutomatorWP' );

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_Drip instance
 *
 * @since       1.0.0
 * @return      AutomatorWP_Integration_Drip
 */
function AutomatorWP_Drip() {

    return AutomatorWP_Integration_Drip::instance();

}
add_action( 'plugins_loaded', 'AutomatorWP_Drip' );
