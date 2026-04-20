<?php
/**
 * Click a link from a category
 *
 * @package     AutomatorWP\Integrations\ShortLinksPro\Triggers\Click_Link_From_Category
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ShortLinksPro_Click_Link_From_Category extends AutomatorWP_Integration_Trigger {

    public $integration = 'shortlinkspro';
    public $trigger = 'shortlinkspro_click_link_from_category';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User clicks a link from a category', 'automatorwp' ),
            'select_option'     => __( 'User clicks <strong>a link</strong> from a category', 'automatorwp' ),
            /* translators: %1$s: Link title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User clicks a link from %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Link title. */
            'log_label'         => sprintf( __( 'User clicks %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'shortlinkspro_before_redirect', 
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'post' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'post',
                    'name'              => __( 'Category:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any category', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_shortlinkspro_get_categories',
                    'options_cb'        => 'automatorwp_shortlinkspro_options_cb_category',
                    'default'           => 'any'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_shortlinkspro_get_link_tags(),
                automatorwp_utilities_times_tag()
            )

        ) );
    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param stdClass  $link           Link object
     * @param string    $parameters     The query parameters
     * @param string    $url            URL to redirect
     */
    public function listener( $link, $parameters, $url ) {
        
        $user_id = get_current_user_id();

        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        // Shorthand
        $link_id = $link->id;
        $link_slug = $link->slug;

        $categories = automatorwp_shortlinkspro_get_categories_from_link( $link_id );

        // Bail if not categories
        if ( ! $categories )
            return;

        foreach ( $categories as $category ) {

            // Trigger link categories event
            automatorwp_trigger_event( array(
                'trigger'       => $this->trigger,
                'user_id'       => $user_id,
                'link_id'       => $link_id,
                'link_slug'     => $link_slug,
                'category_id'   => $category->id,
            ) );

        }
        
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

        // Don't deserve if post is not received
        if( ! isset( $event['category_id'] ) ) {
            return false;
        }

        // Bail if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $event['category_id'] ) !== absint( $trigger_options['post'] ) ) {
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

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['link_slug'] = ( isset( $event['link_slug'] ) ? $event['link_slug'] : '' );

        return $log_meta;

    }

}

new AutomatorWP_ShortLinksPro_Click_Link_From_Category();