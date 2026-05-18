<?php
/**
 * Filters
 *
 * @package GamiPress\SureCart\Partial_Payments\Filters
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Partial payments form output
 *
 * Generates the HTML for the partial payments form to inject in SureCart checkout
 *
 * @since 1.0.0
 *
 * @return string HTML form output
 */
function gamipress_sc_partial_payments_get_form_html() {

    // Guests not allowed
    if( ! is_user_logged_in() ) return '';

    $user_id = get_current_user_id();
    $partial_payments = gamipress_sc_partial_payments_get_partial_payments();

    $points_types = array();
    $prefix = '_gamipress_sc_partial_payments_';

    // Settings
    $amount_type = gamipress_sc_partial_payments_get_option( 'amount_type', 'input' );
    $amount_step = absint( gamipress_sc_partial_payments_get_option( 'amount_step', '1' ) );

    foreach( gamipress_get_points_types() as $points_type => $data ) {

        // Skip points type that are already in use
        if( isset( $partial_payments[$points_type] ) ) continue;

        if( (bool) gamipress_get_post_meta( $data['ID'], $prefix . 'enable' ) ) {

            $data['user_points'] = gamipress_get_user_points( $user_id, $points_type );

            // Skip points types that user doesn't have any amount
            if( $data['user_points'] === 0 ) continue;

            $data['conversion'] = gamipress_get_post_meta( $data['ID'], $prefix . 'conversion' );

            // Points types without a conversion rate can't be used for partial payments
            if( empty( $data['conversion'] ) ) continue;

            $data['initial_amount']  = absint( gamipress_get_post_meta( $data['ID'], $prefix . 'initial_amount' ) );
            $data['max_amount']  = absint( gamipress_get_post_meta( $data['ID'], $prefix . 'max_amount' ) );

            /**
             * Filter to allow other plugins to decide if points type is allowed for partial payments
             *
             * @since 1.0.0
             *
             * @param bool      $allow_points_type  Whatever if points type is allowed for partial payments, by default true
             * @param string    $points_type        The points type slug
             * @param integer   $user_id            The user ID that will perform the purchase
             * @param array     $data               The points type data, with extra keys from this plugin
             *
             * @return bool                         Whatever if points type is allowed for partial payments, by default true
             */
            $allow_points_type = apply_filters( 'gamipress_sc_partial_payments_allow_points_type', true, $points_type, $user_id, $data );

            // Skip not allowed points types
            if( ! $allow_points_type ) continue;

            // Turn amount type into a valid input type value
            switch( $amount_type ) {
                case 'fixed':
                    $field_type = 'hidden';
                    break;
                case 'slider':
                    $field_type = 'range';
                    break;
                default:
                    $field_type = 'number';
                    break;
            }

            // Setup field vars (for the points field)
            $data['field_type'] = $field_type;
            $data['field_placeholder'] = 0;
            $data['field_step'] = $amount_step;
            $data['field_min'] = 0;
            $data['field_max'] = ( $data['max_amount'] === 0 ? $data['user_points'] : $data['max_amount'] );
            $data['field_value'] = $data['initial_amount'];

            $points_types[$points_type] = $data;
        }

    }

    // Bail if none points type is setup to be used for partial payments
    if( empty( $points_types ) ) {
        return '';
    }

    /**
     * Filter to allow other plugins to decide if is allowed partial payments with this full setup
     *
     * @since 1.0.0
     *
     * @param bool      $allow_partial_payments Whatever if is allowed partial payments, by default true
     * @param array     $points_types           Array of the points types allowed, with extra keys from this plugin
     * @param integer   $user_id                The user ID that will perform the purchase
     *
     * @return bool                             Whatever if is allowed partial payments, by default true
     */
    $allow_partial_payments = apply_filters( 'gamipress_sc_partial_payments_allow_partial_payments', true, $points_types, $user_id );

    // Bail if not allowed partial payments
    if( ! $allow_partial_payments ) {
        return '';
    }

    // Setup vars
    $initial_points_type = array_keys( $points_types )[0];
    $initial_points_type_data = $points_types[$initial_points_type];

    // Points preview vars
    $points_label = ( count( $points_types ) === 1 ? $initial_points_type_data['plural_name'] : __( 'points', 'gamipress-sc-partial-payments' ) );

    $points_preview_html = '<span class="gamipress-sc-partial-payments-preview-points">' . gamipress_format_amount( $initial_points_type_data['initial_amount'], $initial_points_type ) . '</span>';
    $points_type_preview_label = '<span class="gamipress-sc-partial-payments-preview-points-type">' . $initial_points_type_data['plural_name'] . '</span>';
    $points_preview_html .= ' ' . $points_type_preview_label;

    // Setup money preview
    $preview_money = gamipress_sc_partial_payments_convert_to_money( $initial_points_type_data['initial_amount'], $initial_points_type );
    $preview_money_formatted = number_format( $preview_money, 2, '.', ',' );

    $currency_symbol = gamipress_sc_partial_payments_get_currency_symbol();
    $money_preview_html = '<span class="gamipress-sc-partial-payments-preview-money">' . $currency_symbol . $preview_money_formatted . '</span>';

    // Start output buffering for the template
    ob_start();
    ?>

    <?php
    /**
     * Before render partial payments template
     *
     * @since 1.0.0
     *
     * @param int   $user_id        The user ID
     * @param array $points_types   Array of points types
     */
    do_action( 'gamipress_sc_partial_payments_before_render_partial_payments', $user_id, $points_types );
    ?>

    <div id="gamipress-sc-partial-payments">

        <?php // Form toggle ?>
        <div class="gamipress-sc-partial-payments-form-toggle">
            <div class="gamipress-sc-partial-payments-info">
                <?php echo sprintf( __( 'Use %s for a discount? <a href="#">Click here</a>', 'gamipress-sc-partial-payments' ), strtolower( $points_label ) ); ?>
            </div>

            <?php
            /**
             * After partial payments form toggle
             *
             * @since 1.0.0
             *
             * @param int   $user_id        The user ID
             * @param array $points_types   Array of points types
             */
            do_action( 'gamipress_sc_partial_payments_after_form_toggle', $user_id, $points_types );
            ?>
        </div>

        <?php // Partial payments form ?>
        <form class="gamipress-sc-partial-payments-form" method="post" style="display:none">

            <?php // Points amount ?>
            <div class="gamipress-sc-partial-payments-points-field">
                <?php foreach( $points_types as $points_type => $data ) :
                    // Only show initial points type
                    $points_style = ( $initial_points_type !== $points_type  ? 'display: none;' : '' );?>

                    <label for="gamipress-sc-partial-payments-points-<?php echo $points_type; ?>" class="gamipress-sc-partial-payments-points-label" style="<?php echo $points_style; ?>"><?php echo __( 'Amount:', 'gamipress-sc-partial-payments' ); ?></label>

                    <?php // Amount preview (for range and hidden fields) ?>
                    <?php if( $data['field_type'] === 'range' || $data['field_type'] === 'hidden' ) : ?>

                        <span id="gamipress-sc-partial-payments-points-<?php echo $points_type; ?>-preview"
                              class="gamipress-sc-partial-payments-points-preview"
                              style="<?php echo $points_style; ?>"
                        ><?php echo $data['field_value']; ?><?php if( $data['field_type'] === 'hidden' ) : ?><br><?php endif; ?></span>

                    <?php endif; ?>

                    <?php // Points field ?>
                    <input type="<?php echo $data['field_type']; ?>"
                        name="<?php echo $points_type; ?>_points"
                        id="gamipress-sc-partial-payments-points-<?php echo $points_type; ?>"
                        class="gamipress-sc-partial-payments-points"
                        placeholder="<?php echo $data['field_placeholder']; ?>"
                        step="<?php echo $data['field_step']; ?>"
                        min="<?php echo $data['field_min']; ?>"
                        max="<?php echo $data['field_max']; ?>"
                        value="<?php echo $data['field_value']; ?>"
                        style="<?php echo $points_style; ?>"
                    />

                    <?php // Current points balance ?>
                    <small id="gamipress-sc-partial-payments-points-<?php echo $points_type; ?>-balance" class="gamipress-sc-partial-payments-points-balance" style="<?php echo $points_style; ?>">
                        <?php echo sprintf( __( 'You have a current balance of %s.', 'gamipress-sc-partial-payments' ), gamipress_format_points( $data['user_points'], $points_type ) ); ?>
                    </small>

                <?php endforeach; ?>
            </div>

            <?php
            /**
             * After partial payments points
             *
             * @since 1.0.0
             *
             * @param int   $user_id        The user ID
             * @param array $points_types   Array of points types
             */
            do_action( 'gamipress_sc_partial_payments_after_points', $user_id, $points_types );
            ?>

            <?php // Points type ?>
            <div class="gamipress-sc-partial-payments-points-type-field">

                <label for="gamipress-sc-partial-payments-points-type"><?php echo __( 'Type:', 'gamipress-sc-partial-payments' ); ?></label>

                <?php if( count( $points_types ) === 1 ) : ?>

                    <?php // Single points type ?>
                    <input type="hidden" name="points_type" value="<?php echo $initial_points_type; ?>">
                    <span><?php echo $initial_points_type_data['plural_name']; ?></span>

                <?php else : ?>

                    <?php // Points types select field ?>
                    <select name="points_type" id="gamipress-sc-partial-payments-points-type">
                        <?php foreach( $points_types as $points_type => $data ) : ?>
                            <option value="<?php echo $points_type; ?>" <?php selected( $initial_points_type, $points_type ); ?>><?php echo $data['plural_name']; ?></option>
                        <?php endforeach; ?>
                    </select>

                <?php endif; ?>
            </div>

            <?php
            /**
             * After partial payments points type
             *
             * @since 1.0.0
             *
             * @param int   $user_id        The user ID
             * @param array $points_types   Array of points types
             */
            do_action( 'gamipress_sc_partial_payments_after_points_type', $user_id, $points_types );
            ?>

            <div class="clear"></div>

            <?php // Preview ?>
            <div class="gamipress-sc-partial-payments-preview">
                <?php echo sprintf( __( 'You will use %s for a %s discount.', 'gamipress-sc-partial-payments' ), $points_preview_html, $money_preview_html ); ?>
            </div>

            <?php
            /**
             * After partial payments preview
             *
             * @since 1.0.0
             *
             * @param int   $user_id        The user ID
             * @param array $points_types   Array of points types
             */
            do_action( 'gamipress_sc_partial_payments_after_preview', $user_id, $points_types );
            ?>

            <?php // Apply button ?>
            <div class="gamipress-sc-partial-payments-button-wrapper">
                <button type="submit" name="apply_partial_payment" id="gamipress-sc-partial-payments-button" class="gamipress-sc-partial-payments-btn"><?php echo __( 'Apply discount', 'gamipress-sc-partial-payments' ); ?></button>
            </div>

            <?php
            /**
             * After partial payments button
             *
             * @since 1.0.0
             *
             * @param int   $user_id        The user ID
             * @param array $points_types   Array of points types
             */
            do_action( 'gamipress_sc_partial_payments_after_button', $user_id, $points_types );
            ?>

            <div class="clear"></div>
        </form>

        <?php // Applied discounts ?>
        <div class="gamipress-sc-partial-payments-applied">
            <?php foreach( $partial_payments as $pt => $data ) :
                $pt_obj = gamipress_get_points_type( $pt );
                if( ! $pt_obj ) continue;
            ?>
                <div class="gamipress-sc-partial-payments-applied-item" data-points-type="<?php echo esc_attr( $pt ); ?>">
                    <span class="gamipress-sc-partial-payments-applied-label">
                        <?php echo sprintf( __( 'Discount using %s: -%s', 'gamipress-sc-partial-payments' ),
                            gamipress_format_points( $data['points'], $pt ),
                            gamipress_sc_partial_payments_format_money( $data['money'] )
                        ); ?>
                    </span>
                    <a href="#" class="gamipress-sc-partial-payments-remove" data-points-type="<?php echo esc_attr( $pt ); ?>"><?php echo __( '[Remove]', 'gamipress-sc-partial-payments' ); ?></a>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php // Notices wrapper ?>
    <div class="gamipress-sc-partial-payments-notices"></div>

    <?php
    /**
     * After render partial payments template
     *
     * @since 1.0.0
     *
     * @param int   $user_id        The user ID
     * @param array $points_types   Array of points types
     */
    do_action( 'gamipress_sc_partial_payments_after_render_partial_payments', $user_id, $points_types );

    return ob_get_clean();
}

