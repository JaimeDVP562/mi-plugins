<?php
/**
 * Trigger: New GitHub Issue Created
 * 
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;




class AutomatorWP_Github_New_Issue_Trigger extends AutomatorWP_Integration_Trigger {

    public $integration = 'github';
    public $trigger = 'github_new_issue';

    /**
     * Register labels to custom this trigger
     * 
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, 
            array(
                    'integration'   => $this->integration,
                    'anonymous'     => true,
                    'label'         => __( 'A new issue is created', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                    'select_option' => __( 'A new issue is created', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                    'edit_label'    => __( 'A new issue is created', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                    'log_label'     => __( 'A new issue was created', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                    'action'        => 'automatorwp_github_new_issue',
                    'function'      => array( $this, 'listener' ),
                    'priority'      => 10,
                    'accepted_args' => 1,
                    'tags'         => array(
                                                'action' => array(
                                                    'label' => __( 'Action', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                                                    'type'  => 'text'
                                                ),
                                                'repository_name' => array(
                                                    'label' => __( 'Repository name', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                                                    'type'  => 'text'
                                                ),
                                                'issue_title' => array(
                                                    'label' => __( 'Issue title', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                                                    'type'  => 'text'
                                                ),
                                                'issue_url' => array(
                                                    'label' => __( 'Issue URL', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                                                    'type'  => 'text'
                                                ),
                                                'created_at' => array(
                                                    'label' => __( 'Created at', AUTOMATORWP_GITHUB_TEXT_DOMAIN ),
                                                    'type'  => 'text'
                                                )
                                )


                ));

    }

    /**
     * Listen Automator_Github_New_Issue_Trigger
     * 
     * @since 1.0.0
     */
    public function listener( $event ) {
        

        error_log(print_r($event,true));

        automatorwp_trigger_event(
                array(
                    'trigger' => $this->trigger,
                    'user_id'=> 0,
                    'meta'    => array(
                        'action'           => $event['action'],
                        'issue_title'      => $event['issue']['title'],
                        'issue_url'        => $event['issue']['url'],
                        'repository_name'  => $event['repository']['full_name'],
                        'created_at'       => $event['issue']['created_at']
                    )
                )
);



    }

}
new AutomatorWP_Github_New_Issue_Trigger();
