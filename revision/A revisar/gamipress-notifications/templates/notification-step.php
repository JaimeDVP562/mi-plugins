<?php
/**
 * Step Notification template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/notifications/notification-step.php
 * To override a specific achievement type just copy it as yourtheme/gamipress/notifications/notification-step-{achievement-type}.php
 */
global $gamipress_notifications_template_args;

// Shorthand
$a = $gamipress_notifications_template_args; ?>

<div id="gamipress-step-<?php the_ID(); ?>-<?php echo $a['earning']->user_earning_id; ?>" class="gamipress-notification-step gamipress-notification-achievement-type-<?php echo $a['achievement']->post_type; ?>">

    <?php
    /**
     * Before render the step notification
     *
     * @since 1.0.0
     *
     * @param integer $step_id          The step ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_before_render_step_notification', get_the_ID(), $a ); ?>

    <?php // Already parsed notification title ?>
    <?php if( ! empty( $a['notification_title'] ) ) : ?>
        <h2 class="gamipress-notification-title gamipress-notification-step-title"><?php echo $a['notification_title']; ?></h2>
    <?php endif; ?>

    <?php // The step ?>
    <div class="gamipress-notification-description gamipress-notification-step-description">
        <?php echo $a['notification_content']; ?>
    </div>

    <?php
    /**
     * After render the step notification
     *
     * @since 1.0.0
     *
     * @param integer $step_id          The step ID
     * @param array   $template_args    Template received arguments
     */
    do_action( 'gamipress_notifications_after_render_step_notification', get_the_ID(), $a ); ?>

</div>
