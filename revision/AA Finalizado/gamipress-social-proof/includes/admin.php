<?php
/**
 * Admin
 * 
 * @author GamiPress
 * @since 1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * CMB2 MetaBoxes 'Fomo Settings'
 * 
 * @since 1.0.0
 * @return void
 */
function gamipress_social_proof_fomo_cmb_boxes( ) {
// Menu section (settings FOMO / Ajustes FOMO)
    $cmb = new_cmb2_box([
        'id'    => 'fomo_settings',
        'title' => __('Settings FOMO', GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
        'object_types' => ['options-page'],
        'option_key' => 'fomo_settings',
        'menu_title' => __('Settings FOMO',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
        'parent_slug' => 'gamipress'
    ]);

        // Options Field with (All / Logged-in Users / Guests)
        $cmb->add_field([
            'name'    => __('Show notifications to',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
            'id'      => 'show_to',
            'type'    => 'select',
            'options' => [
                'all'     =>  __('All',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
                'logged'  =>  __('Logged-in Users',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
                'guests'  =>  __('Guests',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN)
            ], 'default' => 'all',
        ]);

        // Repeatable group with th text ( User/Usuario # ) and the buttons ( Add user/Añadir usuario ) and ( Remove User/Borrar Usuario )
        $group_id = $cmb->add_field([
            'id'         => 'fomo_users',
            'type'       => 'group',
            'repeatable' => true,
            'options'    => [
                'group_title'   => __('User',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN).'{#}',
                'add_button'    => __('Add user',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
                'remove_button' => __('Remove User',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN)
            ]
        ]);

            //input (Name / Nombre)
            $cmb->add_group_field($group_id,[
                'name' => __('Name',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
                'id'   => 'name',
                'type' => 'text'
            ]);
            //input (Avatar)
            $cmb->add_group_field($group_id,[
                'name' => __('Avatar',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
                'id'   => 'avatar',
                'type' => 'file'
            ]);
}
add_action('cmb2_admin_init', 'gamipress_social_proof_fomo_cmb_boxes');

/**
 * GamiPress Social Proof Settings Notices
 * 
 * @since 1.0.0
 * @return void
 */
function gamipress_social_proof_fomo_settings_notice(){
   // Control settings notices
    $screen = get_current_screen();
    if($screen->id !== 'gamipress_page_fomo_settings') return;


    if(!gamipress_social_proof_fomo_detect_creations()){ // No creation all requeriments ?>
    <div class="notice notice-error">
        <p><?php _e("For FOMO to function properly, you must have at least one rank, one achievement, and one points type created in GamiPress.",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN)?></p>
    </div>
 <?php }else{ // Success check created ( Rango,puntos,logro )  ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e("Great news! FOMO is now working perfectly.",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN)?></p>
    </div>
<?php } // Info for greater clarity ?>
     <div class="notice notice-info is-dismissible">
            <p><?php _e("If a user does not have an avatar, the message is still displayed, but not in the recommended way.",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN) ?></p>
    </div>
<?php 

      // If WordPress save
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e("Your FOMO settings have been successfully saved.", GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'gamipress_social_proof_fomo_settings_notice');



/**
 * User creations detection
 * 
 * @since 1.0.0
 * @return bool True if Achievements, ranks, points types is created  
 */ 
function gamipress_social_proof_fomo_detect_creations(){
    $achievements = gamipress_get_achievements();
    $ranks       = gamipress_get_ranks();
    $points      = gamipress_get_points_types();

    if(empty($achievements) || empty($ranks) || empty($points)){
        return false;
    }else{
        return true;
    }

}

/**
 * Menu section Fomo messages shown history
 * 
 * @since 1.0.0
 * @return void
 */
function gamipress_social_proof_set_menu_fomo_history(){
    add_menu_page(
        __('FOMO History',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),        // Page title
        __('FOMO History',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),        // Menu text
        'manage_options',                                   
        'fomo-history',                                     // Menu slug
        'gamipress_social_proof_fomo_historial_page',       // Shown function
        'dashicons-list-view',                              // Menu icon
        25                                                  // Menu position
    );

}
add_action('admin_menu', 'gamipress_social_proof_set_menu_fomo_history');

/**
 * History menu section table created
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_social_proof_fomo_historial_page() { 
    
    if(isset($_POST['delete_history'])){
        delete_option('fomo_display_history');
    }

    $history = get_option('fomo_display_history',[]);


    $shown_to_labels = [
    'all'    => __('All', GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
    'guests'  => __('Guests', GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
    'logged' => __('logged', GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN),
];

    ?>
    <div class="wrap">
     <h1><?php _e('FOMO History','gamipress-social-proof')?></h1>
     <p><?php  _e('Here is the history of messages shown to users',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN) ?></p>
    </div>
        <form method="post">
        <input type="hidden" name="delete_history" value="1">
        <button class="button button-danger"><?php _e('Delete History',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN) ?></button>
     </form>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('User Shown',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN) ?></th>
                <th><?php _e('Message Shown',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN) ?></th>
                <th><?php _e('Time Shown',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN) ?></th>
                <th><?php _e('Shown To',GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN) ?></th>
            </tr>
        </thead>

        <tbody>
            
                <?php

                foreach($history as $event){ 
                       $shown_to= $event['show_to'];
                    ?>
                    <tr> 
                        <td><?php echo esc_html($event['user']) ?></td>
                        <td><?php echo esc_html($event['message']) ?></td>
                        <td><?php echo esc_html($event['time']) ?></td>
                        <td><?php echo isset($shown_to_labels[$shown_to])? $shown_to_labels[$shown_to] : esc_html($shown_to)  ?></td>
                    </tr>
               <?php } ?>   
            
        </tbody>

    </table>

<?php }