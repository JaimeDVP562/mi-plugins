<?php
/**
 * Create Short Link Action
 *
 * @package     AutomatorWP\Integrations\Bitly\Actions\Create_Short_Link
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Bitly_Create_Short_Link extends AutomatorWP_Integration_Action {

    public $integration = 'bitly';
    public $action = 'bitly_create_short_link';
    public $short_url = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Create a short link', 'automatorwp-bitly' ),
            'select_option'     => __( 'Create a <strong>short link</strong>', 'automatorwp-bitly' ),
            'edit_label'        => sprintf( __( 'Create a short link from %s', 'automatorwp-bitly' ), '{link}' ),
            'log_label'         => sprintf( __( 'Create a short link from %s', 'automatorwp-bitly' ), '{link}' ),
            'options'           => array(
                'link' => array(
                    'from'    => 'link',
                    'default' => __( 'link', 'automatorwp-bitly' ),
                    'fields'  => array(
                        'long_url' => array(
                            'name'    => __( 'URL to shorten:', 'automatorwp-bitly' ),
                            'desc'    => __( 'Enter the URL you want to shorten.', 'automatorwp-bitly' ),
                            'type'    => 'text',
                            'default' => '',
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
     *
     * @param stdClass  $action          The action object
     * @param int       $user_id         The user ID
     * @param array     $action_options  The action's stored options
     * @param stdClass  $automation      The automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Get the field values
        $long_url = $action_options['long_url'];

        // Check if API is connected
        if ( ! automatorwp_bitly_get_api() ) {
            $this->result = __( 'Bitly integration is not configured in AutomatorWP settings', 'automatorwp-bitly' );
            return;
        }

        // URL is mandatory
        if ( empty( $long_url ) ) {
            $this->result = __( 'A URL is required to create a short link', 'automatorwp-bitly' );
            return;
        }

        // Send request to Bitly
        $response = automatorwp_bitly_create_short_link( $long_url );

        // Check response
        if ( $response && $response['success'] ) {
            $this->short_url = $response['short_url'];
            $this->result    = sprintf(
                __( 'Short link created successfully: %s', 'automatorwp-bitly' ),
                $response['short_url']
            );
        } else {
            $this->short_url = '';
            $this->result    = __( 'The short link could not be created.', 'automatorwp-bitly' );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     */
    public function configuration_notice( $object, $item_type ) {

        if ( $item_type !== 'action' ) {
            return;
        }

        if ( $object->type !== $this->action ) {
            return;
        }

        if ( ! automatorwp_bitly_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Bitly settings</a> to get this action to work.', 'automatorwp-bitly' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-bitly'
                ); ?>
                <br>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-bitly' ),
                    'https://automatorwp.com/docs/bitly/'
                ); ?>
            </div>
        <?php endif;
    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result']    = $this->result;
        $log_meta['short_url'] = $this->short_url;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object ) {

        if ( $log->type !== 'action' ) {
            return $log_fields;
        }

        if ( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-bitly' ),
            'type' => 'text',
        );

        $log_fields['short_url'] = array(
            'name' => __( 'Short URL:', 'automatorwp-bitly' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_Bitly_Create_Short_Link();