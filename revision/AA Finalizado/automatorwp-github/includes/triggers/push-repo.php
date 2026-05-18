<?php
/**
 * Trigger: Push repository
 * 
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;


class AutomatorWP_Github_Push_Repo_Trigger extends AutomatorWP_Integration_Trigger{

    public $integration = 'github';
    public $trigger = 'github-push-repo';


    /**
     * Register labels to custom this trigger
     * 
     * @since 1.0.0
     */
    public function register() {
        
        automatorwp_register_trigger( $this->trigger, 
        array(
                'integration'  => $this->integration,
                'anonymous'    => true,
                'label'        => __('New push event',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'select_option'=> __('New push event',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'edit_label'   => __('New push event',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'log_label'    => __('New push event detected',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'action'       => 'automatorwp_github_push_repo',
                'function'     => array( $this, 'listener' ),
                'tags'         => array(
                                    'ref' => array(
                                        'label'   => __('Reference',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'    => 'text',
                                        'preview' => __('The repository reference',AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    ),
                                    'repository_name'=> array(
                                        'label' => __('Repository name',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'  => 'text',
                                        'preview' => __('The repository name',AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    ),
                                    'visibility'=> array(
                                        'label'   => __('visibility',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'    => 'text',
                                        'preview' => __('The Repository visibility',AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    ),
                                    'svn_url'=> array(
                                        'label'   => __('Repository URL',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'    => 'text',
                                        'preview' => __('The repository link',AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    ),
                                    'added'=> array(
                                        'label'   => __('Added',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'    => 'text',
                                        'preview' => __('Added items in the repository',AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    ),
                                    'removed'=> array(
                                        'label'   => __('Removed',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'    => 'text',
                                        'preview' => __('Removed items in the repository',AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    ),
                                    'modified'=> array(
                                        'label'   => __('Modified' , AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'    => 'text',
                                        'preview' => __('Modified items in the repository' , AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    ),
                                    'updated_at'=> array(
                                        'label'   => __('Updated at' , AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                                        'type'    => 'text',
                                        'preview' => __('Last push date' , AUTOMATORWP_GITHUB_TEXT_DOMAIN)
                                    )
                                )
        ) );
    }

    /**
     * Listen Automator_Github_Push_Repo_Trigger
     * 
     * @since 1.0.0
     */
public function listener( $event ){
    $added = [];
    $removed = [];
    $modified = [];
if ( ! empty( $event['commits'] ) ) {

    foreach ( $event['commits'] as $commit ) {
        $added = array_merge( $added, $commit['added'] );
        $removed = array_merge( $removed, $commit['removed'] );
        $modified = array_merge( $modified, $commit['modified'] );
    }

}


    automatorwp_trigger_event(
        array(
            'trigger' => $this->trigger,
            'user_id' => 0,
            'meta'    => array(
                    'ref'             => $event['ref'] ,
                    'repository_name' => $event['repository']['full_name'],
                    'visibility'      => $event['repository']['visibility'],
                    'svn_url'         => $event['repository']['svn_url'],
                    'added'           => implode(', ',$added),
                    'removed'         => implode(', ',$removed),
                    'modified'        => implode(', ',$modified),
                    'updated_at'      => $event['repository']['updated_at']
            )
            )
    );
}

}
new AutomatorWP_Github_Push_Repo_Trigger();