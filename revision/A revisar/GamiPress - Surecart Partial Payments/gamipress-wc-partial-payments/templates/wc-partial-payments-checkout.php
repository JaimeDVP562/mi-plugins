<?php
/**
 * Partial Payments Checkout template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/wc-partial-payments/wc-partial-payments-checkout.php
 */
global $gamipress_wc_partial_payments_template_args;

// Shorthand
$a = $gamipress_wc_partial_payments_template_args;

// Setup vars
$user_id = get_current_user_id();
$points_label = ( count( $a['points_types'] ) === 1 ? $a['initial_points_type_data']['plural_name'] : __( 'points', 'gamipress-wc-partial-payments' ) ); ?>

<?php
/**
 * Before render partial payments template
 *
 * @since 1.0.0
 *
 * @param int   $user_id        The user ID
 * @param array $template_args  Template received arguments
 */
do_action( 'gamipress_wc_partial_payments_before_render_partial_payments', $user_id, $a ); ?>

<div id="gamipress-wc-partial-payments">

    <?php // Form toggle ?>
    <div class="gamipress-wc-partial-payments-form-toggle">

        <div class="woocommerce-info">
            <?php echo sprintf( __( 'Use %s for a discount? <a href="#">Click here</a>', 'gamipress-wc-partial-payments' ), strtolower( $points_label ) ); ?>
        </div>

        <?php
        /**
         * After partial payments form toggle
         *
         * @since 1.0.0
         *
         * @param int   $user_id        The user ID
         * @param array $template_args  Template received arguments
         */
        do_action( 'gamipress_wc_partial_payments_after_form_toggle', $user_id, $a ); ?>
    </div>

    <?php // Partial payments form ?>
    <form class="gamipress-wc-partial-payments-form" method="post" style="display:none">

        <?php // Points amount ?>
        <p class="gamipress-wc-partial-payments-points-field form-row form-row-first">
            <?php foreach( $a['points_types'] as $points_type => $data ) :
                // Only show initial points type
                $points_style = ( $a['initial_points_type'] !== $points_type  ? 'display: none;' : '' );?>

                <label for="gamipress-wc-partial-payments-points-<?php echo $points_type; ?>" class="gamipress-wc-partial-payments-points-label" style="<?php echo $points_style; ?>"><?php echo __( 'Amount:', 'gamipress-wc-partial-payments' ); ?></label>

                <?php // Amount preview (for range and hidden fields) ?>
                <?php if( $data['field_type'] === 'range' || $data['field_type'] === 'hidden' ) : ?>

                    <span id="gamipress-wc-partial-payments-points-<?php echo $points_type; ?>-preview"
                          class="gamipress-wc-partial-payments-points-preview"
                          style="<?php echo $points_style; ?>"
                    ><?php echo $data['field_value']; ?><?php if( $data['field_type'] === 'hidden' ) : ?><br><?php endif; ?></span>

                <?php endif; ?>

                <?php // Points field ?>
                <input type="<?php echo $data['field_type']; ?>"
                    name="<?php echo $points_type; ?>_points"
                    id="gamipress-wc-partial-payments-points-<?php echo $points_type; ?>"
                    class="gamipress-wc-partial-payments-points"
                    placeholder="<?php echo $data['field_placeholder']; ?>"
                    step="<?php echo $data['field_step']; ?>"
                    min="<?php echo $data['field_min']; ?>"
                    max="<?php echo $data['field_max']; ?>"
                    value="<?php echo $data['field_value']; ?>"
                    style="<?php echo $points_style; ?>"
                />

                <?php // Current points balance ?>
                <small id="gamipress-wc-partial-payments-points-<?php echo $points_type; ?>-balance" class="gamipress-wc-partial-payments-points-balance" style="<?php echo $points_style; ?>">
                    <?php echo sprintf( __( 'You have a current balance of %s.', 'gamipress-wc-partial-payments' ), gamipress_format_points( $data['user_points'], $points_type ) ); ?>
                </small>

            <?php endforeach; ?>
        </p>

        <?php
        /**
         * After partial payments points
         *
         * @since 1.0.0
         *
         * @param int   $user_id        The user ID
         * @param array $template_args  Template received arguments
         */
        do_action( 'gamipress_wc_partial_payments_after_points', $user_id, $a ); ?>

        <?php // Points type ?>
        <p class="gamipress-wc-partial-payments-points-type-field form-row form-row-last">

            <label for="gamipress-wc-partial-payments-points-type"><?php echo __( 'Type:', 'gamipress-wc-partial-payments' ); ?></label>

            <?php if( count( $a['points_types'] ) === 1 ) : ?>

                <?php // Single points type ?>
                <input type="hidden" name="points_type" value="<?php echo $a['initial_points_type']; ?>">
                <span><?php echo $a['initial_points_type_data']['plural_name']; ?></span>

            <?php else : ?>

                <?php // Points types select field ?>
                <select name="points_type" id="gamipress-wc-partial-payments-points-type">
                    <?php foreach( $a['points_types'] as $points_type => $data ) : ?>
                        <option value="<?php echo $points_type; ?>" <?php selected( $a['initial_points_type'], $points_type ); ?>><?php echo $data['plural_name']; ?></option>
                    <?php endforeach; ?>
                </select>

            <?php endif; ?>
        </p>

        <?php
        /**
         * After partial payments points type
         *
         * @since 1.0.0
         *
         * @param int   $user_id        The user ID
         * @param array $template_args  Template received arguments
         */
        do_action( 'gamipress_wc_partial_payments_after_points_type', $user_id, $a ); ?>

        <div class="clear"></div>

        <?php // Preview ?>
        <p class="gamipress-wc-partial-payments-preview form-row form-row-first">
            <?php echo sprintf( __( 'You will use %s for a %s discount.', 'gamipress-wc-partial-payments' ), $a['points_preview'], $a['money_preview'] ); ?>
        </p>

        <?php
        /**
         * After partial payments preview
         *
         * @since 1.0.0
         *
         * @param int   $user_id        The user ID
         * @param array $template_args  Template received arguments
         */
        do_action( 'gamipress_wc_partial_payments_after_preview', $user_id, $a ); ?>

        <?php // Apply button ?>
        <p class="gamipress-wc-partial-payments-button-wrapper form-row form-row-last">
            <button type="submit" name="apply_partial_payment" id="gamipress-wc-partial-payments-button" class="button"><?php echo __( 'Apply discount', 'gamipress-wc-partial-payments' ); ?></button>
        </p>

        <?php
        /**
         * After partial payments button
         *
         * @since 1.0.0
         *
         * @param int   $user_id        The user ID
         * @param array $template_args  Template received arguments
         */
        do_action( 'gamipress_wc_partial_payments_after_button', $user_id, $a ); ?>

        <div class="clear"></div>
    </form>

</div>

<?php // Notices wrapper ?>
<div class="gamipress-wc-partial-payments-notices"></div>

<?php
/**
 * After render partial payments template
 *
 * @since 1.0.0
 *
 * @param int   $user_id        The user ID
 * @param array $template_args  Template received arguments
 */
do_action( 'gamipress_wc_partial_payments_after_render_partial_payments', $user_id, $a ); ?>
