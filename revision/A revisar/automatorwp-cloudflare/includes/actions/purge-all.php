<?php
/**
 * Action: Purge All Cloudflare Cache
 *
 * Removes every cached asset from the Cloudflare CDN for the configured zone.
 * Useful after bulk content updates, theme changes, or plugin upgrades.
 *
 * @package     AutomatorWP\Cloudflare\Actions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class AutomatorWP_Cloudflare_Purge_All
 *
 * @since 1.0.0
 */
class AutomatorWP_Cloudflare_Purge_All extends AutomatorWP_Integration_Action {

    /**
     * @since 1.0.0
     * @var   string $integration
     */
    public $integration = 'cloudflare';

    /**
     * @since 1.0.0
     * @var   string $action
     */
    public $action = 'cloudflare_purge_all';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge all Cloudflare cache', 'automatorwp-cloudflare' ),
            'select_option' => __( 'Purge <strong>all</strong> Cloudflare cache', 'automatorwp-cloudflare' ),
            'edit_label'    => __( 'Purge all Cloudflare cache for the configured zone', 'automatorwp-cloudflare' ),
            'logged_in'     => false, // Works for anonymous automations too
            'options'       => array(), // No extra fields needed for this action
        ) );
    }

    /**
     * Execute the action
     *
     * @since 1.0.0
     *
     * @param stdClass  $action         The action object.
     * @param int       $user_id        The user ID.
     * @param array     $action_options The action's stored options (tags already replaced).
     * @param stdClass  $automation     The parent automation object.
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Verify credentials before attempting the API call
        if ( ! automatorwp_cloudflare_has_credentials() ) {
            automatorwp_cloudflare_log_result(
                array(
                    'success' => false,
                    'status'  => 0,
                    'data'    => null,
                    'error'   => __( 'Cloudflare API Token or Zone ID is missing. Please check AutomatorWP → Settings → Cloudflare.', 'automatorwp-cloudflare' ),
                ),
                $action,
                $user_id,
                $automation
            );
            return;
        }

        // Purge everything in the zone
        $result = automatorwp_cloudflare_purge_everything();

        // Log result to AutomatorWP activity log
        automatorwp_cloudflare_log_result( $result, $action, $user_id, $automation );
    }
}

// Boot the action
new AutomatorWP_Cloudflare_Purge_All();
