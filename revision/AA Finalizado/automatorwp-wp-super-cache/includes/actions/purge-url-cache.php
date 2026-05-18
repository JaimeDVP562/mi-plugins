<?php
/**
 * Purge URL Cache
 *
 * @package     AutomatorWP\Integrations\WPSuperCache\Actions\Purge_URL_Cache
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WP_Super_Cache_Purge_URL_Cache extends AutomatorWP_Integration_Action {

    public $integration = 'wp_super_cache';
    public $action      = 'wp_super_cache_purge_url_cache';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge cache for a specific URL', 'automatorwp-wp-super-cache' ),
            'select_option' => __( 'Purge WP Super Cache cache for a <strong>specific URL</strong>', 'automatorwp-wp-super-cache' ),
            /* translators: %1$s: URL. */
            'edit_label'    => sprintf( __( 'Purge WP Super Cache cache for URL %1$s', 'automatorwp-wp-super-cache' ), '{url}' ),
            /* translators: %1$s: URL. */
            'log_label'     => sprintf( __( 'Purge WP Super Cache cache for URL %1$s', 'automatorwp-wp-super-cache' ), '{url}' ),
            'options'       => array(
                'url' => array(
                    'from'    => 'url',
                    'default' => __( 'URL', 'automatorwp-wp-super-cache' ),
                    'fields'  => array(
                        'url' => array(
                            'name'    => __( 'URL:', 'automatorwp-wp-super-cache' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $url = sanitize_url( $action_options['url'] );

        $this->result = '';

        if( empty( $url ) ) {
            return;
        }

        $result = automatorwp_wp_super_cache_purge_url( $url );

        if( $result ) {
            $this->result = __( 'URL cache purged successfully.', 'automatorwp-wp-super-cache' );
        } else {
            $this->result = __( 'Could not purge URL cache.', 'automatorwp-wp-super-cache' );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;
        $log_meta['url']    = isset( $action_options['url'] ) ? $action_options['url'] : '';

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields
     * @param stdClass  $log
     * @param stdClass  $object
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['url'] = array(
            'name' => __( 'URL:', 'automatorwp-wp-super-cache' ),
            'type' => 'text',
        );

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-wp-super-cache' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_WP_Super_Cache_Purge_URL_Cache();
