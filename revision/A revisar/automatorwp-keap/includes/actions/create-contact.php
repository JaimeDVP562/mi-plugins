<?php
/**
 * Create Contact
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Create-Contact
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Keap_Create_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'keap';
    public $action      = 'keap_create_contact';

    /**
     * Register the action
     *
     * @since 1.1.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a new contact', 'automatorwp-keap' ),
            'select_option' => __( 'Create a new <strong>contact</strong> in Keap', 'automatorwp-keap' ),
            'edit_label'    => __( 'Create a new contact with email {email}', 'automatorwp-keap' ),
            'log_label'     => __( 'Created contact with email {email}', 'automatorwp-keap' ),
            'options'       => array(
                'contact' => array(
                    'from'    => 'email',
                    'default' => __( 'email', 'automatorwp-keap' ),
                    'fields'  => array(
                        'email' => array(
                            'name'        => __( 'Email:', 'automatorwp-keap' ),
                            'type'        => 'text',
                            'default'     => '{user_email}',
                            'required'    => true,
                            'placeholder' => 'user@example.com',
                        ),
                        'first_name' => array(
                            'name'    => __( 'First Name:', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '{user_first_name}',
                        ),
                        'last_name' => array(
                            'name'    => __( 'Last Name:', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '{user_last_name}',
                        ),
                        'phone' => array(
                            'name'    => __( 'Phone:', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'company' => array(
                            'name'    => __( 'Company:', 'automatorwp-keap' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Action execution function
     *
     * @since 1.1.0
     *
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options (with tags already passed)
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Bail if Keap not configured
        if( ! automatorwp_keap_get_api() ) {
            $this->result = __( 'Keap integration is not configured in AutomatorWP settings', 'automatorwp-keap' );
            return;
        }

        $contact_data = array(
            'email'      => sanitize_email( $action_options['email'] ),
            'first_name' => sanitize_text_field( $action_options['first_name'] ),
            'last_name'  => sanitize_text_field( $action_options['last_name'] ),
            'phone'      => sanitize_text_field( $action_options['phone'] ),
            'company'    => sanitize_text_field( $action_options['company'] ),
        );

        $response = automatorwp_keap_create_contact( $contact_data );

        if ( $response && isset( $response['id'] ) ) {
            $this->result = sprintf(
                __( 'Contact created successfully with ID: %d', 'automatorwp-keap' ),
                $response['id']
            );
        } else {
            $this->result = __( 'The contact could not be created', 'automatorwp-keap' );
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.1.0
     */
    public function hooks() {

        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Configuration notice
     *
     * @since 1.1.0
     */
    public function configuration_notice( $object, $item_type ) {

        if ( $item_type !== 'action' ) {
            return;
        }

        // Use ->type (not ->post_type) — consistent with AutomatorWP framework
        if ( $object->type !== $this->action ) {
            return;
        }

        if ( ! automatorwp_keap_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Keap settings</a> to get this action to work.', 'automatorwp-keap' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-keap'
                ); ?>
            </div>
        <?php endif;
    }

    /**
     * Action log meta
     *
     * @since 1.1.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;
        $log_meta['email']  = isset( $action_options['email'] ) ? $action_options['email'] : '';

        return $log_meta;
    }

    /**
     * Action log fields
     *
     * @since 1.1.0
     */
    public function log_fields( $log_fields, $log, $object ) {

        if ( $log->type !== 'action' ) {
            return $log_fields;
        }

        if ( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-keap' ),
            'type' => 'text',
        );

        $log_fields['email'] = array(
            'name' => __( 'Email:', 'automatorwp-keap' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_Keap_Create_Contact();