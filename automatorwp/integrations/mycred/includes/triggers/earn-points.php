<?php
/**
 * Earn Points
 *
 * @package     AutomatorWP\Integrations\MyCred\Triggers\Earn_Points
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MyCred_Earn_Points extends AutomatorWP_Integration_Trigger {

    public $integration = 'mycred';
    public $trigger = 'mycred_earn_points';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User earns points', 'automatorwp' ),
            'select_option'     => __( 'User earns <strong>points</strong>', 'automatorwp' ),
            /* translators: %1$s: Points amount. %2$s: Post title. */
            'edit_label'        => sprintf( __( 'User earns %1$s %2$s', 'automatorwp' ), '{points}', '{points_type}' ),
            /* translators: %1$s: Points amount. %2$s: Post title. */
            'log_label'         => sprintf( __( 'User earns %1$s %2$s', 'automatorwp' ), '{points}', '{points_type}' ),
            'action'            => 'mycred_add_finished',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'points' => array(
                    'from' => 'points',
                    'default' => __( 'any amount of', 'automatorwp' ),
                    'fields' => array(
                        'points' => array(
                            'name' => __( 'Points amount:', 'automatorwp' ),
                            'desc' => __( 'Leave blank for any amount of points.', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        )
                    )
                ),
                'points_type' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'points_type',
                    'option_default'    => __( 'points type', 'automatorwp' ),
                    'name'              => __( 'Points Type:', 'automatorwp' ),
                    'option_none'       => false,
                    'action_cb'         => 'automatorwp_mycred_get_point_types',
                    'options_cb'        => 'automatorwp_mycred_options_cb_point_type',
                    'placeholder'       => 'Select a point type',
                    'default'           => ''
                ) ),
            ),
            'tags' => array(
                'times' => automatorwp_utilities_times_tag( true )
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param bool      $result
     * @param array     $request
     * @param object    mycred   User points balance
     */
    public function listener( $result, $request, $mycred ) {
        
        // Bail if no result
        if ( ! $result )
            return;

        $user_id = $request['user_id'];

        // Bail if no user
        if ( $user_id === 0 )
            return;

        $new_points = $request['amount'];

        // Bail if negative points
        if ( $new_points < 0 )
            return;

        $points_type = $request['type'];

        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'points_earned' => $new_points,
            'points_type'   => $points_type,
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

        // Don't deserve if points and points type are not received
        if( ! isset( $event['points_earned'] ) && ! isset( $event['points_type'] ) ) {
            return false;
        }

        $test = absint( $trigger_options['points'] );
    
        // Don't deserve if points earned are lower than trigger option
        if( absint( $event['points_earned'] ) < absint( $trigger_options['points'] ) ) {
            return false;
        }
        
        // Don't deserve if points type doesn't match with the trigger option
        if( $trigger_options['points_type'] !== 'any' && $trigger_options['points_type'] !== $event['points_type'] ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_MyCred_Earn_Points();