<?php
/**
 * Create list
 *
 * @package     AutomatorWP\Integrations\Klaviyo\Actions\Create_List
 * @author      AutomatorWP <contact@automatorwp.com>, Irene Rodenas <irener.rglez@gmail.com>
 * @since       1.0.0
 */
 
 // Exit if accessed directly
 if (!defined('ABSPATH')) exit;
 
 class AutomatorWP_Klaviyo_Create_List extends AutomatorWP_Integration_Action {
     public $integration = 'klaviyo';
     public $action = 'klaviyo_create_list';
     public $result;
 
     /**
      * Register the action
      *
      * @since 1.0.0
      */
     public function register() {
         automatorwp_register_action(
             $this->action,
             array(
                 'integration' => $this->integration,
                 'label' => __('Create list', 'automatorwp-klaviyo'),
                 'select_option' => __('Create <strong>list</strong>', 'automatorwp-klaviyo'),
                 'edit_label' => sprintf(__('Create %1$s', 'automatorwp-klaviyo'), '{list}'),
                 'log_label' => sprintf(__('Create %1$s', 'automatorwp-klaviyo'), '{list}'),
                 'options' => array(
                     'list' => array(
                         'from' => 'list',
                         'default' => __('list', 'automatorwp-klaviyo'),
                         'fields' => array(
                             'folder' => automatorwp_utilities_ajax_selector_field(
                                 array(
                                     'name' => __('Folder:', 'automatorwp-klaviyo'),
                                     'option_none' => false,
                                     'required' => false,
                                     'action_cb' => 'automatorwp_klaviyo_get_folders',
                                     'options_cb' => 'automatorwp_klaviyo_options_cb_folder',
                                     'placeholder' => 'Select a folder',
                                     'default' => ''
                                 )
                             ),
                             'name' => array(
                                 'name' => __('List name:', 'automatorwp-klaviyo'),
                                 'desc' => __('The list name.', 'automatorwp-klaviyo'),
                                 'type' => 'text',
                                 'required' => true,
                                 'default' => ''
                             )
                         ),
                     ),
                 )
             )
         );
     }
 
     /**
      * Action execution function
      *
      * @since 1.0.0
      *
      * @param stdClass  $action             The action object
      * @param int       $user_id            The user ID
      * @param array     $action_options     The action's stored options (with tags already passed)
      * @param stdClass  $automation         The action's automation object
      */
     public function execute($action, $user_id, $action_options, $automation) {
         $folder_id = isset($action_options['folder']) ? $action_options['folder'] : null;
         $list_name = $action_options['name'];
 
         // Bail if list name is empty
         if (empty($list_name)) {
             $this->result = __('List name field is empty.', 'automatorwp-klaviyo');
             return;
         }
 
         $this->result = '';
 
         // Bail if Klaviyo is not configured
         if (!automatorwp_klaviyo_get_api()) {
             $this->result = __('Klaviyo integration not configured in AutomatorWP settings.', 'automatorwp-klaviyo');
             return;
         }
 
         $secret = automatorwp_klaviyo_get_api()['secret'];
         $response = automatorwp_klaviyo_create_list($list_name, $folder_id, $secret);
 
         if ($response === 201) {
             $this->result = sprintf(__('List %s created', 'automatorwp-klaviyo'), $list_name);
         } else {
             $this->result = __('The list could not be created', 'automatorwp-klaviyo');
         }
     }
 
     /**
      * Register required hooks
      *
      * @since 1.0.0
      */
     public function hooks() {
         // Configuration notice
         add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);
 
         // Log meta data
         add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
 
         // Log fields
         add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);
 
         parent::hooks();
     }
 
     /**
      * Configuration notice
      *
      * @since 1.0.0
      *
      * @param stdClass  $object     The trigger/action object
      * @param string    $item_type  The object type (trigger|action)
      */
     public function configuration_notice($object, $item_type) {
         // Bail if action type doesn't match this action
         if ($item_type !== 'action' || $object->type !== $this->action) {
             return;
         }
 
         // Warn user if the authorization has not been set up from settings
         if (!automatorwp_klaviyo_get_api()): ?>
             <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                 <?php echo sprintf(
                     __('You need to configure the <a href="%s" target="_blank">Klaviyo settings</a> to get this action to work.', 'automatorwp-klaviyo'),
                     get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-klaviyo'
                 ); ?>
                 <?php echo sprintf(
                     __('<a href="%s" target="_blank">Documentation</a>', 'automatorwp-klaviyo'),
                     'https://automatorwp.com/docs/klaviyo/'
                 ); ?>
             </div>
         <?php endif;
     }
 
     public function log_meta($log_meta, $action, $user_id, $action_options, $automation) {
         if ($action->type !== $this->action) {
             return $log_meta;
         }
 
         $log_meta['result'] = $this->result;
 
         return $log_meta;
     }
 
     public function log_fields($log_fields, $log, $object) {
         if ($log->type !== 'action' || $object->type !== $this->action) {
             return $log_fields;
         }
 
         $log_fields['result'] = array(
             'name' => __('Result:', 'automatorwp-klaviyo'),
             'type' => 'text',
         );
 
         return $log_fields;
     }
 }
 
 new AutomatorWP_Klaviyo_Create_List();
 