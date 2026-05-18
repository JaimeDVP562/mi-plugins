<?php
/**
 * Create short link Action
 *
 * @package     AutomatorWP\Shortio\Actions
 * @since       1.0.0
 */

if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Shortio_Create_Short_Link extends AutomatorWP_Integration_Action {

    public $integration = 'shortio';
    public $action = 'shortio_create_short_link';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a new short link', 'automatorwp-shortio' ),
            'select_option' => __( 'Create a <strong>new short link</strong>', 'automatorwp-shortio' ),
            /* translators: %s: Link name */
            'edit_label'    => sprintf( __( 'Create a new short link: %s', 'automatorwp-shortio' ), '{link_name}' ),
            'log_label'     => sprintf( __( 'Create a new short link: %s', 'automatorwp-shortio' ), '{link_name}' ),
            'options'       => array(
                'link_name' => array(
                    'from'    => 'link_name',
                    'default' => __( 'link name', 'automatorwp-shortio' ),
                    'fields'  => array(
                        'domain' => automatorwp_utilities_ajax_selector_field( array(
                            'name'        => __( 'Domain:', 'automatorwp-shortio' ),
                            'option_none' => false,
                            'action_cb'   => 'automatorwp_shortio_get_domains',
                            'options_cb'  => 'automatorwp_shortio_options_cb_domain',
                            'placeholder' => __( 'Select a domain', 'automatorwp-shortio' ),
                        ) ),
                        'original_link' => array(
                            'name'       => __( 'Original link:', 'automatorwp-shortio' ),
                            'desc'       => __( 'The destination URL.', 'automatorwp-shortio' ),
                            'type'       => 'text',
                            'required'   => true,
                            'attributes' => array( 'placeholder' => 'https://' ),
                        ),
                        'link_name' => array(
                            'name'       => __( 'Short link path:', 'automatorwp-shortio' ),
                            'desc'       => __( 'The custom slug for the short link.', 'automatorwp-shortio' ),
                            'type'       => 'text',
                            'attributes' => array( 'placeholder' => 'my-custom-slug' ),
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Execute the action
     *
     * @since 1.0.0
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        $domain_id     = $action_options['domain'];
        $original_link = $action_options['original_link'];
        $link_name     = $action_options['link_name'];

        if( ! automatorwp_shortio_get_api() ) {
            $this->result = __( 'Short.io not configured.', 'automatorwp-shortio' );
            return;
        }

        if ( empty( $domain_id ) || empty( $original_link ) ) {
            $this->result = __( 'Required fields are empty.', 'automatorwp-shortio' );
            return;
        }

        $domain_name = automatorwp_shortio_get_domain_name( $domain_id );
        $response    = automatorwp_shortio_create_short_link( $domain_name, $original_link, $link_name );

        if ( 200 === $response || 201 === $response ) {
            $this->result = sprintf( __( 'Short link "%s" created successfully.', 'automatorwp-shortio' ), $link_name );
        } else {
            $this->result = sprintf( __( 'Error creating link. API Code: %s', 'automatorwp-shortio' ), $response );
        }
    }
}
new AutomatorWP_Shortio_Create_Short_Link();