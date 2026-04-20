<?php
/**
 * Award User Points
 *
 * @package     AutomatorWP\Integrations\MyCred\Actions\Award_User_Points
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MyCred_Award_User_Points extends AutomatorWP_Integration_Action {

    public $integration = 'mycred';
    public $action = 'mycred_award_user_points';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Award points to user', 'automatorwp' ),
            'select_option'     => __( 'Award <strong>points</strong> to user', 'automatorwp' ),
            /* translators: %1$s: Points amount. %2$s: Post title. %3$s: User. */
            'edit_label'        => sprintf( __( 'Award %1$s %2$s to %3$s', 'automatorwp' ), '{points}', '{points_type}', '{user}' ),
            /* translators: %1$s: Points amount. %2$s: Post title. %3$s: User. */
            'log_label'         => sprintf( __( 'Award %1$s %2$s to %3$s', 'automatorwp' ), '{points}', '{points_type}', '{user}' ),
            'options'           => array(
                'points' => array(
                    'from' => 'points',
                    'fields' => array(
                        'points' => array(
                            'name' => __( 'Points amount:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => '1'
                        ),
                        'reference' => array(
                            'name' => __( 'Reference:', 'automatorwp' ),
                            'desc' => __( 'The reference to log. Leave blank for "Automatorwp Reward" default value', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                        'earning_text' => array(
                            'name' => __( 'User earning text:', 'automatorwp' ),
                            'desc' => __( 'Enter the text for the user entry. Leave blank for "Automatorwp Reward" default value.', 'automatorwp' ),
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
                'user' => array(
                    'from' => 'user',
                    'default' => __( 'user', 'automatorwp' ),
                    'fields' => array(
                        'user' => array(
                            'name' => __( 'User ID:', 'automatorwp' ),
                            'desc' => __( 'User ID that will receive this points. Leave blank to award the points to the user that completes the automation.', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $points = absint( $action_options['points'] );
        $points_type = $action_options['points_type'];
        $reference = $action_options['reference'];
        $earning_text = $action_options['earning_text'];
        $user_id_to_award = absint( $action_options['user'] );

        if( $user_id_to_award === 0 ) {
            $user_id_to_award = $user_id;
        }

        $user = get_userdata( $user_id_to_award );

        // Bail if user does not exists
        if( ! $user ) {
            return;
        }

        // Bail if no points to award
        if( $points === 0 ) {
            return;
        }
    
        if ( empty ( $reference ) )
            $reference = 'automatorwp_reward';

        if ( empty ( $earning_text ) )
            $earning_text = 'AutomatorWP Reward';

        mycred_add( $reference, $user_id_to_award, $points, $earning_text, '', '', $points_type );
        

    }

}

new AutomatorWP_MyCred_Award_User_Points();