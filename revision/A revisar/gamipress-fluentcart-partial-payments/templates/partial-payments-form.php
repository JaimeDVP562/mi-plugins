<?php
/**
 * Template: Partial Payments Form
 *
 * Displayed before the FluentCart checkout payment form.
 * Override in your theme: {theme}/gamipress/fluentcart-partial-payments/partial-payments-form.php
 *
 * Variables available:
 *   $points_types     (array)  Enabled points types with their data.
 *   $amount_type      (string) 'input' | 'slider' | 'fixed'
 *   $amount_step      (int)    Step for the amount field.
 *   $partial_payments (array)  Already-applied partial payments.
 *
 * @package GamiPress\FluentCart\Partial_Payments\Templates
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div id="gamipress-fluentcart-partial-payments" class="gamipress-fluentcart-partial-payments">

    <div class="gamipress-fluentcart-partial-payments-notices"></div>

    <?php if ( count( $points_types ) > 1 ) : ?>
        <div class="gamipress-fluentcart-partial-payments-form-toggle">
            <a href="#"><?php esc_html_e( 'Use points for a discount?', 'gamipress-fluentcart-partial-payments' ); ?></a>
        </div>
    <?php endif; ?>

    <div class="gamipress-fluentcart-partial-payments-form">

        <h3><?php esc_html_e( 'Partial Payment with Points', 'gamipress-fluentcart-partial-payments' ); ?></h3>

        <?php if ( count( $points_types ) > 1 ) : ?>
            <p>
                <label for="gamipress-fluentcart-partial-payments-points-type">
                    <?php esc_html_e( 'Points Type', 'gamipress-fluentcart-partial-payments' ); ?>
                </label>
                <select id="gamipress-fluentcart-partial-payments-points-type" name="points_type">
                    <?php foreach ( $points_types as $slug => $data ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>">
                            <?php echo esc_html( $data['plural_name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
        <?php else :
            $first_slug = key( $points_types );
            ?>
            <input type="hidden" name="points_type" value="<?php echo esc_attr( $first_slug ); ?>">
        <?php endif; ?>

        <?php foreach ( $points_types as $slug => $data ) :
            $initial = intval( $data['initial_amount'] );
            $max     = intval( $data['max_amount'] );
            $balance = intval( $data['user_points'] );
            $field_max = ( $max > 0 ) ? min( $max, $balance ) : $balance;
            ?>

            <div class="gamipress-fluentcart-partial-payments-points-type-fields" data-points-type="<?php echo esc_attr( $slug ); ?>">

                <label class="gamipress-fluentcart-partial-payments-points-label"
                       for="gamipress-fluentcart-partial-payments-points-<?php echo esc_attr( $slug ); ?>">
                    <?php echo esc_html( $data['plural_name'] ); ?>:
                    <span id="gamipress-fluentcart-partial-payments-points-<?php echo esc_attr( $slug ); ?>-preview"
                          class="gamipress-fluentcart-partial-payments-points-preview">
                        <?php echo esc_html( $initial ); ?>
                    </span>
                </label>

                <?php if ( $amount_type === 'slider' ) : ?>
                    <input
                        type="range"
                        id="gamipress-fluentcart-partial-payments-points-<?php echo esc_attr( $slug ); ?>"
                        name="<?php echo esc_attr( $slug ); ?>_points"
                        class="gamipress-fluentcart-partial-payments-points"
                        value="<?php echo esc_attr( $initial ); ?>"
                        min="0"
                        max="<?php echo esc_attr( $field_max ); ?>"
                        step="<?php echo esc_attr( $amount_step ); ?>"
                    />
                <?php elseif ( $amount_type === 'fixed' ) : ?>
                    <input type="hidden"
                           name="<?php echo esc_attr( $slug ); ?>_points"
                           value="<?php echo esc_attr( $initial ); ?>" />
                    <strong><?php echo esc_html( $initial ); ?></strong>
                <?php else : // default: input ?>
                    <input
                        type="number"
                        id="gamipress-fluentcart-partial-payments-points-<?php echo esc_attr( $slug ); ?>"
                        name="<?php echo esc_attr( $slug ); ?>_points"
                        class="gamipress-fluentcart-partial-payments-points"
                        value="<?php echo esc_attr( $initial ); ?>"
                        min="0"
                        max="<?php echo esc_attr( $field_max ); ?>"
                        step="<?php echo esc_attr( $amount_step ); ?>"
                    />
                <?php endif; ?>

                <span id="gamipress-fluentcart-partial-payments-points-<?php echo esc_attr( $slug ); ?>-balance"
                      class="gamipress-fluentcart-partial-payments-points-balance">
                    <?php printf(
                        /* translators: 1: user balance, 2: points type plural name */
                        esc_html__( 'You have %1$d %2$s available.', 'gamipress-fluentcart-partial-payments' ),
                        esc_html( $balance ),
                        esc_html( $data['plural_name'] )
                    ); ?>
                </span>

            </div>

        <?php endforeach; ?>

        <p class="gamipress-fluentcart-partial-payments-preview">
            <?php esc_html_e( 'Discount preview:', 'gamipress-fluentcart-partial-payments' ); ?>
            <strong>
                <span class="gamipress-fluentcart-partial-payments-preview-points">0</span>
                <span class="gamipress-fluentcart-partial-payments-preview-points-type"></span>
                =
                <span class="gamipress-fluentcart-partial-payments-preview-money">0.00</span>
            </strong>
        </p>

        <button type="button" name="apply_partial_payment">
            <?php esc_html_e( 'Apply Discount', 'gamipress-fluentcart-partial-payments' ); ?>
        </button>

    </div><!-- /.gamipress-fluentcart-partial-payments-form -->

</div><!-- /#gamipress-fluentcart-partial-payments -->
