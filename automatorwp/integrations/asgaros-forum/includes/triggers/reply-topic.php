<?php
/**
 * Reply Topic
 *
 * @package     AutomatorWP\Integrations\Asgaros_Forum\Triggers\Reply_Topic
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Asgaros_Forum_Reply_Topic extends AutomatorWP_Integration_Trigger {

    public $integration = 'asgaros_forum';
    public $trigger = 'asgaros_forum_reply_topic';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
public function register() {

    automatorwp_register_trigger( $this->trigger, array(
        'integration'       => $this->integration,
        'label'             => __( 'User replies to a topic on a forum', 'automatorwp' ),
        'select_option'     => __( 'User <strong>replies</strong> to a topic of a forum', 'automatorwp' ),
        /* translators: %1$s: Topic title. %2$s: Forum title. %2$s: Number of times. */
        'edit_label'        => sprintf( __( 'User replies to %1$s of %2$s %3$s time(s)', 'automatorwp' ), '{topic}', '{forum}', '{times}' ),
        /* translators: %1$s: Topic title. %2$s: Forum title. */
        'log_label'         => sprintf( __( 'User replies to %1$s of %2$s', 'automatorwp' ), '{topic}', '{forum}' ),
        'action'            => 'asgarosforum_after_add_post_submit',
        'function'          => array( $this, 'listener' ),
        'priority'          => 10,
        'accepted_args'     => 6,
        'options'           => array(
            'topic' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'topic',
                    'name'              => __( 'Topic:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any topic', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_asgaros_forum_get_topics',
                    'options_cb'        => 'automatorwp_asgaros_forum_options_cb_topic',
                    'default'           => 'any'
                ) ),
            'forum' => automatorwp_utilities_ajax_selector_option( array(
                'field'             => 'forum',
                'name'              => __( 'Forum:', 'automatorwp' ),
                'option_none_value' => 'any',
                'option_none_label' => __( 'any forum', 'automatorwp' ),
                'action_cb'         => 'automatorwp_asgaros_forum_get_forums',
                'options_cb'        => 'automatorwp_asgaros_forum_options_cb_forum',
                'default'           => 'any'
            ) ),
            'times' => automatorwp_utilities_times_option(),
        ),
        'tags' => array_merge(
            automatorwp_asgaros_forum_get_general_tags(),
            automatorwp_asgaros_forum_get_reply_tags(),
            automatorwp_utilities_times_tag()
        ),
        
    ) );
}

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $post_id
     * @param int $topic_id
     * @param array $post_data
     * 
     */
public function listener( $post_id, $topic_id, $subject, $content, $link, $author_id ) {

    $user_id = get_current_user_id();
 
    // Login is required
    if ( $user_id === 0 ) {
        return;
    }
    
    // To get topic parent
    $forum_id = automatorwp_asgaros_forum_get_forum_by_topic( $topic_id );

    automatorwp_trigger_event( array(
        'trigger'       => $this->trigger,
        'user_id'       => $author_id,
        'forum_id'      => $forum_id,
        'topic_id'      => $topic_id,
        'topic_title'   => $subject,
        'reply_content' => $content,
        'reply_link'    => $link,
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
     * @param array     $event              Event inpostation
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post is not received
        if( ! isset( $event['forum_id'] ) ) {
            return false;
        }

        // Don't deserve if post is not received
        if( ! isset( $event['topic_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( $trigger_options['forum'] !== 'any' &&  absint( $trigger_options['forum'] ) !== absint( $event['forum_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( $trigger_options['topic'] !== 'any' &&  absint( $trigger_options['topic'] ) !== absint( $event['topic_id'] ) ) {
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
     * @param array     $event              Event inpostation
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

        $log_meta['forum_id'] = isset( $event['forum_id'] ) ? $event['forum_id'] : '';
        $log_meta['topic_id'] = isset( $event['topic_id'] ) ? $event['topic_id'] : '';
        $log_meta['topic_title'] = isset( $event['topic_title'] ) ? $event['topic_title'] : '';
        $log_meta['reply_content'] = isset( $event['reply_content'] ) ? $event['reply_content'] : '';
        $log_meta['reply_link'] = isset( $event['reply_link'] ) ? $event['reply_link'] : '';

        return $log_meta;

    }

}

new AutomatorWP_Asgaros_Forum_Reply_Topic();