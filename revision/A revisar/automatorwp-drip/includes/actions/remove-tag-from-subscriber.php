<?php
/**
 * Remove Tag from Subscriber
 *
 * @package     AutomatorWP\Integrations\Drip\Actions\Remove_Tag_From_Subscriber
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Remove_Tag_From_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'drip';
    public $action      = 'drip_remove_tag_from_subscriber';
    public $result      = '';

    /**
     * Register action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Remove tag from subscriber', 'automatorwp-drip' ),
            'select_option' => __( 'Remove <strong>tag</strong> from <strong>subscriber</strong>', 'automatorwp-drip' ),
            /* translators: %1$s: Tag. */
            'edit_label'    => sprintf( __( 'Remove %1$s from subscriber', 'automatorwp-drip' ), '{tag}' ) ?: '',
            /* translators: %1$s: Tag. */
            'log_label'     => sprintf( __( 'Remove %1$s from subscriber', 'automatorwp-drip' ), '{tag}' ) ?: '',
            'options'       => array(
                'tag' => array(
                    'from'    => 'tag',
                    'default' => __( 'tag', 'automatorwp-drip' ),
                    'fields'  => array(
                        'email' => array(
                            'name'    => __( 'Email:', 'automatorwp-drip' ),
                            'desc'    => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-drip' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                        'tag' => automatorwp_utilities_ajax_selector_field( array(
                            'name'       => __( 'Tags:', 'automatorwp-drip' ),
                            'desc'       => __( 'Select tags in your Drip account.', 'automatorwp-drip' ),
                            'type'       => 'select',
                            'field'      => 'tag',
                            'action_cb'  => 'automatorwp_drip_get_tags',
                            'options_cb' => 'automatorwp_drip_options_cb_tag',
                            'attributes' => array(
                                'placeholder' => __( 'Select tags', 'automatorwp-drip' ),
                            ),
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
     *
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Bail if Drip not configured
        if ( ! automatorwp_drip_get_api() ) {
            $this->result = __( 'Drip integration not configured in AutomatorWP settings.', 'automatorwp-drip' );
            return;
        }

        // Get Email (fallback to the user who triggers the automation)
        $email = isset( $action_options['email'] ) && ! empty( $action_options['email'] )
            ? sanitize_email( $action_options['email'] )
            : '';

        if ( empty( $email ) ) {
            $user  = get_user_by( 'ID', $user_id );
            $email = $user ? $user->user_email : '';
        }

        $tag         = isset( $action_options['tag'] ) ? $action_options['tag'] : '';
        $tag_to_send = is_array( $tag ) ? reset( $tag ) : $tag;

        if ( empty( $email ) || empty( $tag_to_send ) ) {
            $this->result = __( 'No email or tag provided.', 'automatorwp-drip' );
            return;
        }

        $response = automatorwp_drip_remove_tag_subscriber( $email, $tag_to_send );

        if ( $response['code'] === 204 || $response['code'] === 200 ) {
            $this->result = sprintf( __( 'Tag "%1$s" removed from %2$s.', 'automatorwp-drip' ), $tag_to_send, $email );
        } else {
            $this->result = sprintf( __( 'Error removing tag: HTTP %d', 'automatorwp-drip' ), $response['code'] );
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
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

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

        if ( $item_type !== 'action' || $object->type !== $this->action ) return;

        if ( ! automatorwp_drip_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Drip settings</a> to get this action to work.', 'automatorwp-drip' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-drip'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-drip' ),
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
     * @param array     $log_meta       Log meta data
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) return $log_meta;

        $log_meta['result'] = (string) $this->result;

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

        if ( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;

        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-drip' ), 'type' => 'text' );

        return $log_fields;

    }

}

new AutomatorWP_Drip_Remove_Tag_From_Subscriber();
