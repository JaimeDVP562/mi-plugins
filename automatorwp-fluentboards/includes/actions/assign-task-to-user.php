<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Assign task to user
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Assign_Task_To_User
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_Assign_Task_To_User extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_assign_task_to_user';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Assign task to user in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Assign <strong>task</strong> to user in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Assign task %1$s to user %2$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Assign task %1$s to user %2$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'task_id' => array(
                    'name'       => __( 'Task:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_tasks',
                    'required'   => true,
                ),
                'user_id' => array(
                    'name'       => __( 'User:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_users',
                    'required'   => true,
                ),
            ),
        ));
    }

    /**
     * Execute the action
     *
     * @since 1.0.0
     *
     * @param stdClass $action
     * @param int $user_id
     * @param array $action_options
     * @param stdClass $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        // Aquí iría la lógica real de asignar la tarea
        $this->result = __( 'Task assigned (demo).', 'automatorwp-fluentboards' );
    }

    /**
     * Hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        parent::hooks();
    }
}

add_action( 'automatorwp_init', function() {
    new AutomatorWP_FluentBoards_Assign_Task_To_User();
});
