<?php
/**
 * Credly login template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/credly/credly-login.php
 */
global $gamipress_credly_template_args;

// Shorthand
$a = $gamipress_credly_template_args;

// Setup vars
$user_id = get_current_user_id(); ?>

<fieldset class="gamipress-credly-form-wrapper gamipress-credly-login-form-wrapper">

    <form class="gamipress-credly-form gamipress-credly-login-form" action="" method="POST">

        <?php
        /**
         * Before render login form
         *
         * @since 1.0.0
         *
         * @param integer     $user_id          User ID
         * @param array       $template_args    Template received arguments
         */
        do_action( 'gamipress_credly_before_login_form', $user_id, $a ); ?>

        <?php // Email field ?>

        <p id="gamipress-credly-login-form-email" class="gamipress-credly-login-form-email-input">

            <?php if( ! empty( $a['label'] ) ) : ?>
                <label for="gamipress-credly-login-form-email-label"><?php echo $a['label']; ?></label>
            <?php endif; ?>

            <input
                id="gamipress-credly-login-form-email-input"
                class="gamipress-credly-login-form-email-input"
                name="email"
                type="text">

        </p>

        <?php // Setup submit actions ?>

        <p class="gamipress-credly-form-submit gamipress-credly-login-form-submit">
            <?php // Loading spinner ?>
            <span class="gamipress-spinner" style="display: none;"></span>
            <input
                id="gamipress-credly-login-form-submit-button"
                class="gamipress-credly-form-submit-button gamipress-credly-login-form-submit-button"
                type="submit"
                value="<?php echo $a['button_text']; ?>">
        </p>

        <?php // Output hidden fields ?>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'gamipress_credly_login_form' ); ?>">
        <input type="hidden" name="referrer" value="<?php echo get_the_permalink(); ?>">

        <?php
        /**
         * After render login form
         *
         * @since 1.0.0
         *
         * @param integer     $user_id          User ID
         * @param array       $template_args    Template received arguments
         */
        do_action( 'gamipress_credly_after_login_form', $user_id, $a ); ?>

    </form>

</fieldset>

