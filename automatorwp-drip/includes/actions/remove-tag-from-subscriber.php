<?php
/**
 * Remove Tag from Subscriber
 *
 * @package     AutomatorWP\Integrations\Drip\Actions\Remove_Tag_From_Subscriber
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Remove_Tag_From_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'drip';
    public $action = 'drip_remove_tag_from_subscriber';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Remove tag from subscriber', 'automatorwp' ),
            'select_option'     => __( 'Remove <strong>tag</strong> from <strong>subscriber</strong>', 'automatorwp' ),
            /* translators: %1$s: Tag. */
            'edit_label'        => sprintf( __( 'Remove %1$s from subscriber', 'automatorwp' ), '{tag}' ),
            /* translators: %1$s: Tag. */
            'log_label'         => sprintf( __( 'Remove %1$s from subscriber', 'automatorwp' ), '{tag}' ),
            'options'           => array(
                'tag' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'tag',
                    'option_default'    => __( 'Select a tag', 'automatorwp' ),
                    'name'              => __( 'Tag:', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_drip_get_tags',
                    'options_cb'        => 'automatorwp_drip_options_cb_tags',
                    'placeholder' => __( 'Select a tag', 'automatorwp' ),
                    'default'           => ''
                ) ),
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
        $user = get_user_by ( 'ID', $user_id );
        $tag_id = $action_options['tag'];

        // Bail if Drip not configured
        if( ! automatorwp_drip_get_api() ) {
            $this->result = __( 'Drip integration not configured in AutomatorWP settings.', 'automatorwp' );
            return;
        }

        // Bail if tag is empty
        if ( empty( $tag_id ) ) {
            $this->result = __( 'Tag field is empty.', 'automatorwp' );
            return;
        }

        $response = automatorwp_drip_remove_tag_subscriber( $user->user_email, $tag_id );

        if ( $response === 203 || $response === 204 ) {

            $this->result = sprintf( __( 'Tag %s removed from Subscriber %s', 'automatorwp' ), $tag_id, $user->user_email );

        } else $this->result = sprintf( __( 'Error removing tag %s from Subscriber %s: ' . $response, 'automatorwp' ), $tag_id, $user->user_email );

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
        if( ! automatorwp_drip_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Drip settings</a> to get this action to work.', 'automatorwp' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-drip'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp' ),
                    'https://automatorwp.com/docs/drip/'
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
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Drip_Remove_Tag_From_Subscriber();