<?php
/**
 * Points Deduct Notification template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/notifications/notification-points-deduct.php
 * To override a specific achievement type just copy it as yourtheme/gamipress/notifications/notification-points-deduct-{points-type}.php
 */
global $gamipress_notifications_template_args;

// Shorthand
$a = $gamipress_notifications_template_args; ?>

<div id="gamipress-points-deduct-<?php the_ID(); ?>-<?php echo $a['earning']->user_earning_id; ?>" class="gamipress-notification-points-deduct gamipress-notification-points-type-<?php echo $a['points_type']->post_name; ?>">

    <?php
    /**
     * Before render the points deducts notification
     *
     * @since 1.0.0
     *
     * @param integer $points_deduct_id The points deduct ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_before_render_points_deduct_notification', get_the_ID(), $a ); ?>

    <?php // Already parsed notification title ?>
    <?php if( ! empty( $a['notification_title'] ) ) : ?>
        <h2 class="gamipress-notification-title gamipress-notification-points-deduct-title"><?php echo $a['notification_title']; ?></h2>
    <?php endif; ?>

    <?php // The points deduct ?>
    <div class="gamipress-notification-description gamipress-notification-points-deduct-description">
        <?php echo $a['notification_content']; ?>
    </div>

    <?php
    /**
     * After render the points deducts notification
     *
     * @since 1.0.0
     *
     * @param integer $points_deduct_id The points deduct ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_after_render_points_deduct_notification', get_the_ID(), $a ); ?>

</div>
