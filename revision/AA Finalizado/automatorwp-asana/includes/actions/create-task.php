<?php
/**
 * Asana Action: Create Task
 *
 * @package     AutomatorWP\Asana\Actions\Create_Task
 * @author      AutomatorWP
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AutomatorWP class to handle "Create Task in Asana" action.
 *
 * @since 1.0.0
 */
class AutomatorWP_Asana_Create_Task extends AutomatorWP_Integration_Action {

    /**
     * The integration slug.
     *
     * @var string
     */
    public $integration = 'asana';

    /**
     * The action slug.
     *
     * @var string
     */
    public $action      = 'asana_create_task';

    /**
     * Registers the action in AutomatorWP.
     *
     * @since 1.0.0
     * @return void
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create task in Asana', 'automatorwp-asana' ),
            'select_option' => __( 'Create a task in Asana', 'automatorwp-asana' ),
            'edit_label'    => sprintf( __( 'Create task %1$s in project %2$s', 'automatorwp-asana' ), '{name}', '{project}' ),
            'log_label'     => sprintf( __( 'Created task %s in Asana', 'automatorwp-asana' ), '{name}' ),
            'options'       => array(

                'name' => array(
                    'name'     => __( 'Task Name:', 'automatorwp-asana' ),
                    'type'     => 'text',
                    'required' => true,
                ),

                'project' => array(
                    'name'       => __( 'Project:', 'automatorwp-asana' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_asana_options_cb_projects',
                    'required'   => true,
                ),

                'description' => array(
                    'name' => __( 'Description:', 'automatorwp-asana' ),
                    'type' => 'textarea',
                ),

                'assignee' => array(
                    'name'       => __( 'Assignee:', 'automatorwp-asana' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_asana_options_cb_users',
                ),
            ),
        ) );
    }

    /**
     * Executes the "Create Task in Asana" action.
     *
     * @since 1.0.0
     *
     * @param array $action         The action data from the database.
     * @param int   $user_id        The ID of the user that triggered the automation.
     * @param array $action_options The selected options for this action.
     * @param array $automation     The automation object.
     * @return void
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $name         = sanitize_text_field( $action_options['name'] );
        $project      = sanitize_text_field( $action_options['project'] );
        $description  = sanitize_textarea_field( $action_options['description'] );
        $assignee     = sanitize_text_field( $action_options['assignee'] );

        if ( empty( $project ) || empty( $name ) ) {
            return;
        }

        $args = array(
            'name'     => $name,
            'notes'    => $description,
            'projects' => array( $project ),
        );

        if ( ! empty( $assignee ) ) {
            $args['assignee'] = $assignee;
        }

        // Use the centralized API function.
        $result = automatorwp_asana_create_task( $args );

        if ( is_wp_error( $result ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'AutomatorWP Asana Error: ' . $result->get_error_message() );
        }
    }

}

// Instantiate the action class.
new AutomatorWP_Asana_Create_Task();
