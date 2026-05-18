<?php
/**
 * Action: Purge Cloudflare Cache for a Specific Post
 *
 * @package     AutomatorWP\Cloudflare\Actions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cloudflare_Purge_Post extends AutomatorWP_Integration_Action {

    public $integration = 'cloudflare';
    public $action      = 'cloudflare_purge_post';

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge Cloudflare cache for a post', 'automatorwp-cloudflare' ),
            'select_option' => __( 'Purge Cloudflare cache for a <strong>specific post</strong>', 'automatorwp-cloudflare' ),
            'edit_label'    => __( 'Purge Cloudflare cache for post ID {post_id}', 'automatorwp-cloudflare' ),
            'logged_in'     => false,
            'options'       => array(
                'post_id' => array(
                    'from'   => 'post_id',
                    'fields' => array(
                        'post_id' => array(
                            'name'    => __( 'Post ID:', 'automatorwp-cloudflare' ),
                            'desc'    => __( 'The ID of the post to purge. Supports AutomatorWP tags (e.g. {post:ID}).', 'automatorwp-cloudflare' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        if ( ! automatorwp_cloudflare_has_credentials() ) {
            automatorwp_cloudflare_log_result( array(
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => __( 'Cloudflare API Token or Zone ID is missing.', 'automatorwp-cloudflare' ),
            ), $action, $user_id, $automation );
            return;
        }

        $raw_post_id = isset( $action->options->post_id ) ? $action->options->post_id : '';
        $post_id     = isset( $action_options['post_id'] ) ? $action_options['post_id'] : '';

        // Anti-Numeric Patch
        if ( is_numeric( $post_id ) && ! is_numeric( $raw_post_id ) ) {
            $post_id = $raw_post_id;
        }

        $post_id = absint( $post_id );

        if ( empty( $post_id ) ) {
            automatorwp_cloudflare_log_result( array(
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => __( 'No Post ID provided.', 'automatorwp-cloudflare' ),
            ), $action, $user_id, $automation );
            return;
        }

        $post = get_post( $post_id );

        if ( ! $post || $post instanceof WP_Error ) {
            automatorwp_cloudflare_log_result( array(
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => sprintf( __( 'Post ID %d does not exist.', 'automatorwp-cloudflare' ), $post_id ),
            ), $action, $user_id, $automation );
            return;
        }

        $post_url = get_permalink( $post_id );

        if ( empty( $post_url ) || ! filter_var( $post_url, FILTER_VALIDATE_URL ) ) {
            automatorwp_cloudflare_log_result( array(
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => sprintf( __( 'Could not resolve permalink for post ID %d.', 'automatorwp-cloudflare' ), $post_id ),
            ), $action, $user_id, $automation );
            return;
        }

        $urls = array_unique( array(
            $post_url,
            trailingslashit( $post_url ),
            untrailingslashit( $post_url ),
        ) );

        $result = automatorwp_cloudflare_purge_urls( $urls );
        automatorwp_cloudflare_log_result( $result, $action, $user_id, $automation );
    }
}

new AutomatorWP_Cloudflare_Purge_Post();