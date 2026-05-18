<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create task
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Create_Task
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_Create_Task extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_create_task';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create task in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Create <strong>task</strong> in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Create task %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Create task %1$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'task_name' => array(
                    'name'       => __( 'Task name:', 'automatorwp-fluentboards' ),
                    'type'       => 'text',
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
        $this->result = __( 'Task created (demo).', 'automatorwp-fluentboards' );
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
    new AutomatorWP_FluentBoards_Create_Task();
});