/**
 * Inject partial payments form into SureCart checkout via render_block filter
 *
 * @since 1.0.0
 *
 * @param string $block_content The block content
 * @param array  $block         The block data
 *
 * @return string Modified block content
 */
function gamipress_sc_partial_payments_inject_form( $block_content, $block ) {

    // Inject before the SureCart checkout form block
    if ( isset( $block['blockName'] ) && $block['blockName'] === 'surecart/checkout-form' ) {
        $form_html = gamipress_sc_partial_payments_get_form_html();
        if ( ! empty( $form_html ) ) {
            $block_content = $form_html . $block_content;
        }
    }

    return $block_content;
}
add_filter( 'render_block', 'gamipress_sc_partial_payments_inject_form', 10, 2 );

/**
 * Also inject via shortcode for SureCart forms loaded via shortcode
 *
 * @since 1.0.0
 *
 * @param string $content The page content
 *
 * @return string Modified content
 */
function gamipress_sc_partial_payments_inject_form_shortcode( $content ) {

    // Check if content contains SureCart checkout shortcode
    if ( has_shortcode( $content, 'sc_form' ) || strpos( $content, 'sc-checkout' ) !== false ) {
        $form_html = gamipress_sc_partial_payments_get_form_html();
        if ( ! empty( $form_html ) ) {
            $content = $form_html . $content;
        }
    }

    return $content;
}
add_filter( 'the_content', 'gamipress_sc_partial_payments_inject_form_shortcode', 5 );

