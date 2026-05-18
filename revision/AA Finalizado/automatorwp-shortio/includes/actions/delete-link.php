<?php
/**
 * Delete short link Action
 *
 * @package     AutomatorWP\Shortio\Actions
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Shortio_Delete_Link extends AutomatorWP_Integration_Action {

    public $integration = 'shortio';
    public $action      = 'shortio_delete_link';

    /**
     * The action result message
     * @var string 
     */
    public $result = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Delete a short link', 'automatorwp-shortio' ),
            'select_option' => __( '<strong>Delete a short link</strong>', 'automatorwp-shortio' ),
            /* translators: %s: Link URL */
            'edit_label'    => sprintf( __( 'Delete a short link: %s', 'automatorwp-shortio' ), '{link}' ),
            'log_label'     => sprintf( __( 'Delete a short link: %s', 'automatorwp-shortio' ), '{link}' ),
            'options'       => array(
                'link' => array(
                    'from'    => 'link',
                    'default' => __( 'link', 'automatorwp-shortio' ),
                    'fields'  => array(
                        'domain' => automatorwp_utilities_ajax_selector_field( array(
                            'name'        => __( 'Domain:', 'automatorwp-shortio' ),
                            'option_none' => false,
                            'action_cb'   => 'automatorwp_shortio_get_domains',
                            'options_cb'  => 'automatorwp_shortio_options_cb_domain',
                            'placeholder' => __( 'Select a domain', 'automatorwp-shortio' ),
                        ) ),
                        'link' => automatorwp_utilities_ajax_selector_field( array(
                            'name'        => __( 'Link:', 'automatorwp-shortio' ),
                            'option_none' => false,
                            'action_cb'   => 'automatorwp_shortio_get_links',
                            'options_cb'  => 'automatorwp_shortio_options_cb_link',
                            'placeholder' => __( 'Select a link', 'automatorwp-shortio' ),
                            'default'     => ''
                        ) ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $link_url = $action_options['link'];

        if( ! automatorwp_shortio_get_api() ) {
            $this->result = __( 'Short.io integration not configured.', 'automatorwp-shortio' );
            return;
        }

        if ( empty( $link_url ) ) {
            $this->result = __( 'No link selected for deletion.', 'automatorwp-shortio' );
            return;
        }

        // Parse domain and path from the URL to find the Link ID
        $url_parts = parse_url( $link_url );
        $domain    = isset( $url_parts['host'] ) ? $url_parts['host'] : '';
        $path      = isset( $url_parts['path'] ) ? ltrim( $url_parts['path'], '/' ) : '';

        $link_id = automatorwp_shortio_get_link_id( $domain, $path );

        if ( empty( $link_id ) ) {
            $this->result = __( 'Could not find the link ID in Short.io.', 'automatorwp-shortio' );
            return;
        }

        $response = automatorwp_shortio_delete_link( $link_id );

        if ( 200 === $response || 204 === $response ) {
            $this->result = __( 'Short link deleted successfully.', 'automatorwp-shortio' );
        } else {
            $this->result = sprintf( __( 'Error deleting link. API Code: %s', 'automatorwp-shortio' ), $response );
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
     * Log meta data storage
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if( $action->type === $this->action ) {
            $log_meta['result'] = $this->result;
        }
        return $log_meta;
    }

    /**
     * Log fields display
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object ) {
        if( $log->type === 'action' && $object->type === $this->action ) {
            $log_fields['result'] = array(
                'name' => __( 'Result:', 'automatorwp-shortio' ),
                'type' => 'text',
            );
        }
        return $log_fields;
    }
}

new AutomatorWP_Shortio_Delete_Link();