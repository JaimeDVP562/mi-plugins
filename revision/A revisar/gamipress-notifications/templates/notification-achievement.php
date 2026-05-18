<?php
/**
 * Achievement Notification template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/notifications/notification-achievement.php
 * To override a specific achievement type just copy it as yourtheme/gamipress/notifications/notification-achievement-{achievement-type}.php
 */
global $gamipress_notifications_template_args;

// Shorthand
$a = $gamipress_notifications_template_args; ?>

<div id="gamipress-achievement-<?php the_ID(); ?>-<?php echo $a['earning']->user_earning_id; ?>" class="gamipress-notification-achievement gamipress-notification-achievement-type-<?php echo gamipress_get_post_type( get_the_ID() ); ?>">

    <?php
    /**
     * Before render the achievement notification
     *
     * @since 1.0.0
     *
     * @param integer $achievement_id   The Achievement ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_before_render_achievement_notification', get_the_ID(), $a ); ?>

    <?php // Already parsed notification title ?>
    <?php if( ! empty( $a['notification_title'] ) ) : ?>
        <h2 class="gamipress-notification-title gamipress-notification-achievement-title"><?php echo $a['notification_title']; ?></h2>
    <?php endif; ?>

    <?php // The achievement ?>
    <div class="gamipress-notification-description gamipress-notification-achievement-description">
        <?php echo gamipress_render_achievement( get_the_ID(), $a['template_args'] ) ?>
    </div>

    <?php // The configurable extra content ?>
    <?php if( ! empty( $a['notification_content'] ) ) : ?>
        <div class="gamipress-notification-extra-description gamipress-notification-achievement-extra-description">
            <?php echo $a['notification_content']; ?>
        </div>
    <?php endif; ?>

    <?php
    /**
     * After render the achievement notification
     *
     * @since 1.0.0
     *
     * @param integer $achievement_id   The Achievement ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_after_render_achievement_notification', get_the_ID(), $a ); ?>

</div>