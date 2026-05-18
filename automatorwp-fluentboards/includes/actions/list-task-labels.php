<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * List task labels
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\List_Task_Labels
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_List_Task_Labels extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_list_task_labels';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'List task labels in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'List <strong>task labels</strong> in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'List labels for task %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'List labels for task %1$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'task_id' => array(
                    'name'       => __( 'Task:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_tasks',
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
        $this->result = __( 'Task labels listed (demo).', 'automatorwp-fluentboards' );
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
    new AutomatorWP_FluentBoards_List_Task_Labels();
});
