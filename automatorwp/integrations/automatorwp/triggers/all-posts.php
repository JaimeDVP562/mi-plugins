<?php
/**
 * All Posts
 *
 * @package     AutomatorWP\Integrations\AutomatorWP\Triggers\All_Posts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_AutomatorWP_All_Posts extends AutomatorWP_Integration_Trigger {

    public $integration = 'automatorwp';
    public $trigger = 'automatorwp_all_posts';

    /**
     * The field conditions
     *
     * @since 1.0.0
     *
     * @var array $field_conditions
     */
    public $field_conditions = array();

    /**
     * The meta conditions
     *
     * @since 1.0.0
     *
     * @var array $meta_conditions
     */
    public $meta_conditions = array();

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'Run automation on all posts', 'automatorwp' ),
            'select_option'     => '',
            'show_in_selector'  => false,
            /* translators: %1$s: Automation title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'Run automation on %1$s', 'automatorwp' ), '{all_posts_conditions}' ),
            /* translators: %1$s: Automation title. */
            'log_label'         => sprintf( __( 'Run automation on %1$s', 'automatorwp' ), '{all_posts_conditions}' ),
            'options'           => array(
                'all_posts_conditions' => array(
                    'default' => __( 'all posts', 'automatorwp' ),
                    'fields' => array(
                        'field_conditions' => array(
                            'name' => __( 'Field Conditions:', 'automatorwp' ),
                            'desc' => __( 'Set conditions to filter posts by their fields.', 'automatorwp' ),
                            'type' => 'group',
                            'classes' => 'automatorwp-fields-table',
                            'options'     => array(
                                'add_button'        => __( 'Add condition', 'automatorwp' ),
                                'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                            ),
                            'fields' => array(
                                'operator' => automatorwp_utilities_operator_field(),
                                'field' => array(
                                    'name' => __( 'Field:', 'automatorwp' ),
                                    'type' => 'select',
                                    'options_cb' => 'automatorwp_options_cb_post_fields',
                                    'option_none' => true,
                                    'option_none_value' => '',
                                    'option_none_label' => __( 'Choose a field', 'automatorwp' ),
                                    'default' => ''
                                ),
                                'condition' => automatorwp_utilities_condition_field(),
                                'value' => array(
                                    'name' => __( 'Value:', 'automatorwp' ),
                                    'type' => 'text',
                                    'default' => ''
                                ),
                            ),
                        ),
                        'meta_conditions' => array(
                            'name' => __( 'Meta Conditions:', 'automatorwp' ),
                            'desc' => __( 'Set conditions to filter posts by their metas.', 'automatorwp' ),
                            'type' => 'group',
                            'classes' => 'automatorwp-fields-table',
                            'options'     => array(
                                'add_button'        => __( 'Add condition', 'automatorwp' ),
                                'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                            ),
                            'fields' => array(
                                'operator' => automatorwp_utilities_operator_field(),
                                'meta_key' => array(
                                    'name' => __( 'Meta Key:', 'automatorwp' ),
                                    'type' => 'text',
                                    'default' => ''
                                ),
                                'condition' => automatorwp_utilities_condition_field(),
                                'meta_value' => array(
                                    'name' => __( 'Meta Value:', 'automatorwp' ),
                                    'type' => 'text',
                                    'default' => ''
                                ),
                            ),
                        ),
                    )
                ),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        parent::hooks();

        add_filter( 'automatorwp_get_automation_item_option_replacement', array( $this, 'dynamic_item_option_replacement' ), 10, 5 );
        add_filter( 'automatorwp_get_all-posts_automation_sql', array( $this, 'get_sql' ), 10, 7 );

        // Override edit item HTML
        add_filter( 'automatorwp_automation_item_edit_html_integration', array( $this, 'override_item_edit_html_integration' ), 10, 4 );
        add_filter( 'automatorwp_automations_column_triggers_integration', array( $this, 'override_item_edit_html_integration' ), 10, 4 );
        add_filter( 'automatorwp_get_log_integration_icon_integration', array( $this, 'override_item_edit_html_integration' ), 10, 4 );
        add_filter( 'automatorwp_automation_item_edit_html_classes', array( $this, 'override_item_edit_html_classes' ), 10, 4 );
        add_filter( 'automatorwp_automation_item_edit_html_actions', array( $this, 'override_item_edit_html_actions' ), 10, 4 );

        // Times tag, register as "no user trigger"
        add_filter( 'automatorwp_get_trigger_tag_replacement_times_no_user_triggers', array( $this, 'add_type_to_array' ) );
        add_filter( 'automatorwp_get_trigger_last_completion_log_no_user_types', array( $this, 'add_type_to_array' ) );
        add_filter( 'automatorwp_get_action_last_completion_log_no_user_types', array( $this, 'add_type_to_array' ) );

        // Extend log object on last completion log
        add_filter( 'automatorwp_get_trigger_last_completion_log_extend_log', array( $this, 'extend_last_completion_log' ), 10, 2 );
        add_filter( 'automatorwp_get_action_last_completion_log_extend_log', array( $this, 'extend_last_completion_log' ), 10, 2 );

    }

    /**
     * Filters the option value for replacement on labels
     *
     * @since 1.0.0
     *
     * @param string    $value      The option value
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The item type (trigger|action)
     * @param string    $option     The option name
     * @param string    $context    The context this function is executed
     *
     * @return string
     */
    public function dynamic_item_option_replacement( $value, $object, $item_type, $option, $context ) {

        // Bail if not is our option
        if( $option !== 'all_posts_conditions' ) {
            return $value;
        }

        ct_setup_table( "automatorwp_{$item_type}s" );

        // Setup vars
        $field_conditions = ct_get_object_meta( $object->id, 'field_conditions', true );
        $meta_conditions = ct_get_object_meta( $object->id, 'meta_conditions', true );

        $someting_in_fields = false;
        $someting_in_metas = false;

        // Check if there is any field condition
        if( is_array( $field_conditions ) ) {
            foreach( $field_conditions as $condition ) {
                if( ! empty( $condition['field'] ) && ! empty( $condition['condition'] ) && ! empty( $condition['value'] ) ) {
                    $someting_in_fields = true;
                    break;
                }
            }
        }

        // Check if there is any meta condition
        if( is_array( $meta_conditions ) ) {
            foreach( $meta_conditions as $condition ) {
                if( ! empty( $condition['meta_key'] ) && ! empty( $condition['condition'] ) && ! empty( $condition['meta_value'] ) ) {
                    $someting_in_fields = true;
                    break;
                }
            }
        }

        if( $someting_in_fields || $someting_in_metas ) {
            $value = __( 'some posts', 'automatorwp' );
        }

        ct_reset_setup_table();

        return $value;

    }

    /**
     * Available filter to override the all posts automation SQL
     *
     * @since 1.0.0
     *
     * @param string    $sql                The SQL query
     * @param stdClass  $automation         The automation object
     * @param stdClass  $trigger            The trigger object
     * @param bool      $count              True if is looking for the SQL to count the number of posts
     * @param array     $trigger_options    The trigger's stored options
     * @param int       $items_per_loop     The automation items per loop option
     * @param int       $loop               The current loop
     */
    public function get_sql( $sql, $automation, $trigger, $count, $trigger_options, $items_per_loop, $loop ) {

        global $wpdb;

        // Setup vars
        $field_conditions = isset( $trigger_options['field_conditions'] ) ? $trigger_options['field_conditions'] : array();
        $meta_conditions = isset( $trigger_options['meta_conditions'] ) ? $trigger_options['meta_conditions'] : array();
        $allowed_fields = automatorwp_utilities_get_post_fields( );

        $joins = array();
        $where = array();

        // Set up the post field conditions
        if( is_array( $field_conditions ) ) {

            $where = array( "( 1=1" );
            
            foreach( $field_conditions as $condition ) {

                if( ! isset( $condition['field'] ) ) {
                    continue;
                }

                // Skip not allowed fields
                if( ! isset( $allowed_fields[$condition['field']] ) ) {
                    continue;
                }

                 // Initialize operator for backward compatibility
                 if( ! isset( $condition['operator'] ) ) {
                    $condition['operator'] = "AND";
                }

                // Ensure that operator is AND or OR, other values are not allowed
                if( ! in_array( $condition['operator'], array( "AND", "OR" ) ) ) {
                    $condition['operator'] = "AND";
                }

                // Replace all tags by their replacements
                $condition = automatorwp_parse_automation_tags( $automation->id, 0, $condition );

                // Sanitize
                $field = sanitize_text_field( $condition['field'] );
                $value = sanitize_text_field( $condition['value'] );

                if( ! empty( $field ) ) {
                    $where[] = $condition['operator'] . " (" . automatorwp_utilities_parse_condition_to_sql( 'p.' . $field, $condition['condition'], $value ) . ")";
                }
            }
            $where[] = ')';
        }

        // Set up the post meta conditions
        if( is_array( $meta_conditions ) ) {
            foreach( $meta_conditions as $condition ) {

                if( ! isset( $condition['meta_key'] ) ) {
                    continue;
                }

                // Initialize operator for backward compatibility
                if( ! isset( $condition['operator'] ) ) {
                    $condition['operator'] = "AND";
                }

                // Ensure that operator is AND or OR, other values are not allowed
                if( ! in_array( $condition['operator'], array( "AND", "OR" ) ) ) {
                    $condition['operator'] = "AND";
                }

                // Replace all tags by their replacements
                $condition = automatorwp_parse_automation_tags( $automation->id, 0, $condition );

                // Sanitize
                $meta_key = sanitize_key( $condition['meta_key'] );
                $meta_key = esc_sql( $meta_key );
                $meta_value = sanitize_text_field( $condition['meta_value'] );

                if( ! empty( $meta_key ) ) {
                    $index = count( $joins );

                    $joins[] = "INNER JOIN {$wpdb->postmeta} AS pm{$index} ON ( pm{$index}.post_id = p.ID AND pm{$index}.meta_key = '{$meta_key}' )";

                    $where[] = $condition['operator'] . " (" .automatorwp_utilities_parse_condition_to_sql( "pm{$index}.meta_value", $condition['condition'], $meta_value, false ) . ")";
                }
            }
        }

        // Turn arrays into strings
        $joins = implode( ' ', $joins );
        $where = ( ! empty( $where ) ? 'WHERE ' . implode( ' ', $where ) : '' );

        if( $count ) {
            // The count SQL query
            return "SELECT COUNT(*) FROM {$wpdb->posts} AS p {$joins} {$where}";
        } else {
            $offset = $loop * $items_per_loop;

            // The normal SQL query
            return "SELECT p.ID FROM {$wpdb->posts} AS p {$joins} {$where} LIMIT $offset, {$items_per_loop}";
        }

    }

    public function override_item_edit_html_integration( $integration, $object, $item_type, $automation ) {

        if( $item_type === 'trigger' && $object->type === $this->trigger ) {
            $integration['icon'] = AUTOMATORWP_URL . 'assets/img/automatorwp-all-posts.svg';
        }

        return $integration;

    }

    public function override_item_edit_html_classes( $classes, $object, $item_type, $automation ) {

        if( $automation->type === 'all-posts' && $object->type === $this->trigger ) {
            $classes[] = 'automatorwp-no-grab';
        }

        return $classes;

    }

    public function override_item_edit_html_actions( $actions, $object, $item_type, $automation ) {

        if( $automation->type === 'all-posts' && $object->type === $this->trigger ) {
            unset( $actions['move-up'] );
            unset( $actions['move-down'] );
            unset( $actions['delete'] );
        }

        return $actions;

    }

    public function add_type_to_array( $no_user_triggers ) {

        $no_user_triggers[] = $this->trigger;

        return $no_user_triggers;

    }

    public function extend_last_completion_log( $log, $trigger ) {
    
        global $automatorwp_event;
        
        if( $trigger->type === $this->trigger ) {        
        
            // Update the post ID for tag parsing
            if( is_array( $automatorwp_event ) && isset( $automatorwp_event['post_id'] ) ) {
                $log->post_id = absint( $automatorwp_event['post_id'] );
            }
        }

        return $log;
    }

}

new AutomatorWP_AutomatorWP_All_Posts();