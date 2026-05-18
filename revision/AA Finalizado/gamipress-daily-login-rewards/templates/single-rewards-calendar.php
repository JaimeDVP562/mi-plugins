<?php
/**
 * Single Rewards Calendar template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/daily_login_rewards/single-rewards-calendar.php
 */
global $gamipress_daily_login_rewards_template_args;

// Shorthand
$a = $gamipress_daily_login_rewards_template_args; ?>

<div id="gamipress-rewards-calendar-<?php the_ID(); ?>" class="single-gamipress-rewards-calendar">

    <?php
    /**
     * Before single rewards calendar
     *
     * @param $rewards_calendar_id  integer The rewards calendar ID
     * @param $template_args        array   Template received arguments
     */
    do_action( 'gamipress_before_single_rewards_calendar', get_the_ID(), $a ); ?>

    <?php // Rewards Calendar content
    if( isset( $a['original_content'] ) ) :
        echo wpautop( $a['original_content'] );
    endif; ?>

    <?php
    /**
     * After single rewards calendar content
     *
     * @param $rewards_calendar_id  integer The rewards calendar ID
     * @param $template_args        array   Template received arguments
     */
    do_action( 'gamipress_after_single_rewards_calendar_content', get_the_ID(), $a ); ?>

    <?php
    // Setup the rewards calendar
    $rewards_calendar = new GamiPress_Rewards_Calendar( get_the_ID() );

    // Display the rewards calendar
    $rewards_calendar->display();
    ?>

    <?php
    /**
     * After single rewards calendar
     *
     * @param $rewards_calendar_id  integer The rewards calendar ID
     * @param $template_args        array   Template received arguments
     */
    do_action( 'gamipress_after_single_rewards_calendar', get_the_ID(), $a ); ?>

</div>