/**
 * Handle purchase completion - register user earnings and clear partial payments
 *
 * @since 1.0.0
 *
 * @param \SureCart\Models\Purchase $purchase The purchase object
 * @param array                    $data     The raw event data
 */
function gamipress_sc_partial_payments_on_purchase_created( $purchase, $data ) {

    if( ! is_user_logged_in() ) return;

    $user_id = get_current_user_id();

    // Also try to get user from customer email
    if( ! $user_id && isset( $purchase->customer ) ) {
        $customer = $purchase->customer;
        if( isset( $customer->email ) ) {
            $user = get_user_by( 'email', $customer->email );
            if( $user ) {
                $user_id = $user->ID;
            }
        }
    }

    if( ! $user_id ) return;

    $partial_payments = get_user_meta( $user_id, 'gamipress_sc_partial_payments', true );

    if( ! is_array( $partial_payments ) || empty( $partial_payments ) ) return;

    $order_id = isset( $purchase->order ) ? $purchase->order : '';

    // Register on user earnings for each partial payment
    foreach( $partial_payments as $points_type => $pp_data ) {

        if( gamipress_get_points_type( $points_type ) ) {

            $points = absint( $pp_data['points'] );

            // Register on user earnings
            if( apply_filters( 'gamipress_sc_partial_payments_register_on_user_earnings', true ) ) {

                $points_type_label = gamipress_get_points_amount_label( $points, $points_type, true );

                gamipress_insert_user_earning( $user_id, array(
                    'title'         => sprintf( __( '-%s %s used for a discount on SureCart order %s', 'gamipress-sc-partial-payments' ), $points, $points_type_label, $order_id ),
                    'user_id'       => $user_id,
                    'post_id'       => gamipress_get_points_type_id( $points_type ),
                    'post_type'     => 'points-type',
                    'points'        => $points,
                    'points_type'   => $points_type,
                    'date'          => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
                ) );

            }
        }
    }

    // Clear partial payments after successful purchase
    delete_user_meta( $user_id, 'gamipress_sc_partial_payments' );

}
add_action( 'surecart/purchase_created', 'gamipress_sc_partial_payments_on_purchase_created', 10, 2 );

