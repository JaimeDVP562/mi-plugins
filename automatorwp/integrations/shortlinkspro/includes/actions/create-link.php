<?php
/**
 * Add new link
 *
 * @package     AutomatorWP\Integrations\ShortLinksPro\Actions\Create_Link
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ShortLinksPro_Create_Link extends AutomatorWP_Integration_Action {

    public $integration = 'shortlinkspro';
    public $action = 'shortlinkspro_create_link';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Create a link', 'automatorwp' ),
            'select_option'     => __( 'Create a <strong>link</strong>', 'automatorwp' ),
            /* translators: %1$s: Link title. */
            'edit_label'        => sprintf( __( 'Create a %1$s', 'automatorwp' ), '{link}' ),
            /* translators: %1$s: Link title. */
            'log_label'         => sprintf( __( 'Create a %1$s', 'automatorwp' ), '{link}' ),
            'options'           => array(
                'link' => array(
                    'from' => 'link',
                    'default' => __('link', 'automatorwp'),
                    'fields' => array(
                        'name_link' => array(
                            'name' => __( 'Name:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => '',
                            'required'  => true
                        ),
                        'redirection' => array(
                            'name' => __( 'Redirect type:', 'automatorwp' ),
                            'type' => 'select',
                            'options' => shortlinkspro_redirect_types(),
                            'required'  => true
                        ),
                        'target_url' => array(
                            'name' => __( 'Target URL:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => '',
                            'required'  => true
                        ),
                        'shortlink_slug' => array(
                            'name' => __( 'ShortLink slug:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => '',
                            'desc' => __('A random slug will be generated if this field is empty.', 'automatorwp')
                        ),
                        'notes' => array(
                            'name' => __( 'Notes:', 'automatorwp' ),
                            'type' => 'textarea',
                            'default' => '',
                            'desc' => __('Internal notes to your link for your own needs.', 'automatorwp')
                        ),
                        'link_options' => array(
                            'name'      => __( 'Link options:', 'automatorwp' ),
                            'type'      => 'multicheck_inline',
                            'classes'      => 'cmb2-switch',
                            'options' => array(
                                'nofollow' => __( 'No Follow', 'automatorwp' ) . cmb_tooltip_get_html( __( 'This will add the nofollow and noindex parameters in the HTTP response headers when enabled.', 'automatorwp' ) ),
                                'sponsored' => __( 'Sponsored', 'automatorwp' ) . cmb_tooltip_get_html( __( 'This will add the sponsored parameter in the HTTP response headers when enabled.', 'automatorwp' ) ),
                                'parameter_forwarding' => __( 'Parameter Forwarding', 'automatorwp' ) . cmb_tooltip_get_html( __( 'This will forward parameters passed to links when enabled.', 'automatorwp' ) ),
                                'tracking' => __( 'Tracking', 'automatorwp' ) . cmb_tooltip_get_html( __( 'This will enable clicks tracking when enabled.', 'automatorwp' ) ),
                            ),
                            'select_all_button' => false,
                            'default' => array( 'nofollow', 'tracking' ),
                            'label_cb' => 'cmb_tooltip_label_cb',
                        ),
                    )
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
    
        $this->result = '';

        // Bail if no target URL
        if ( empty( $action_options['target_url'] ) ) {
            $this->result = __('Target url cannot be empty.', 'automatorwp');
            return;
        }

        // If empty generate random slug
        if ( empty( $action_options['slug'] ) ) {
            $slug = shortlinkspro_generate_link_slug();
        }

        // Link options
        $link_options = $action_options['link_options'];

        $link_data = array(
            'title' => $action_options['name_link'],
            'url' => $action_options['target_url'],
            'slug' => $slug,
            'redirect_type' => $action_options['redirection'],
            'nofollow' => absint( in_array( 'nofollow', $link_options) ),
            'sponsored' => absint( in_array( 'sponsored', $link_options) ),
            'parameter_forwarding' => absint( in_array( 'parameter_forwarding', $link_options) ),
            'tracking' => absint( in_array('tracking', $link_options) ),
        );

        // Insert the object
        $ct_table = ct_setup_table( 'shortlinkspro_links' );
        $object_id = ct_insert_object( $link_data );
        ct_reset_setup_table();

        $this->result = __('Link created.', 'automatorwp');
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store the action's result
        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_ShortLinksPro_Create_Link();