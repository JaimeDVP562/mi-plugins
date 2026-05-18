<?php
/**
 * Rewards Calendar template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/daily_login_rewards/rewards-calendar.php
 */
global $gamipress_daily_login_rewards_template_args;

// Shorthand
$a = $gamipress_daily_login_rewards_template_args; ?>

<div id="gamipress-rewards-calendar-<?php the_ID(); ?>" class="gamipress-rewards-calendar">

    <?php
    /**
     * Before render rewards calendar
     *
     * @param $rewards_calendar_id  integer The rewards calendar ID
     * @param $template_args        array   Template received arguments
     */
    do_action( 'gamipress_before_render_rewards_calendar', get_the_ID(), $a ); ?>

    <?php if( $a['title'] === 'yes' ) : ?>
        <h2 class="gamipress-rewards-calendar-title"><?php echo gamipress_get_post_field( 'post_title', get_the_ID() ); ?></h2>

        <?php
        /**
         * After rewards calendar title
         *
         * @param $rewards_calendar_id   integer The rewards calendar ID
         * @param $template_args    array   Template received arguments
         */
        do_action( 'gamipress_after_rewards_calendar_title', get_the_ID(), $a ); ?>
    <?php endif; ?>

    <?php // Rewards calendar Short Description
    if( $a['excerpt'] === 'yes' ) :  ?>
        <div class="gamipress-rewards-calendar-excerpt">
            <?php
            $excerpt = has_excerpt() ? gamipress_get_post_field( 'post_excerpt', get_the_ID() ) : gamipress_get_post_field( 'post_content', get_the_ID() );
            echo wpautop( apply_filters( 'get_the_excerpt', $excerpt, get_post() ) );
            ?>
        </div><!-- .gamipress-achievement-excerpt -->

        <?php
        /**
         * After rewards calendar excerpt
         *
         * @param $rewards_calendar_id  integer The rewards calendar ID
         * @param $template_args        array   Template received arguments
         */
        do_action( 'gamipress_after_rewards_calendar_excerpt', get_the_ID(), $a ); ?>
    <?php endif; ?>

    <?php

    $args = array(
        'columns'       => $a['columns'],
        'image_size'    => $a['image_size'],
    );

    // Setup the rewards calendar
    $rewards_calendar = new GamiPress_Rewards_Calendar( get_the_ID(), $args );

    // Display the rewards calendar
    $rewards_calendar->display();
    ?>

    <?php
    /**
     * After render rewards calendar
     *
     * @param $rewards_calendar_id  integer The rewards calendar ID
     * @param $template_args        array   Template received arguments
     */
    do_action( 'gamipress_after_render_rewards_calendar', get_the_ID(), $a ); ?>

</div>
