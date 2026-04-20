<?php
/**
 * Create or Update Subscriber
 *
 * @package     AutomatorWP\Integrations\Drip\Actions\Create_Update_Subscriber
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Create_Update_Subscriber extends AutomatorWP_Integration_Action {

    public $integration = 'drip';
    public $action      = 'drip_create_update_subscriber';
    public $result      = '';

    /**
     * Register action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create / update subscriber', 'automatorwp-drip' ),
            'select_option' => __( 'Create / update <strong>subscriber</strong>', 'automatorwp-drip' ),
            /* translators: %1$s: Email. */
            'edit_label'    => sprintf( __( 'Create/update %1$s', 'automatorwp-drip' ), '{email}' ) ?: '',
            /* translators: %1$s: Email. */
            'log_label'     => sprintf( __( 'Create/update %1$s', 'automatorwp-drip' ), '{email}' ) ?: '',
            'options'       => array(
                'email' => array(
                    'from'    => 'email',
                    'default' => __( 'subscriber', 'automatorwp-drip' ),
                    'fields'  => array(
                        'email' => array(
                            'name'       => __( 'Email:', 'automatorwp-drip' ),
                            'desc'       => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'sample@email.com or use the tag selector', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
                        ),
                        'first_name' => array(
                            'name'       => __( 'First Name:', 'automatorwp-drip' ),
                            'desc'       => __( 'Leave empty to use the name of the user who triggers the automation.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'John or use the tag selector', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
                        ),
                        'last_name' => array(
                            'name'       => __( 'Last Name:', 'automatorwp-drip' ),
                            'desc'       => __( 'Leave empty to use the last name of the user who triggers the automation.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'Doe or use the tag selector', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
                        ),
                        'tag' => array(
                            'name'       => __( 'Tags:', 'automatorwp-drip' ),
                            'desc'       => __( 'Use comma-separated tags or dynamic tags.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'customer, lead, active... or use the tag selector', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
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
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Bail if Drip not configured
        if ( ! automatorwp_drip_get_api() ) {
            $this->result = __( 'Drip integration not configured.', 'automatorwp-drip' );
            return;
        }

        $email      = isset( $action_options['email'] )      ? sanitize_email( $action_options['email'] )            : '';
        $first_name = isset( $action_options['first_name'] ) ? sanitize_text_field( $action_options['first_name'] )  : '';
        $last_name  = isset( $action_options['last_name'] )  ? sanitize_text_field( $action_options['last_name'] )   : '';
        $tag_input  = isset( $action_options['tag'] )        ? sanitize_text_field( $action_options['tag'] )         : '';

        // Fetch WP user once for fallbacks on empty fields
        $user = null;
        if ( empty( $email ) || empty( $first_name ) || empty( $last_name ) ) {
            $user = get_user_by( 'ID', $user_id );
        }

        if ( empty( $email ) && $user ) {
            $email = $user->user_email;
        }

        if ( empty( $email ) ) {
            $this->result = __( 'No email provided.', 'automatorwp-drip' );
            return;
        }

        if ( empty( $first_name ) && $user ) {
            $first_name = get_user_meta( $user->ID, 'first_name', true );
        }

        if ( empty( $last_name ) && $user ) {
            $last_name = get_user_meta( $user->ID, 'last_name', true );
        }

        $subscriber_data = array(
            'email'      => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
        );

        // Process comma-separated tags into a trimmed array
        if ( ! empty( $tag_input ) ) {
            $subscriber_data['tags'] = array_filter( array_map( 'trim', explode( ',', $tag_input ) ) );
        }

        $response = automatorwp_drip_create_update_subscriber( $subscriber_data );

        if ( $response['code'] === 201 || $response['code'] === 200 ) {
            $status       = ( $response['code'] === 201 ) ? __( 'added', 'automatorwp-drip' ) : __( 'updated', 'automatorwp-drip' );
            $this->result = sprintf( __( 'Subscriber %1$s %2$s in Drip.', 'automatorwp-drip' ), $email, $status );
        } else {
            $this->result = sprintf( __( 'Error for %1$s: HTTP %2$d', 'automatorwp-drip' ), $email, $response['code'] );
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

new AutomatorWP_Drip_Create_Update_Subscriber();
