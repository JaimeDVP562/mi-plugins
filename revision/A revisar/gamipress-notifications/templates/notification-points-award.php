<?php
/**
 * Points Award Notification template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/notifications/notification-points-award.php
 * To override a specific achievement type just copy it as yourtheme/gamipress/notifications/notification-points-award-{points-type}.php
 */
global $gamipress_notifications_template_args;

// Shorthand
$a = $gamipress_notifications_template_args; ?>

<div id="gamipress-points-award-<?php the_ID(); ?>-<?php echo $a['earning']->user_earning_id; ?>" class="gamipress-notification-points-award gamipress-notification-points-type-<?php echo $a['points_type']->post_name; ?>">

    <?php
    /**
     * Before render the points awards notification
     *
     * @since 1.0.0
     *
     * @param integer $points_award_id  The points award ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_before_render_points_award_notification', get_the_ID(), $a ); ?>

    <?php // Already parsed notification title ?>
    <?php if( ! empty( $a['notification_title'] ) ) : ?>
        <h2 class="gamipress-notification-title gamipress-notification-points-award-title"><?php echo $a['notification_title']; ?></h2>
    <?php endif; ?>

    <?php // The points award ?>
    <div class="gamipress-notification-description gamipress-notification-points-award-description">
        <?php echo $a['notification_content']; ?>
    </div>

    <?php
    /**
     * After render the points awards notification
     *
     * @since 1.0.0
     *
     * @param integer $points_award_id  The points award ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_after_render_points_award_notification', get_the_ID(), $a ); ?>

</div>
