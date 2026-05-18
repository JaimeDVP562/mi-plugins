<?php

/**
 * Admin
 * 
 * @package  AutomatorWP\GitHub\Ajax_Functions
 * @author   AutomatorWP
 * @since    1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 * 
 * @since 1.0.0
 * @param string  $option_name The option to search
 * @param string  $prefix  prefix GitHub integration
 * 
 * @return mixed
 */
function automatorwp_github_get_option($opcion_name, $default=false){
    
    $prefix = 'automatorwp_github_';

    return automatorwp_get_option($prefix . $opcion_name, $default );
}


/**
 * Register plugin in settings sections
 * 
 * @since  1.0.0
 * 
 * @return array
 */
function automatorwp_github_settings_sections($automatorwp_settings_sections){

    $automatorwp_settings_sections['github'] = array(
        'title' => 'GitHub',
        'icon'  => 'dashicons-github'
    );
    
    return $automatorwp_settings_sections;

}
add_filter('automatorwp_settings_sections','automatorwp_github_settings_sections');

/**
 * Register plugin settings meta boxes
 * 
 * @since   1.0.0
 * 
 * @return  array $meta_boxes
 */
function automatorwp_github_settings_meta_boxes($meta_boxes){
    $prefix = 'automatorwp_github_';

    $meta_boxes['automatorwp-github-settings'] = array(
        
        'title'  => automatorwp_dashicon('github').'GitHub',
        'fields' => apply_filters('automatorwp_github_settings_fields', array(
            
            $prefix . 'username' => array(
                'name' => __('GitHub Username',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'desc' =>sprintf(__('Your GitHub Username.',AUTOMATORWP_GITHUB_TEXT_DOMAIN)),
                'type' => 'text'
            ),
            $prefix . 'key' => array(
                'name' => __('API Key (PAT):',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'desc' => sprintf(__('Your Github API key PAT (Personal Access Token).'),AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'type' => 'text'
            ),
            $prefix . 'webhook_token' => array(
                'name'  => __('Webhook Secret Token:',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'desc'  => __('Secret token used to validate GitHub webhooks.',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
                'type'  => 'text'
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_github_authorize_display_cb'
            )
        
        )
         ) 
          );

          return $meta_boxes;
}
add_filter('automatorwp_settings_github_meta_boxes','automatorwp_github_settings_meta_boxes');



/**
 * Display callback for the GitHub authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_github_authorize_display_cb( $field_args, $field ) {
    // Field ID
    $field_id = $field_args['id'];

    $username = automatorwp_github_get_option('username','');
    $key = automatorwp_github_get_option('key','');
    $webhook  =  automatorwp_github_get_option('webhook_token','');

    ?>

    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-github-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with GitHub:', AUTOMATORWP_GITHUB_TEXT_DOMAIN ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Save credentials', AUTOMATORWP_GITHUB_TEXT_DOMAIN ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you GitHub Username, GitHub API key (PAT) and  your WebHook key then click on "Authorize" to connect.', AUTOMATORWP_GITHUB_TEXT_DOMAIN ); ?></p>
            <?php if ( ! empty( $username ) && ! empty( $key ) && ! empty( $webhook ) ) { ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site prepared to connect with GitHub (Save) for check de connection.', AUTOMATORWP_GITHUB_TEXT_DOMAIN ); ?></div>
            <?php } ?>
        </div>    
    </div>
  
    <?php
}




