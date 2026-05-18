<?php
/**
 * Add Tag
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Add_Tag
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AutomatorWP_Keap_Add_Tag extends AutomatorWP_Integration_Action {

	public $integration = 'keap';
	public $action = 'keap_add_tag';

	/**
	 * Register the action
	 *
	 * @since 1.0.0
	 */
	public function register() {

		automatorwp_register_action( $this->action, array(
			'integration'   => $this->integration,
			'label'         => __( 'Add a tag to contact', 'automatorwp-keap' ),
			'select_option' => __( 'Add a <strong>tag</strong> to contact', 'automatorwp-keap' ),
			/* translators: %1$s: Tag. %2$s: Contact. */
			'edit_label'    => sprintf( __( 'Add %1$s to %2$s', 'automatorwp-keap' ), '{tag}', '{email}' ),
			/* translators: %1$s: Tag. %2$s: Contact. */
			'log_label'     => sprintf( __( 'Add %1$s to %2$s', 'automatorwp-keap' ), '{tag}', '{email}' ),
			'options'       => array(
				'tag'   => array(
					'from'    => 'tag',
					'default' => __( 'tag', 'automatorwp-keap' ),
					'fields'  => array(
						'tag' => automatorwp_utilities_ajax_selector_field( array(
							'name'        => __( 'Tag:', 'automatorwp-keap' ),
							'option_none' => false,
							'action_cb'   => 'automatorwp_keap_get_tags',
							'options_cb'  => 'automatorwp_keap_options_cb_tags',
							'placeholder' => __( 'Select a tag', 'automatorwp-keap' ),
							'default'     => '',
							'required'    => true
						) )
					),
				),
				'email' => array(
					'from'    => 'email',
					'default' => __( 'email', 'automatorwp-keap' ),
					'fields'  => array(
						'email' => array(
							'name'     => __( 'Contact Email:', 'automatorwp-keap' ),
							'type'     => 'text',
							'default'  => '{{user_email}}',
							'required' => true
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
	 * @param stdClass $action         The action object
	 * @param int      $user_id        The user ID
	 * @param array    $action_options The action's stored options (with tags already passed)
	 * @param stdClass $automation     The action's automation object
	 */
	public function execute( $action, $user_id, $action_options, $automation ) {

		// Shorthand
		$tag_id = $action_options['tag'];
		$email  = $action_options['email'];

		// Bail if Keap not configured
		if ( ! automatorwp_keap_get_api() ) {
			$this->result = __( 'Keap integration is not configured.', 'automatorwp-keap' );

			return;
		}

		if ( empty( $tag_id ) || empty( $email ) ) {
			$this->result = __( 'Missing Tag ID or Email.', 'automatorwp-keap' );

			return;
		}

		// Steps:
		// 1. Find Contact by Email. (Need a lookup function)
		// 2. Add Tag to Contact ID.

		// Implementation of Lookup in execute for now as it wasn't in functions.php yet
		// We'll add a quick lookup helper here or use valid API calls.
		// Since I can't easily edit functions.php again without context switch, I'll rely on a helper if it existed,
		// or implement inline.
		// Keap V1: GET /contacts?email=...
        
        $api = automatorwp_keap_get_api();
        $contact_id = false;
        
        // Find Contact
        $url = add_query_arg( array( 'email' => $email, 'optional_properties' => 'id' ), 'https://api.keap.com/crm/rest/v1/contacts' );
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['access_token'],
            )
        );
        
        $response = wp_remote_get( $url, $args );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( isset( $data['contacts'][0]['id'] ) ) {
            $contact_id = $data['contacts'][0]['id'];
        } else {
             // Contact doesn't exist? Create or Fail?
             // Usually "Add Tag" implies the contact exists. 
             // Optionally we could create it. For now, fail.
             $this->result = __( 'Contact not found.', 'automatorwp-keap' );
             return;
        }

		$response = automatorwp_keap_add_tag_to_contact( $contact_id, $tag_id );

		if ( $response ) {
			$this->result = __( 'Tag added to contact.', 'automatorwp-keap' );
		} else {
			$this->result = __( 'Could not add tag.', 'automatorwp-keap' );
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
	 *
	 * @param stdClass $object    The trigger/action object
	 * @param string   $item_type The object type (trigger|action)
	 */
	public function configuration_notice( $object, $item_type ) {

		// Bail if action type don't match this action
		if ( $item_type !== 'action' ) {
			return;
		}

		if ( $object->type !== $this->action ) {
			return;
		}

		// Warn user if the authorization has not been setup from settings
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
	 * Action custom log meta
	 *
	 * @since 1.0.0
	 *
	 * @param array    $log_meta       Log meta data
	 * @param stdClass $action         The action object
	 * @param int      $user_id        The user ID
	 * @param array    $action_options The action's stored options (with tags already passed)
	 * @param stdClass $automation     The action's automation object
	 *
	 * @return array
	 */
	public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

		// Bail if action type don't match this action
		if ( $action->type !== $this->action ) {
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
	 * @param array    $log_fields The log fields
	 * @param stdClass $log        The log object
	 * @param stdClass $object     The trigger/action/automation object attached to the log
	 *
	 * @return array
	 */
	public function log_fields( $log_fields, $log, $object ) {

		// Bail if log is not assigned to an action
		if ( $log->type !== 'action' ) {
			return $log_fields;
		}

		// Bail if action type don't match this action
		if ( $object->type !== $this->action ) {
			return $log_fields;
		}

		$log_fields['result'] = array(
			'name' => __( 'Result:', 'automatorwp-keap' ),
			'type' => 'text',
		);

		return $log_fields;
	}
}

new AutomatorWP_Keap_Add_Tag();
