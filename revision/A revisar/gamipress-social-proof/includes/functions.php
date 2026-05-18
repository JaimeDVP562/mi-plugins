<?php
/**
 * Functions
 * 
 * @author GamiPress
 * @since  1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

    /**
     * Function manages visivility notifications
     * 
     * @since 1.0.0
     * @param string $show_to contains 'logged', 'guests', 'all'
     * @return bool True if coincide with conditions
     */
    function gamipress_social_proof_fomo_should_show_notification( $show_to ) {
            if($show_to === "logged" && is_user_logged_in()){
                return true;
            }else if($show_to === "guests" && !is_user_logged_in()){
                return true;
            }else if ($show_to === 'all'){
                return true;
            }else{
                return false;
            }
        }


    /**
     * General function to show the fomo message
     * 
     * @since 1.0.0
     * @return null|string Body message with content
     */
    function gamipress_social_proof_fomo_general_function( ) {
    // Points
    $points_types = gamipress_get_points_types();

    // Fake users
    $options = get_option('fomo_settings');
    $fomo_users = $options['fomo_users']?? 0;
    
    // People that can see messages
    $show_to = $options['show_to'] ?? 'all';
    
    // Save random amount
    $amount=gamipress_social_proof_fomo_random_amount();
    
    // Control plural and singular
    if (empty($points_types)) {
    $points_type_name = "";
    } else {
    $first_points_type = reset($points_types);
    $points_type_name = ($amount == 1)
        ? $first_points_type['singular_name']
        : $first_points_type['plural_name'];
    }

    //  Check there is FOMO Users
    if(empty($fomo_users)) return;
    //
    // If not shown messages exit
    if(!gamipress_social_proof_fomo_should_show_notification($show_to))return;
    // Chose single candidate for shown
    $random_user = $fomo_users[array_rand($fomo_users)];

    // We collect fake user vars 
    $name    = $random_user['name']?? __('User');
    $avatar  = !empty($random_user['avatar'])? $random_user['avatar']:plugin_dir_url(__FILE__) . 'assets/images/unknown-user.png';

   

    // We construct the message
    $body_message=gamipress_social_proof_fomo_message_constructor($name,$avatar,$points_type_name,$amount);

    return $body_message;

    }
     
    /**
     * Random amount generator for points type
     * 
     * @since 1.0.0
     * @return int $number
     */
    function gamipress_social_proof_fomo_random_amount(){
        $number=random_int(1000,100000);
        return $number;
        }

    /**
     * Random achievement generator
     * 
     * @since  1.0.0
     * @param  array $achievements_names  
     * @return string
     */
    function gamipress_social_proof_fomo_get_random_achievement($achievements_names){
        //Si no ha llegado achievements_names
        if(!$achievements_names)return false;

        $max=(count($achievements_names)-1);
        $candidate=random_int(0,$max);
        return $achievements_names[$candidate];
    }

    /**
     * Get all ranks created function
     * 
     * @since 1.0.0
     * @return array $ranks_data
     */
   function gamipress_social_proof_fomo_get_all_ranks(){
    $rank_types = gamipress_get_rank_types();
    $ranks_data = [];

    foreach($rank_types as $slug => $type){
        $ranks = gamipress_get_ranks([
            'rank_type' => $slug,
            'number'    => -1
        ]);

        foreach($ranks as $rank){

            
            $image = get_post_meta($rank->ID, '_gamipress_rank_image', true);

            $ranks_data[] = [
                'name'  => $rank->post_title,
                'image' => $image,
                'id'    => $rank->ID,
                'type'  => $slug
            ];
        }
    }

    return $ranks_data;
}

    /**
     * Get single random rank
     * 
     * @since 1.0.0
     * @param array $ranks Ranks storage
     * @return array Random candidate
     */
    function gamipress_social_proof_fomo_get_random_rank($ranks){
        // if there aren't ranks
        if(empty($ranks)){
            return false;
        }

        $max = count($ranks) - 1;

        if ($max < 0) {
        return false;
        }

        $candidate=random_int(0,$max);

        return $ranks[$candidate];
    }



    /**
     * Fomo random message generator
     * 
     * @since  1.0.0
     * @param  string $name Fomo user name
     * @param  string $rank Fomo random rank
     * @param  string $points_type_name Fomo random point type name
     * @param  int    $amount Fomo random amount for point
     * @param  string $achievement Fomo random achivement
     * @return string Message candidate 
     */
    function gamipress_social_proof_fomo_get_random_message($name,$rank,$points_type_name,$amount,$achievement){
    // Error filter
    if (!$rank || empty($rank['name']) || (!$points_type_name || !$amount)|| empty($points_type_name) || !$achievement || empty($achievement) ) {
        return "<span class='name'>$name</span> ".__("just won an award",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN);
    }

    /*---------- MESSAGES ----------*/
   /* Default Message --------------*/$message1="<span class='name'>$name</span> ".__("just won an award",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN);
   /* Unlocked rank Message --------*/$message2="<span class='name'>$name</span> ".__("unlocked",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN)." <span class='rank'>".$rank['name']."</span>";
   /* Won Points Message -----------*/$message3="<span class='name'>$name</span> ".__("won",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN)." <span class='points'>$amount $points_type_name</span>";
   /* Unlocked Achievement Message -*/$message4="<span class='name'>$name</span> ".__("unlocked",GAMIPRESS_SOCIAL_PROOF_TEXT_DOMAIN)." <span class='achievement'>$achievement</span>";

    $messages=[$message1,$message2,$message3,$message4];

    $candidate=random_int(0,(count($messages)-1));
    return $messages[$candidate];
    
    } 

  /**
   * Fomo message constructor
   * 
   * @since  1.0.0
   * @param string $name fomo user name
   * @param string $avatar fomo user avatar
   * @param string $points_type_name Fomo random points type name
   * @param int $amount Fomo random amount for points
   * @return string $body_message if created cookie
   */
   function gamipress_social_proof_fomo_message_constructor($name,$avatar,$points_type_name,$amount){

   // Get achievement names
    $achievements = gamipress_get_achievements();
    $achievements_names = wp_list_pluck($achievements,'post_title');
   // Get random achievement 
    $achievement = gamipress_social_proof_fomo_get_random_achievement($achievements_names);

    // Get all ranks
    $ranks = gamipress_social_proof_fomo_get_all_ranks();
    
    // If ther aren't ranks, nothing to show
    if (empty($ranks)) return;



    // Get random rank
    $rank = gamipress_social_proof_fomo_get_random_rank($ranks);

    // Si no hay rango
    if (!$rank) {
        return;
    }

    // Create and choose single random message
    $message = gamipress_social_proof_fomo_get_random_message($name, $rank,$points_type_name,$amount,$achievement);

    // If there isn't message, nothing to show
    if (!$message) {
        return;
    }

    // Image and name rank
    $image_rank = $rank['image'] ?: "";
    $rank_name  = $rank['name']  ?: "";

    $img_rank_html = ($image_rank)
    ? "<img src='$image_rank' alt='$rank_name'>"
    : "";
    // HTML construction
    $body_message = "<div class='fomo-message' id='message-fomo'>
                    <img class='avatar' src='$avatar'>
                    <p>$message</p>
                    $img_rank_html
                    </div>";

    // We create a COOKIE that control the shown messages. Duration Cookie: 10 minutes                   
    if(!isset($_COOKIE['fomo_shown'])){
        setcookie('fomo_shown',0,time()+(60*10),"/"); 
    }else{
        if($_COOKIE['fomo_shown']<3){
        $fomo_shown = $_COOKIE['fomo_shown']+1;
        setcookie('fomo_shown',$fomo_shown,time()+(60*10),"/");
        }
    }
        // Shown message control 
        if(isset($_COOKIE['fomo_shown']) && $_COOKIE['fomo_shown']<3){
        $options = get_option('fomo_settings');
        $show_to = $options['show_to'] ?? 'all';
        gamipress_social_proof_fomo_register_display_event($name,$message,$show_to);
        return $body_message;
        }else{
            return "";
        }
    }

    /**
     * Save fomo historial messages
     * 
     * @since 1.0.0
     * @param string $user_name 
     * @param string $message
     * @param string $show_to
     * @return void
     */
    function gamipress_social_proof_fomo_register_display_event($user_name,$message,$show_to){

        // Select DB zone
        $history = get_option('fomo_display_history',[]);

        // We fill $history
        $history[] = [
            'user'   => $user_name,
            'message'=> $message,
            'show_to'=> $show_to,
            'time'   => current_time('mysql')
        ];

        // Update history to DB
        update_option('fomo_display_history',$history);
        
    }