<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove assignee from task
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Remove_Assignee_From_Task
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_Remove_Assignee_From_Task extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_remove_assignee_from_task';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Remove assignee from task in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Remove <strong>assignee</strong> from task in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Remove assignee from task %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Remove assignee from task %1$s', 'automatorwp-fluentboards' ),
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
        $this->result = __( 'Assignee removed from task (demo).', 'automatorwp-fluentboards' );
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
    new AutomatorWP_FluentBoards_Remove_Assignee_From_Task();
});

