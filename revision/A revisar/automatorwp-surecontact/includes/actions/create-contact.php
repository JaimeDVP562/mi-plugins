<?php
/**
 * Create Contact
 *
 * @package     AutomatorWP\Integrations\SureContact\Actions\Create_Contact
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SureContact_Create_Contact extends AutomatorWP_Integration_Action {

    public $integration = 'surecontact';
    public $action = 'surecontact_create_contact';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a contact', 'automatorwp-surecontact' ),
            'select_option' => __( 'Create a <strong>contact</strong> in SureContact', 'automatorwp-surecontact' ),
            /* translators: %1$s: Email. */
            'edit_label'    => sprintf( __( 'Create contact %1$s in SureContact', 'automatorwp-surecontact' ), '{email}' ),
            /* translators: %1$s: Email. */
            'log_label'     => sprintf( __( 'Create contact %1$s in SureContact', 'automatorwp-surecontact' ), '{email}' ),
            'options'       => array(
                'email' => array(
                    'from'    => 'email',
                    'default' => __( 'email', 'automatorwp-surecontact' ),
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-surecontact' ),
                            'type'    => 'text',
                            'default' => '{user_email}'
                        ),
                    ),
                ),
                'first_name' => array(
                    'from'    => 'first_name',
                    'default' => __( 'first name', 'automatorwp-surecontact' ),
                    'fields'  => array(
                        'first_name' => array(
                            'name'    => __( 'First name:', 'automatorwp-surecontact' ),
                            'type'    => 'text',
                            'default' => '{user_first_name}'
                        ),
                    ),
                ),
                'last_name' => array(
                    'from'    => 'last_name',
                    'default' => __( 'last name', 'automatorwp-surecontact' ),
                    'fields'  => array(
                        'last_name' => array(
                            'name'    => __( 'Last name:', 'automatorwp-surecontact' ),
                            'type'    => 'text',
                            'default' => '{user_last_name}'
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
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $email      = $action_options['email'];
        $first_name = $action_options['first_name'];
        $last_name  = $action_options['last_name'];

        $this->result = '';

        if( empty( $email ) ) {
            return;
        }

        if( ! AutomatorWP_SureContact_get_api() ) {
            $this->result = __( 'SureContact integration not configured in AutomatorWP settings.', 'automatorwp-surecontact' );
            return;
        }

        $contact_uuid = AutomatorWP_SureContact_create_contact( $email, $first_name, $last_name );

        if( $contact_uuid ) {
            $this->result = __( 'Contact created successfully.', 'automatorwp-surecontact' );
        } else {
            $this->result = __( 'The contact could not be created.', 'automatorwp-surecontact' );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
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
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {

        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        if( ! AutomatorWP_SureContact_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">SureContact settings</a> to get this action to work.', 'automatorwp-surecontact' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-surecontact'
                ); ?>
            </div>
        <?php endif;

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

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

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

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-surecontact' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_SureContact_Create_Contact();