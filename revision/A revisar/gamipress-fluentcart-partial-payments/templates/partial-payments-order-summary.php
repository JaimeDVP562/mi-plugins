<?php
/**
 * Template: Partial Payments Order Summary
 *
 * Rendered inside the FluentCart order summary table to show applied point discounts.
 * Override in your theme: {theme}/gamipress/fluentcart-partial-payments/partial-payments-order-summary.php
 *
 * Variables available:
 *   $partial_payments (array)  Applied partial payments indexed by points type slug.
 *
 * @package GamiPress\FluentCart\Partial_Payments\Templates
 * @since   1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

foreach ( $partial_payments as $points_type => $data ) :

    $points_type_obj = gamipress_get_points_type( $points_type );

    if ( ! $points_type_obj ) continue;

    $points = absint( $data['points'] );
    $money  = floatval( $data['money'] );
    ?>
    <tr class="gamipress-fluentcart-partial-payments-row">
        <td class="gamipress-fluentcart-partial-payments-label">
            <?php printf(
                /* translators: 1: points amount, 2: points type plural name */
                esc_html__( '%1$d %2$s discount', 'gamipress-fluentcart-partial-payments' ),
                esc_html( $points ),
                esc_html( $points_type_obj['plural_name'] )
            ); ?>
            <span class="gamipress-fluentcart-partial-payments-fee-actions">
                <a href="#"
                   class="gamipress-fluentcart-partial-payments-remove"
                   data-points-type="<?php echo esc_attr( $points_type ); ?>">
                    <?php esc_html_e( '[Remove]', 'gamipress-fluentcart-partial-payments' ); ?>
                </a>
            </span>
        </td>
        <td class="gamipress-fluentcart-partial-payments-amount">
            -<?php echo esc_html( number_format( $money, 2 ) ); ?>
        </td>
    </tr>
    <?php
endforeach;
