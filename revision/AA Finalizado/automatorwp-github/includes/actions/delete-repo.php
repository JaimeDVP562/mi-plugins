<?php

/**
 * Action:Create GitHub repository
 * 
 * @since 1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Github_Delete_Repo_Action extends AutomatorWP_Integration_Action{

    public $integration = 'github';
    public $action = 'github_delete_repo';


    /**
     * Register labels to custom this action
     * 
     * @since 1.0.0
     */
    public function register(){

       automatorwp_register_action($this->action, array(
                        'integration'   => $this->integration,
                        'label'         => __('Delete a repository', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                        'select_option' => __('Delete a <strong>repository</strong>', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                        'edit_label'    => sprintf(__('Delete repository %1$s'), '{repo_name}'),
                        'log_label'     => sprintf(__('Deleted repository %1$s',AUTOMATORWP_GITHUB_TEXT_DOMAIN), '{repo_name}'),
                        'options' => array(
                            'repo_name' => array(
                                'from' => 'repo_name',
                                'default' => '',
                                'fields' => array( 
                                   'repo_name' => array (
                                                    'label'       => __('Repository Name', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                    'type'        => 'text',
                                                    'default'     => 'Repository name',
                                                    'description' => __('The repository name to delete', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                    'required'    => true,
                                                     ) )
                                                 ),
                                            ),

                    ));

    }

    /**
     * Execute Automator_Github_Delete_Repo_Action
     * 
     * @since 1.0.0
     */
    public function execute($action, $user_id, $action_options, $automation)
    {

       $repo_name   = $action_options['repo_name'];
      
       

         $result = automatorwp_github_action_delete_repo($repo_name);
         
         $this->result = $result;

         if( $result['success'] === 'No' ) {
            return false;
            }
            return true;
        }


 


    /**
     * Register required hooks
     * 
     * @since 1.0.0
     */
    public function hooks(){
       // Configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

        public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store the action's result
        $log_meta['result'] = $this->result;

        return $log_meta;
    }
    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param mixed $item  The trigger/action object
     */
    public function configuration_notice( $item, $automation_id ) {

    // Solo mostrar el aviso en esta acción
    if( $item->type !== $this->action ) {
        return;
    }

    echo '<div class="automatorwp-notice">';
    echo '<p>' . __( 'Remember to configure your GitHub token before using this action.', AUTOMATORWP_GITHUB_TEXT_DOMAIN ) . '</p>';
    echo '</div>';
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

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
            'type' => 'text',
        );
        return $log_fields;
    }
}
add_action( 'automatorwp_init', function() {
    new AutomatorWP_Github_Delete_Repo_Action();
});