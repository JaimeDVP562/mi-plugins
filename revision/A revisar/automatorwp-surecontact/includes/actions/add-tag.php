<?php
/**
 *
 * @package     AutomatorWP\Integrations\SureContact\Actions\Add_Tag
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SureContact_Add_Tag extends AutomatorWP_Integration_Action {

    public $integration = 'surecontact';
    public $action = 'surecontact_add_tag';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add a tag to a contact', 'automatorwp-surecontact' ),
            'select_option' => __( 'Add a <strong>tag</strong> to a contact', 'automatorwp-surecontact' ),
            /* translators: %1$s: Email. %2$s: Tag. */
            'edit_label'    => sprintf( __( 'Add tag %1$s to contact %2$s in SureContact', 'automatorwp-surecontact' ), '{tag_uuid}', '{email}' ),
            /* translators: %1$s: Email. %2$s: Tag. */
            'log_label'     => sprintf( __( 'Add tag %1$s to contact %2$s in SureContact', 'automatorwp-surecontact' ), '{tag_uuid}', '{email}' ),
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
                'tag_uuid' => array(
                    'from'    => 'tag_uuid',
                    'default' => __( 'tag', 'automatorwp-surecontact' ),
                    'fields'  => array(
                        'tag_uuid' => array(
                            'name'    => __( 'Tag UUID:', 'automatorwp-surecontact' ),
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
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $email    = $action_options['email'];
        $tag_uuid = $action_options['tag_uuid'];

        $this->result = '';

        if( empty( $email ) || empty( $tag_uuid ) ) {
            return;
        }

        if( ! AutomatorWP_SureContact_get_api() ) {
            $this->result = __( 'SureContact integration not configured in AutomatorWP settings.', 'automatorwp-surecontact' );
            return;
        }

        $user = get_user_by( 'email', $email );

        if( ! $user ) {
            return;
        }

        $contact_uuid = AutomatorWP_SureContact_create_contact( $email, $user->first_name, $user->last_name );

        if( ! $contact_uuid ) {
            $this->result = __( 'Contact could not be found or created.', 'automatorwp-surecontact' );
            return;
        }

        $result = AutomatorWP_SureContact_attach_tag( $contact_uuid, $tag_uuid );

        if( $result ) {
            $this->result = __( 'Tag added successfully.', 'automatorwp-surecontact' );
        } else {
            $this->result = __( 'The tag could not be added.', 'automatorwp-surecontact' );
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

new AutomatorWP_SureContact_Add_Tag();