/**
 * Handle purchase revocation - refund points back to user
 *
 * @since 1.0.0
 *
 * @param \SureCart\Models\Purchase $purchase The purchase object
 * @param array                    $data     The raw event data
 */
function gamipress_sc_partial_payments_on_purchase_revoked( $purchase, $data ) {

    // Try to find the user from the purchase
    $user_id = 0;

    if( isset( $purchase->customer ) ) {
        $customer = $purchase->customer;
        if( isset( $customer->email ) ) {
            $user = get_user_by( 'email', $customer->email );
            if( $user ) {
                $user_id = $user->ID;
            }
        }
    }

    if( ! $user_id ) return;

    $order_id = isset( $purchase->order ) ? $purchase->order : '';

    // Check if we have stored partial payment info for this order
    $order_partial_payments = get_user_meta( $user_id, 'gamipress_sc_partial_payments_order_' . $order_id, true );

    if( ! is_array( $order_partial_payments ) || empty( $order_partial_payments ) ) return;

    // Refund points for each partial payment
    foreach( $order_partial_payments as $points_type => $pp_data ) {

        if( gamipress_get_points_type( $points_type ) ) {

            $points = absint( $pp_data['points'] );

            // Award the points back to the user
            gamipress_award_points_to_user( $user_id, $points, $points_type );

            // Register on user earnings
            if( apply_filters( 'gamipress_sc_partial_payments_register_on_user_earnings', true ) ) {

                $points_type_label = gamipress_get_points_amount_label( $points, $points_type, true );

                gamipress_insert_user_earning( $user_id, array(
                    'title'         => sprintf( __( '%s %s refunded for SureCart order %s', 'gamipress-sc-partial-payments' ), $points, $points_type_label, $order_id ),
                    'user_id'       => $user_id,
                    'post_id'       => gamipress_get_points_type_id( $points_type ),
                    'post_type'     => 'points-type',
                    'points'        => $points,
                    'points_type'   => $points_type,
                    'date'          => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
                ) );

            }
        }
    }

    // Remove order partial payment record
    delete_user_meta( $user_id, 'gamipress_sc_partial_payments_order_' . $order_id );

}
add_action( 'surecart/purchase_revoked', 'gamipress_sc_partial_payments_on_purchase_revoked', 10, 2 );

