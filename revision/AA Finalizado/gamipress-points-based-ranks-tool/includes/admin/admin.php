<?php
/**
 * Admin
 *
 * @package GamiPress\Points_Based_Ranks\Admin
 *
 * This file handles the admin interface for the Points-Based Ranks Tool.
 * Registers meta boxes, enqueues assets, and manages the tool's admin UI.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Admin includes
require_once GPRBT_PLUGIN_DIR . 'includes/admin/tools/points-based-ranks.php';

/**
 * GPRBT_Admin Class
 *
 * Main admin controller class that handles all admin-side functionality
 * including asset loading, meta box registration, and footer output.
 *
 * @since 1.0.0
 */
class GPRBT_Admin {

    /**
     * Singleton instance.
     *
     * @var GPRBT_Admin
     */
    private static $instance;

    /**
     * Get singleton instance.
     *
     * @since 1.0.0
     *
     * @return GPRBT_Admin Singleton instance.
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks.
     *
     * Sets up action hooks for meta boxes, assets, and footer output.
     *
     * @since 1.0.0
     */
    private function hooks() {
        add_filter( 'gamipress_tools_general_meta_boxes', array( $this, 'register_tools_meta_boxes' ) );
        add_filter( 'gamipress_tools_page_bottom', array( $this, 'print_tools_footer' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Register the tool meta box with GamiPress tools.
     *
     * @since 1.0.0
     *
     * @param array $meta_boxes Existing meta boxes array.
     * @return array Modified meta boxes array with our tool added.
     */
    public function register_tools_meta_boxes( $meta_boxes ) {
        return gamipress_points_based_ranks_tool_meta_boxes( $meta_boxes );
    }

    /**
     * Enqueue admin JavaScript and localize data.
     *
     * Loads the admin JavaScript file and passes PHP data to JavaScript
     * via wp_localize_script for internationalization and AJAX functionality.
     *
     * @since 1.0.0
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'gamipress_page_gamipress_tools' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'gamipress-fabric-js' );

        wp_enqueue_script(
            'gprbt-admin-tools-js',
            GPRBT_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'gamipress-fabric-js' ),
            GPRBT_PLUGIN_VERSION,
            true
        );

        wp_localize_script( 'gprbt-admin-tools-js', 'gprbt_data', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => gamipress_get_admin_nonce(),

        ) );
    }

    /**
     * Print hidden canvas elements for badge builder.
     *
     * Outputs hidden canvas elements needed for the badge image generation
     * functionality. Only outputs on the GamiPress Tools page.
     *
     * @since 1.0.0
     */
    public function print_tools_footer() {
        $screen = get_current_screen();

        if ( ! $screen || 'gamipress_page_gamipress_tools' !== $screen->id ) {
            return;
        }

        echo '<div class="gamipress-badge-builder" style="display:none;">';
        echo '<canvas id="gamipress-badge-builder-canvas" width="600" height="600"></canvas>';
        echo '<canvas id="gamipress-badge-builder-canvas-backup" width="600" height="600"></canvas>';
        echo '</div>';
    }
}
