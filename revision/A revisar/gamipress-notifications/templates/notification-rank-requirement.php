<?php
/**
 * Rank Requirement Notification template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/notifications/notification-rank-requirement.php
 * To override a specific rank type just copy it as yourtheme/gamipress/notifications/notification-rank-requirement-{rank-type}.php
 */
global $gamipress_notifications_template_args;

// Shorthand
$a = $gamipress_notifications_template_args; ?>

<div id="gamipress-rank-requirement-<?php the_ID(); ?>-<?php echo $a['earning']->user_earning_id; ?>" class="gamipress-notification-rank-requirement gamipress-notification-rank-type-<?php echo $a['rank']->post_type; ?>">

    <?php
    /**
     * Before render the rank requirement notification
     *
     * @since 1.0.0
     *
     * @param integer $rank_requirement_id  The rank requirement ID
     * @param array   $template_args        Template received arguments
     */
    do_action( 'gamipress_notifications_before_render_rank_requirement_notification', get_the_ID(), $a ); ?>

    <?php // Already parsed notification title ?>
    <?php if( ! empty( $a['notification_title'] ) ) : ?>
        <h2 class="gamipress-notification-title gamipress-notification-rank-requirement-title"><?php echo $a['notification_title']; ?></h2>
    <?php endif; ?>

    <?php // The rank requirement ?>
    <div class="gamipress-notification-description gamipress-notification-rank-requirement-description">
        <?php echo $a['notification_content']; ?>
    </div>

    <?php
    /**
     * After render the rank requirement notification
     *
     * @param integer $rank_requirement_id  The rank requirement ID
     * @param array   $template_args        Template received arguments
     */
    do_action( 'gamipress_notifications_after_render_rank_requirement_notification', get_the_ID(), $a ); ?>

</div>
