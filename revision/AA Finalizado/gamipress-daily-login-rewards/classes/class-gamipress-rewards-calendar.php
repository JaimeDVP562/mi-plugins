<?php
/**
 * GamiPress Rewards Calendar Class
 *
 * @package     GamiPress\Daily_Login_Rewards\Rewards_Calendar
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_Rewards_Calendar {

    protected $rewards_calendar_id = 0;
    protected $args = array();
    protected $items = array();

    public function __construct( $rewards_calendar_id = null, $args = array() ) {

        if( $rewards_calendar_id === null ) {
            $this->rewards_calendar_id = get_the_ID();
        } else {
            $this->rewards_calendar_id = $rewards_calendar_id;
        }

        $this->args = wp_parse_args( $args, array(
            'columns'       => absint( $this->get_post_meta( 'columns' ) ),
            'image_size'    => absint( $this->get_post_meta( 'image_size' ) ),
        ) );
    }

    public function display() {

        if( get_post_field( 'post_type', $this->rewards_calendar_id ) !== 'rewards-calendar' ) {
            return;
        }

        $this->prepare_items();

        if( $this->has_items() ) : ?>

            <?php // Setup table classes
            $classes = $this->get_calendar_classes(); ?>

            <div class="gamipress-rewards-calendar-rewards <?php echo implode( ' ', $classes ); ?>">

                <?php $this->display_rows(); ?>

            </div>
        <?php elseif( current_user_can( gamipress_get_manager_capability() ) ) : ?>
            <div class="gamipress-rewards-calendar no-items">
                <?php $this->no_items(); ?>
            </div>
        <?php endif;
    }

    public function prepare_items() {

        $this->items = $this->get_items();

    }

    public function has_items() {
        return ( count( $this->items ) > 0 );
    }

    public function no_items() {
        _e( 'No rewards configured on this rewards calendar.', 'gamipress-daily-login-rewards' );
    }

    public function get_items() {

        $args = array(
            'post_type'         => 'calendar-reward',
            'post_parent'       => $this->rewards_calendar_id,
            'orderby'           => 'menu_order',
            'order'             => 'ASC',
            'numberposts'       => -1,
            'suppress_filters'  => false
        );

        $args = apply_filters( 'gamipress_daily_login_rewards_rewards_calendar_items_args', $args, $this );

        return get_posts( $args );

    }

    public function get_calendar_classes() {

        $classes = array();

        $classes[] = 'gamipress-rewards-calendar-col-' . $this->args['columns'];

        return apply_filters( 'gamipress_daily_login_rewards_rewards_calendar_classes', $classes, $this );

    }

    public function display_rows() {

        foreach ( $this->items as $item ) {
            $this->single_row( $item );
        }

    }

    public function single_row( $item ) {

        global $post;

        global $gamipress_daily_login_rewards_template_args;

        // Initialize template args global
        $gamipress_daily_login_rewards_template_args = $this->args;

        $post = $item;

        setup_postdata( $post );

        gamipress_get_template_part( 'calendar-reward' );

        wp_reset_postdata();

    }

    /**
     * Helper function to easily get rewards calendar meta field
     *
     * @since 1.0.0
     *
     * @param string   $meta_key        The meta key to retrieve.
     * @param bool     $single          Optional. Whether to return a single value.
     *
     * @return mixed
     */
    private function get_post_meta( $meta_key, $single = true ) {
        $prefix = '_gamipress_daily_login_rewards_';

        return gamipress_get_post_meta( $this->rewards_calendar_id, $prefix . $meta_key, $single );
    }

}