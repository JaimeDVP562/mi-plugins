<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create board
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Create_Board
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_Create_Board extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_create_board';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create board in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Create <strong>board</strong> in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Create board %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Create board %1$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'board_name' => array(
                    'name'       => __( 'Board name:', 'automatorwp-fluentboards' ),
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
        $this->result = __( 'Board created (demo).', 'automatorwp-fluentboards' );
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
    new AutomatorWP_FluentBoards_Create_Board();
});
