<?php
/**
 * Save Page
 *
 * @package     AutomatorWP\Integrations\Breakdance\Triggers\Save_Page
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class AutomatorWP_Breakdance_Save_Page extends AutomatorWP_Integration_Trigger {

    public $integration = 'breakdance';
    public $trigger = 'breakdance_save_page';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User saves a page', 'automatorwp' ),
            'select_option'     => __( 'User saves <strong>a page</strong>', 'automatorwp' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User saves %1$s %2$s time(s)', 'automatorwp' ), '{post_title}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User saves %1$s', 'automatorwp' ), '{post_title}' ),
            'action'            => 'save_post',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'post_title' => array(
                    'from' => 'post_title',
                    'default' => __( 'any page', 'automatorwp' ),
                    'fields' => array(
                        'post_title' => array(
                            'name' => __( 'Page title:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        )
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     */
    public function listener( $post_id, $post, $update ) {

        // Bail if not a page or if is a revision
        if ( $post->post_type !== 'page' || wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Bail if not a Breakdance page
        if ( ! automatorwp_breakdance_is_breakdance_page( $post_id ) ) {
            return;
        }

        $user_id = get_current_user_id();

        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        // Trigger save page event
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'post_id'       => $post_id,
            'post_title'    => $post->post_title,
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

        // Don't deserve if post title is not received
        if( ! isset( $event['post_title'] ) ) {
            return false;
        }

        // Don't deserve if post title doesn't match with the trigger option
        if( ! empty( $trigger_options['post_title'] ) && $event['post_title'] !== $trigger_options['post_title'] ) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        parent::hooks();
    }

}

new AutomatorWP_Breakdance_Save_Page();