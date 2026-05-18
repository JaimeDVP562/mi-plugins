<?php
/**
 * Create and assign tag to contact
 *
 * @package     AutomatorWP\Integrations\ActiveCampaign\Actions\Create_Contact_Tag
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ActiveCampaign_Create_Contact_Tag extends AutomatorWP_Integration_Action {

    public $integration = 'activecampaign';
    public $action = 'activecampaign_create_contact_tag';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Create tag and assign to contact', 'automatorwp-activecampaign' ),
            'select_option'     => __( 'Create <strong>tag</strong> and assign to <strong>contact</strong>', 'automatorwp-activecampaign' ),
            /* translators: %1$s: Tag. */
            'edit_label'        => sprintf( __( 'Create %1$s and assign to %2$s', 'automatorwp-activecampaign' ), '{tag}', '{contact}' ),
            /* translators: %1$s: Tag. */
            'log_label'         => sprintf( __( 'Create %1$s and assign to %2$s', 'automatorwp-activecampaign' ), '{tag}', '{contact}' ),
            'options'           => array(
                'tag' => array(
                    'from' => 'tag',
                    'default' => __( 'tag', 'automatorwp-activecampaign' ),
                    'fields' => array(
                        'tag' => array(
                            'name' => __( 'Tag:', 'automatorwp-activecampaign' ),
                            'desc' => __( 'The tag name', 'automatorwp-activecampaign' ),
                            'type' => 'text',
                            'required'  => true,
                            'default' => ''
                        ),
                    )
                ),
                'contact' => array(
                    'from' => 'user_email',
                    'default' => __( 'contact', 'automatorwp-activecampaign' ),
                    'fields' => array(
                        'user_email' => array(
                            'name' => __( 'Email:', 'automatorwp-activecampaign' ),
                            'desc' => __( 'The contact email address.', 'automatorwp-activecampaign' ),
                            'type' => 'text',
                            'required'  => true,
                            'default' => ''
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

        // Shorthand
        $tag_name = $action_options['tag'];

        // Bail if tag is empty
        if ( empty ( $tag_name ) ) {
            return;
        }

        $contact_data = wp_parse_args ( $action_options, array(
            'user_email'    => '',
        ) );

        $this->result = '';

        // Bail if contact email is empty
        if ( empty( $contact_data['user_email'] ) ){
            $this->result = __( 'Email contact field is empty', 'automatorwp-activecampaign' );
            return;
        }

        // Bail if ActiveCampaign not configured
        if( ! automatorwp_activecampaign_get_api() ) {
            $this->result = __( 'ActiveCampaign integration not configured in AutomatorWP settings.', 'automatorwp-activecampaign' );
            return;
        }

        $response = automatorwp_activecampaign_get_contact( $contact_data['user_email'] );

        // Bail if user doesn't exist in ActiveCampaign
        if ( empty( $response['contacts'] ) ) {

            $this->result = sprintf( __( '%s is not a contact in ActiveCampaign', 'automatorwp-activecampaign' ), $contact_data['user_email'] );
            return;

        }

        foreach ( $response['contacts'] as $contact ) {
            $contact_id = $contact['id'];
        }

        // To get all contact tags in ActiveCampaign
        $list_tags = automatorwp_activecampaign_get_tags();
        $tag_id = false;

        if ( ! empty( $list_tags ) ) {

            // Check if tag exists
            foreach( $list_tags as $tag ) {
                if ( strtolower( $tag['name'] ) === strtolower( $tag_name ) ) {
                    $tag_id = $tag['id'];
                    break;
                }
            }

        }

        // Create tag if not exist
        if ( $tag_id === false ) {
            $response = automatorwp_activecampaign_create_tag( $tag_name );
            $tag_id = $response['tag']['id'];

            $this->result = sprintf( __( 'Tag %s created and assigned to %s', 'automatorwp-activecampaign' ), $tag_name, $contact_data['user_email'] );
        } else {
            $this->result = sprintf( __( 'Tag %s found and assigned to %s', 'automatorwp-activecampaign' ), $tag_name, $contact_data['user_email'] );
        }

        // To add tag to contact
        automatorwp_activecampaign_add_contact_tag( $contact_id, $tag_id );

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
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {

        // Bail if action type don't match this action
        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if( ! automatorwp_activecampaign_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">ActiveCampaign settings</a> to get this action to work.', 'automatorwp-activecampaign' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-activecampaign'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-activecampaign' ),
                    'https://automatorwp.com/docs/activecampaign/'
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
            'name' => __( 'Result:', 'automatorwp-activecampaign' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_ActiveCampaign_Create_Contact_Tag();