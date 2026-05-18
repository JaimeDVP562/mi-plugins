<?php
/**
 * Review Product Rating
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Review_Product_Rating
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Review_Product_Rating extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_review_product_rating';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User reviews a product with a rating greater than, less than or equal to a specific amount', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User reviews a product with a rating <strong>greater than, less than or equal</strong> to a specific amount', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Post title. %2$s: Condition. %3$s: Rating. %4$s: Number of times. */
            'edit_label'        => sprintf( __( 'User reviews %1$s with a rating %2$s %3$s %4$s time(s)', 'automatorwp-woocommerce' ), '{post}', '{condition}', '{rating}', '{times}' ),
            /* translators: %1$s: Post title. %2$s: Condition. %3$s: Rating. */
            'log_label'         => sprintf( __( 'User reviews %1$s with a rating %2$s %3$s', 'automatorwp-woocommerce' ), '{post}', '{condition}', '{rating}' ),
            'action'            => 'comment_post',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any product', 'automatorwp-woocommerce' ),
                    'post_type' => 'product'
                ) ),
                'condition' => automatorwp_utilities_condition_option(),
                'rating' => array(
                    'from' => 'rating',
                    'fields' => array(
                        'rating' => array(
                            'name' => __( 'Rating:', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'type' => 'number',
                                'min' => '1',
                            ),
                            'default' => 1
                        )
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags( __( 'Product', 'automatorwp-woocommerce' ) ),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int        $comment_ID        The comment ID.
     * @param int|string $comment_approved  1 if the comment is approved, 0 if not, 'spam' if spam.
     * @param array      $comment           Review data.
     */
    public function listener( $comment_ID, $comment_approved, $comment ) {

        // Bail if comments is not approved
        if( $comment_approved !== 1 ) {
            return;
        }

        $post = get_post( $comment[ 'comment_post_ID' ] );

        // Bail if not post instanced
        if( ! $post instanceof WP_Post ) {
            return;
        }

        // Bail if post type is not a product
        if( $post->post_type !== 'product' ) {
            return;
        }

        // Get the review rating
        $rating = get_comment_meta( $comment_ID, 'rating', true );

        $user_id = (int) $comment['user_id'];

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'post_id' => $post->ID,
            'rating'  => $rating,
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post and rating not received
        if( ! isset( $event['post_id'] ) && ! isset( $event['rating'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        $rating = absint( $event['rating'] );
        $required_rating = absint( $trigger_options['rating'] );

        // Don't deserve if rating doesn't match with the trigger option
        if( ! automatorwp_number_condition_matches( $rating, $required_rating, $trigger_options['condition'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_WooCommerce_Review_Product_Rating();