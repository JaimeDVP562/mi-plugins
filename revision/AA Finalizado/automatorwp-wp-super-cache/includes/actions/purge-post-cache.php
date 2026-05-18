<?php
/**
 * Purge Post Cache
 *
 * @package     AutomatorWP\Integrations\WPSuperCache\Actions\Purge_Post_Cache
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WP_Super_Cache_Purge_Post_Cache extends AutomatorWP_Integration_Action {

    public $integration = 'wp_super_cache';
    public $action      = 'wp_super_cache_purge_post_cache';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge cache for a specific post', 'automatorwp-wp-super-cache' ),
            'select_option' => __( 'Purge WP Super Cache cache for a <strong>specific post</strong>', 'automatorwp-wp-super-cache' ),
            /* translators: %1$s: Post ID. */
            'edit_label'    => sprintf( __( 'Purge WP Super Cache cache for post %1$s', 'automatorwp-wp-super-cache' ), '{post_id}' ),
            /* translators: %1$s: Post ID. */
            'log_label'     => sprintf( __( 'Purge WP Super Cache cache for post %1$s', 'automatorwp-wp-super-cache' ), '{post_id}' ),
            'options'       => array(
                'post_id' => array(
                    'from'    => 'post_id',
                    'default' => __( 'post ID', 'automatorwp-wp-super-cache' ),
                    'fields'  => array(
                        'post_id' => array(
                            'name'    => __( 'Post ID:', 'automatorwp-wp-super-cache' ),
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

        $post_id = absint( $action_options['post_id'] );

        $this->result = '';

        if( empty( $post_id ) ) {
            return;
        }

        $result = automatorwp_wp_super_cache_purge_post( $post_id );

        if( $result ) {
            $this->result = __( 'Post cache purged successfully.', 'automatorwp-wp-super-cache' );
        } else {
            $this->result = __( 'Could not purge post cache.', 'automatorwp-wp-super-cache' );
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

        $log_meta['result']  = $this->result;
        $log_meta['post_id'] = isset( $action_options['post_id'] ) ? $action_options['post_id'] : 0;

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

        $log_fields['post_id'] = array(
            'name' => __( 'Post ID:', 'automatorwp-wp-super-cache' ),
            'type' => 'text',
        );

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-wp-super-cache' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_WP_Super_Cache_Purge_Post_Cache();
