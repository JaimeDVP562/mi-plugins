<?php
/**
 * Create Contact
 *
 * @package     AutomatorWP\Integrations\Keap\Actions\Create_Contact
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AutomatorWP_Keap_Create_Contact extends AutomatorWP_Integration_Action {

	public $integration = 'keap';
	public $action = 'keap_create_contact';

	/**
	 * Register the action
	 *
	 * @since 1.0.0
	 */
	public function register() {

		automatorwp_register_action( $this->action, array(
			'integration'   => $this->integration,
			'label'         => __( 'Create a contact', 'automatorwp-keap' ),
			'select_option' => __( 'Create a <strong>contact</strong>', 'automatorwp-keap' ),
			/* translators: %1$s: Contact. */
			'edit_label'    => sprintf( __( 'Create a %1$s', 'automatorwp-keap' ), '{contact}' ),
			/* translators: %1$s: Contact. */
			'log_label'     => sprintf( __( 'Create a %1$s', 'automatorwp-keap' ), '{contact}' ),
			'options'       => array(
				'contact' => array(
					'from'    => 'contact',
					'default' => __( 'contact', 'automatorwp-keap' ),
					'fields'  => array(
						'email'      => array(
							'name'     => __( 'Email:', 'automatorwp-keap' ),
							'type'     => 'text',
							'default'  => '{{user_email}}',
							'required' => true
						),
						'first_name' => array(
							'name'     => __( 'First Name:', 'automatorwp-keap' ),
							'type'     => 'text',
							'default'  => '{{user_firstname}}',
							'required' => false
						),
						'last_name'  => array(
							'name'     => __( 'Last Name:', 'automatorwp-keap' ),
							'type'     => 'text',
							'default'  => '{{user_lastname}}',
							'required' => false
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
		$contact_data = array(
			'email'      => $action_options['email'],
			'first_name' => $action_options['first_name'],
			'last_name'  => $action_options['last_name'],
		);

		// Bail if Keap not configured
		if ( ! automatorwp_keap_get_api() ) {
			$this->result = __( 'Keap integration is not configured in AutomatorWP settings', 'automatorwp-keap' );

			return;
		}

		$response = automatorwp_keap_create_contact( $contact_data );

		if ( $response ) {
			$this->result = sprintf( __( 'Contact created with ID: %s', 'automatorwp-keap' ), $response );
		} else {
			$this->result = __( 'The contact could not be created', 'automatorwp-keap' );
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

new AutomatorWP_Keap_Create_Contact();
