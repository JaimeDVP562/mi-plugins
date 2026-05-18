<?php
/**
 * Referral URL Generator template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/referrals/referral-url-generator.php
 */
global $gamipress_referrals_template_args;

// Shorthand
$a = $gamipress_referrals_template_args; ?>

<div class="gamipress-referrals-form gamipress-referrals-referral-url-generator">

    <?php
    /**
     * Before render referral URL Generator
     *
     * @since 1.0.0
     *
     * @param array $template_args Template received arguments
     */
    do_action( 'gamipress_before_render_referral_url_generator', $a ); ?>

    <p class="gamipress-referrals-form-url">

        <label for="gamipress-referrals-form-url-input"><?php _e( 'Page URL', 'gamipress-referrals' ); ?></label>

        <span class="gamipress-referrals-form-description"><?php _e( 'Enter any URL from this website to generate a referral link.', 'gamipress-referrals' ); ?></span>

        <input type="text" id="gamipress-referrals-form-url-input" class="gamipress-referrals-form-url-input" value="<?php echo $a['url']; ?>">

    </p>

    <?php
    /**
     * After render referral URL Generator URL field
     *
     * @since 1.0.0
     *
     * @param array $template_args Template received arguments
     */
    do_action( 'gamipress_after_render_referral_url_generator_url_field', $a ); ?>

    <p class="gamipress-referrals-form-referral-url" style="display: none;">

        <label for="gamipress-referrals-form-referral-url-input"><?php _e( 'Referral URL', 'gamipress-referrals' ); ?></label>

        <span class="gamipress-referrals-form-description"><?php _e( 'Copy this URL and share it anywhere.', 'gamipress-referrals' ); ?></span>

        <input type="text" id="gamipress-referrals-form-referral-url-input" class="gamipress-referrals-form-referral-url-input" value="">

    </p>

    <?php
    /**
     * After render referral URL Generator referral URL field
     *
     * @since 1.0.0
     *
     * @param array $template_args Template received arguments
     */
    do_action( 'gamipress_after_render_referral_url_generator_referral_url_field', $a ); ?>

    <?php // Setup submit actions ?>
    <p class="gamipress-referrals-form-submit">
        <input type="button" id="gamipress-referrals-form-submit-button" class="gamipress-referrals-form-submit-button" value="<?php echo $a['button_text']; ?>">
        <input type="button" id="gamipress-referrals-copy-button" class="gamipress-referrals-copy-button" style="display:none" value="<?php echo __('Copy to clipboard', 'gamipress-referrals'); ?>">
        <span id="gamipress-referrals-copy-successful" class="gamipress-referrals-copy-successful" style="display:none"><?php echo __('URL copied', 'gamipress-referrals'); ?></span>
    </p>

    <?php
    /**
     * After render referral URL Generator
     *
     * @since 1.0.0
     *
     * @param array $template_args Template received arguments
     */
    do_action( 'gamipress_after_render_referral_url_generator', $a ); ?>

</div>
