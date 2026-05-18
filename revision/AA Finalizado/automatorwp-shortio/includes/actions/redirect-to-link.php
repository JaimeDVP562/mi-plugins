<?php
/**
 * Redirect to a Short.io link Action
 *
 * @package     AutomatorWP\Shortio\Actions
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Shortio_Redirect_To_Link extends AutomatorWP_Integration_Action {

    public $integration = 'shortio';
    public $action      = 'shortio_redirect_to_link';

    /**
     * The redirection link
     * @var string 
     */
    public $link = '';

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
            'label'         => __( 'Redirect to a Short.io link', 'automatorwp-shortio' ),
            'select_option' => __( 'Redirect to a <strong>Short.io link</strong>', 'automatorwp-shortio' ),
            /* translators: %s: Link URL */
            'edit_label'    => sprintf( __( 'Redirect to link: %s', 'automatorwp-shortio' ), '{link}' ),
            'log_label'     => sprintf( __( 'Redirect to link: %s', 'automatorwp-shortio' ), '{link}' ),
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

        $this->link = $action_options['link'];

        // Bail if Short.io not configured
        if( ! automatorwp_shortio_get_api() ) {
            $this->result = __( 'Short.io integration not configured.', 'automatorwp-shortio' );
            return;
        }

        if ( empty( $this->link ) ) {
            $this->result = __( 'Link is empty.', 'automatorwp-shortio' );
            return;
        }

        // Clean link format
        $this->link = str_replace( array( '#038;', '&&' ), '&', $this->link );

        if ( ! filter_var( $this->link, FILTER_VALIDATE_URL ) ) {
            $this->result = sprintf( __( '%s is not a valid link.', 'automatorwp-shortio' ), $this->link );
            $this->link   = '';
            return;
        }

        // Add filter to override other redirects
        add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );

        // Handle AJAX/Cron/REST or Direct JS redirect
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {           
            update_option( 'automatorwp_redirect_link_' . $user_id, $this->link, false );       
        } else { ?>
            <script type="text/javascript">
                setTimeout( function () {
                    window.location.href = '<?php echo esc_js( $this->link ); ?>';
                }, 100 );
            </script>
        <?php }

        $this->result = __( 'User redirected successfully.', 'automatorwp-shortio' );
    }

    /**
     * Override wp_redirect() calls
     *
     * @since 1.0.0
     * @return string
     */
    public function wp_redirect( $location, $status ) {
        if( ! empty( $this->link ) ) {
            return $this->link;
        }
        return $location;
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        add_action( 'wp_ajax_automatorwp_check_for_redirect', array( $this, 'ajax_check_for_redirect' ) );
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Ajax handler to check for pending redirects
     *
     * @since 1.0.0
     */
    public function ajax_check_for_redirect() {
        check_ajax_referer( 'automatorwp', 'nonce' );

        $user_id = absint( $_REQUEST['user_id'] );
        $link    = get_option( 'automatorwp_redirect_link_' . $user_id, '' );
        $link    = esc_url_raw( $link );

        delete_option( 'automatorwp_redirect_link_' . $user_id );

        wp_send_json_success( array( 'redirect_link' => $link ) );
    }

    /**
     * Configuration notice for the admin UI
     *
     * @since 1.0.0
     */
    public function configuration_notice( $object, $item_type ) {
        if( $item_type !== 'action' || $object->type !== $this->action ) return;

        if( ! automatorwp_shortio_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px;">
                <?php printf( 
                    __( 'You need to configure the <a href="%s" target="_blank">Short.io settings</a>.', 'automatorwp-shortio' ),
                    admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-shortio' )
                ); ?>
            </div>
        <?php endif;
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

new AutomatorWP_Shortio_Redirect_To_Link();