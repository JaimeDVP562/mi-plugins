<?php
/**
 * Calendar Reward template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/daily_login_rewards/calendar-reward.php
 */
global $gamipress_daily_login_rewards_template_args;

// Shorthand
$a = $gamipress_daily_login_rewards_template_args;

$calendar_reward = gamipress_daily_login_rewards_get_reward_object( get_the_ID(), $a['image_size'] );
$stamp = gamipress_daily_login_rewards_get_option( 'stamp', '' );
$align_stamp_with_image = (bool) gamipress_daily_login_rewards_get_option( 'align_stamp_with_image', false );

// Check if user has earned this calendar reward
$earned = gamipress_daily_login_rewards_has_user_earned_reward( get_current_user_id(), get_the_ID(), true );

// Setup calendar reward classes
$classes = array(
    'gamipress-calendar-reward',
    'gamipress-calendar-reward-day-' . $calendar_reward['order'],
    'gamipress-calendar-reward-type-' . $calendar_reward['reward_type'],
    ( $earned ? 'user-has-earned' : 'user-has-not-earned' ),
    ( $align_stamp_with_image ? 'align-stamp-with-image' : '' ),
);

/**
 * Calendar reward classes
 *
 * @since 1.0.0
 *
 * @param array     $classes                Array of achievement classes
 * @param integer   $calendar_reward_id     The Calendar Reward ID
 * @param array     $template_args          Template received arguments
 */
$classes = apply_filters( 'gamipress_daily_login_rewards_calendar_reward_classes', $classes, get_the_ID(), $a ); ?>

<div id="gamipress-calendar-reward-<?php the_ID(); ?>" class="<?php echo implode( ' ', $classes ); ?>">

    <?php // Stamp effect (on full reward element) ?>
    <?php if( $earned && $stamp
            &&  ( ! $align_stamp_with_image || ! $calendar_reward['thumbnail'] ) ) : ?>
        <div class="reward-stamp">
            <img src="<?php echo $stamp; ?>">
        </div>
    <?php endif; ?>

    <div class="gamipress-calendar-reward-day">
        <?php echo sprintf( __( 'Day %d', 'gamipress-daily-login-rewards' ), $calendar_reward['order'] ); ?>
    </div>

    <?php
    /**
     * Before render calendar reward
     *
     * @since 1.0.0
     *
     * @param integer $calendar_reward_id   The Calendar Reward ID
     * @param array   $template_args        Template received arguments
     */
    do_action( 'gamipress_before_render_calendar_reward', get_the_ID(), $a ); ?>

    <?php if( $calendar_reward['thumbnail'] ) : ?>

        <div class="gamipress-calendar-reward-thumbnail">

            <?php // Stamp effect (on reward's image) ?>
            <?php if( $earned && $stamp && $align_stamp_with_image ) : ?>
                <div class="reward-stamp">
                    <img src="<?php echo $stamp; ?>" width="<?php echo $a['image_size']; ?>">
                </div>
            <?php endif; ?>

            <?php echo $calendar_reward['thumbnail']; ?>
        </div>

        <?php
        /**
         * After render calendar reward thumbnail
         *
         * @since 1.0.0
         *
         * @param integer $calendar_reward_id   The Calendar Reward ID
         * @param array   $template_args        Template received arguments
         */
        do_action( 'gamipress_after_render_calendar_reward_thumbnail', get_the_ID(), $a ); ?>

    <?php endif; ?>

    <div class="gamipress-calendar-reward-label">
        <?php echo $calendar_reward['label']; ?>
    </div>

    <?php
    /**
     * After render calendar reward label
     *
     * @since 1.0.0
     *
     * @param integer $calendar_reward_id   The Calendar Reward ID
     * @param array   $template_args        Template received arguments
     */
    do_action( 'gamipress_after_render_calendar_reward_label', get_the_ID(), $a ); ?>

    <?php
    /**
     * After render calendar reward
     *
     * @since 1.0.0
     *
     * @param integer $calendar_reward_id   The Calendar Reward ID
     * @param array   $template_args        Template received arguments
     */
    do_action( 'gamipress_after_render_calendar_reward', get_the_ID(), $a ); ?>

</div>