/**
 * Store partial payment info for the order before clearing
 * This is called during purchase creation to preserve refund data
 *
 * @since 1.0.0
 *
 * @param \SureCart\Models\Purchase $purchase The purchase object
 * @param array                    $data     The raw event data
 */
function gamipress_sc_partial_payments_store_order_data( $purchase, $data ) {

    if( ! is_user_logged_in() ) return;

    $user_id = get_current_user_id();
    $partial_payments = get_user_meta( $user_id, 'gamipress_sc_partial_payments', true );

    if( ! is_array( $partial_payments ) || empty( $partial_payments ) ) return;

    $order_id = isset( $purchase->order ) ? $purchase->order : '';

    if( ! empty( $order_id ) ) {
        // Store a copy of partial payments linked to this order for potential refunds
        update_user_meta( $user_id, 'gamipress_sc_partial_payments_order_' . $order_id, $partial_payments );
    }

}
add_action( 'surecart/purchase_created', 'gamipress_sc_partial_payments_store_order_data', 5, 2 );

/**
 * Validate partial payments during SureCart checkout
 *
 * @since 1.0.0
 *
 * @param \WP_Error $error
 * @param array     $data
 *
 * @return \WP_Error
 */
function gamipress_sc_partial_payments_validate_checkout( $error, $data ) {

    if( ! is_user_logged_in() ) return $error;

    $partial_payments = gamipress_sc_partial_payments_get_partial_payments();

    if( empty( $partial_payments ) ) return $error;

    $user_id = get_current_user_id();

    // Validate that user still has the required points
    foreach( $partial_payments as $points_type => $pp_data ) {

        $points_type_obj = gamipress_get_points_type( $points_type );

        if( ! $points_type_obj ) {
            // Remove invalid entries
            unset( $partial_payments[$points_type] );
            continue;
        }

        // Points were already deducted when applied, so we don't need to check balance again
    }

    // Update partial payments if any were removed
    update_user_meta( $user_id, 'gamipress_sc_partial_payments', $partial_payments );

    return $error;
}
add_filter( 'surecart/checkout/validate', 'gamipress_sc_partial_payments_validate_checkout', 10, 2 );
