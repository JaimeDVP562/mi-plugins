<?php

/**
 * Action:Create GitHub repository
 * 
 * @since 1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Github_Create_Repo_Action extends AutomatorWP_Integration_Action{

    public $integration = 'github';
    public $action = 'github_create_repo';

    /**
     * Register labels to custom this action
     * 
     * @since 1.0.0
     */
    public function register(){
       automatorwp_register_action($this->action, array(
                        'integration'   => $this->integration,
                        'label'         => __('Create a new repository', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                        'select_option' => __('Create a <strong>new repository</strong>', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                        'edit_label'    => sprintf(__('Create new repository %1$s , %2$s , %3$s'), '{repo_name}','{description}','{private}'),
                        'log_label'     => sprintf(__('Created new repository %1$s',AUTOMATORWP_GITHUB_TEXT_DOMAIN), '{repo_name}'),
                        'options' => array(
                            'repo_name' => array(
                                'from' => 'repo_name',
                                'default' => '',
                                'fields' => array( 
                                   'repo_name' => array (
                                                    'label'       => __('Repository Name', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                    'type'        => 'text',
                                                    'default'     => 'Repository name',
                                                    'description' => __('The repository name', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                    'required'    => true,
                                                     ) )
                                                    ),
                            'description' => array(
                                'from' => 'description',
                                'default' => '',
                                'fields' => array( 
                                    'description' => array(
                                                    'label'       => __('Description', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                    'type'        => 'text',
                                                    'default'     => 'Description',
                                                    'description' => __('Short description of the repository', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                    ) )
                                                    ),
                            'private' => array(
                                'from' => 'private',
                                'default' => '',
                                'fields'  => array(
                                    'private' => array(
                                                'label'       => __('Private repository', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                'type'        => 'checkbox',
                                                'default'     => '',
                                                'description' => __('Create private repository?', AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                                ) ) ),

                        ),

                    ));

    }

    /**
     * Execute Automator_Github_Create_Repo_Action
     * 
     * @since 1.0.0
     */
    public function execute($action, $user_id, $action_options, $automation)
    {
       //Get GitHub Repository name
       $repo_name   = $action_options['repo_name'];
       //Get GitHub Repository description
       $description = $action_options['description'];
       //Get GitHub Repository privacy
       $private     = (!empty($action_options['private']) && $action_options['private'] === 'on' )? true : false;

        // Create GitHub repository function
         $result = automatorwp_github_action_create_repo($repo_name,$description,$private);
         
         // Result LOG
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

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
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
    new AutomatorWP_Github_Create_Repo_Action();
});
