<?php
/**
 * Admin
 *
 * @package GamiPress\Recurring_Rewards\Admin
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get all valid Recurring Rewards submenu slugs.
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_recurring_rewards_get_menu_slugs() {
    return array(
        'ct-list_gamipress_recurring_rewards',
        'gamipress_recurring_rewards',
    );
}

/**
 * Get manager capability used by recurring rewards admin.
 *
 * @since 1.0.0
 *
 * @return string
 */
function gamipress_recurring_rewards_get_manager_capability() {

    if ( function_exists( 'gamipress_get_manager_capability' ) ) {
        return gamipress_get_manager_capability();
    }

    return 'manage_options';

}

/**
 * Check if current user can manage recurring rewards.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function gamipress_recurring_rewards_current_user_can_manage() {

    return current_user_can( gamipress_recurring_rewards_get_manager_capability() );

}

/**
 * Check whether Recurring Rewards submenu already exists.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function gamipress_recurring_rewards_menu_exists() {
    global $submenu;

    if ( ! isset( $submenu['gamipress'] ) || ! is_array( $submenu['gamipress'] ) ) {
        return false;
    }

    foreach ( $submenu['gamipress'] as $item ) {
        if ( isset( $item[2] ) && in_array( $item[2], gamipress_recurring_rewards_get_menu_slugs(), true ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Ensures the recurring rewards submenu remains visible under GamiPress.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_ensure_menu() {
    global $submenu, $menu;

    $gamipress_menu_exists = false;
    if ( isset( $menu ) && is_array( $menu ) ) {
        foreach ( $menu as $item ) {
            if ( isset( $item[2] ) && 'gamipress' === $item[2] ) {
                $gamipress_menu_exists = true;
                break;
            }
        }
    }

    if ( ! $gamipress_menu_exists ) {
        return;
    }

    $menu_exists = gamipress_recurring_rewards_menu_exists();

    if ( ! $menu_exists ) {
        add_submenu_page(
            'gamipress',
            __( 'Recurring Rewards', 'gamipress-recurring-rewards' ),
            __( 'Recurring Rewards', 'gamipress-recurring-rewards' ),
            gamipress_recurring_rewards_get_manager_capability(),
            'ct-list_gamipress_recurring_rewards',
            null
        );
    }
}
add_action( 'admin_menu', 'gamipress_recurring_rewards_ensure_menu', 9999 );
add_action( 'parent_file', 'gamipress_recurring_rewards_ensure_menu', 9999 );

/**
 * Adds the recurring rewards meta boxes to the edit screen.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_add_meta_boxes() {

    add_meta_box(
        'gamipress_recurring_rewards_requirements',
        __( 'Unlock Requirements', 'gamipress-recurring-rewards' ),
        'gamipress_recurring_rewards_requirements_meta_box',
        'gamipress_recurring_rewards',
        'normal',
        'default'
    );

    add_meta_box(
        'gamipress_recurring_rewards_users',
        __( 'Users with Access', 'gamipress-recurring-rewards' ),
        'gamipress_recurring_rewards_users_meta_box',
        'gamipress_recurring_rewards',
        'side',
        'default'
    );

}
add_action( 'add_meta_boxes_gamipress_recurring_rewards', 'gamipress_recurring_rewards_add_meta_boxes' );

/**
 * Registers the CMB2 fields used by recurring rewards.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_cmb2_meta_boxes() {

    $cmb = new_cmb2_box( array(
        'id'           => 'gamipress_recurring_rewards_details',
        'title'        => __( 'Recurring Reward Details', 'gamipress-recurring-rewards' ),
        'object_types' => array( 'gamipress_recurring_rewards' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $cmb->add_field( array(
        'name' => __( 'Title', 'gamipress-recurring-rewards' ),
        'id'   => 'title',
        'type' => 'text',
    ) );

    $cmb->add_field( array(
        'name' => __( 'Points Amount', 'gamipress-recurring-rewards' ),
        'id'   => 'points_amount',
        'type' => 'text_small',
        'attributes' => array(
            'type' => 'number',
        ),
    ) );

    $points_type_options = array();
    foreach ( gamipress_get_points_types() as $slug => $data ) {
        $points_type_options[ $slug ] = $data['plural_name'];
    }
    $cmb->add_field( array(
        'name'    => __( 'Points Type', 'gamipress-recurring-rewards' ),
        'id'      => 'points_type',
        'type'    => 'select',
        'options' => $points_type_options,
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Cycle Interval', 'gamipress-recurring-rewards' ),
        'id'         => 'cycle_amount',
        'type'       => 'text',
        'attributes' => array(
            'type' => 'number',
            'min'  => 1,
        ),
        'desc' => __( 'How often to repeat this reward (e.g., 2 with Cycle Type "Day" means every 2 days)', 'gamipress-recurring-rewards' ),
    ) );

    $cmb->add_field( array(
        'name'    => __( 'Cycle Type', 'gamipress-recurring-rewards' ),
        'id'      => 'cycle_type',
        'type'    => 'select',
        'options' => array(
            'second' => __( 'Second', 'gamipress-recurring-rewards' ),
            'minute' => __( 'Minute', 'gamipress-recurring-rewards' ),
            'day'    => __( 'Day', 'gamipress-recurring-rewards' ),
            'week'   => __( 'Week', 'gamipress-recurring-rewards' ),
            'month'  => __( 'Month', 'gamipress-recurring-rewards' ),
            'year'   => __( 'Year', 'gamipress-recurring-rewards' ),
        ),
    ) );

    $cmb->add_field( array(
        'name'        => __( 'Day of Week', 'gamipress-recurring-rewards' ),
        'id'          => 'cycle_day',
        'type'        => 'select',
        'row_classes' => 'grr-cycle-day-row',
        'options'     => array(
            '1' => __( 'Monday', 'gamipress-recurring-rewards' ),
            '2' => __( 'Tuesday', 'gamipress-recurring-rewards' ),
            '3' => __( 'Wednesday', 'gamipress-recurring-rewards' ),
            '4' => __( 'Thursday', 'gamipress-recurring-rewards' ),
            '5' => __( 'Friday', 'gamipress-recurring-rewards' ),
            '6' => __( 'Saturday', 'gamipress-recurring-rewards' ),
            '7' => __( 'Sunday', 'gamipress-recurring-rewards' ),
        ),
    ) );

    $cmb->add_field( array(
        'name'        => __( 'Day of Month', 'gamipress-recurring-rewards' ),
        'id'          => 'cycle_month_day',
        'type'        => 'text_small',
        'row_classes' => 'grr-cycle-month-day-row',
        'attributes'  => array(
            'type' => 'number',
            'min'  => 1,
            'max'  => 31,
        ),
    ) );

}
add_action( 'cmb2_admin_init', 'gamipress_recurring_rewards_cmb2_meta_boxes' );

/**
 * Filters the updated messages displayed for recurring rewards.
 *
 * @since 1.0.0
 *
 * @param array $messages Existing updated messages.
 * @return array Filtered updated messages.
 */
function gamipress_recurring_rewards_updated_messages( $messages ) {
    $messages[1] = __( 'Recurring Reward updated successfully.', 'gamipress-recurring-rewards' );
    $messages[0] = __( 'Recurring Reward could not be updated.', 'gamipress-recurring-rewards' );
    return $messages;
}
add_filter( 'ct_table_updated_messages', 'gamipress_recurring_rewards_updated_messages' );

/**
 * Normalizes recurring reward data before CT saves the object.
 *
 * Serializes the requirements field and preserves the stored value when the
 * requirements UI is not included in the current request.
 *
 * @since 1.0.0
 *
 * @param array $object_data          Object data that will be saved.
 * @param array $original_object_data Original object data before save.
 * @return array Filtered object data ready for persistence.
 */
function gamipress_recurring_rewards_prepare_ct_object_data( $object_data, $original_object_data ) {
    global $ct_table;

    if ( ! is_a( $ct_table, 'CT_Table' ) || $ct_table->name !== 'gamipress_recurring_rewards' ) {
        return $object_data;
    }

    $request_data = wp_unslash( $_POST );

    $requirements_processed = isset( $request_data['grr_requirements_processed'] );
    
    if ( $requirements_processed ) {
        if ( isset( $request_data['requirements'] ) && is_array( $request_data['requirements'] ) && ! empty( $request_data['requirements'] ) ) {
            $requirements = array();
            foreach ( $request_data['requirements'] as $req ) {
                if ( ! is_array( $req ) ) {
                    continue;
                }
                $requirements[] = array(
                    'type' => isset( $req['type'] ) ? sanitize_text_field( $req['type'] ) : '',
                    'id'   => isset( $req['id'] ) ? intval( $req['id'] ) : 0,
                );
            }
            $object_data['requirements'] = wp_json_encode( $requirements );
        } else {
            $object_data['requirements'] = wp_json_encode( array() );
        }
    } else {
        if ( ! isset( $object_data['requirements'] ) && isset( $original_object_data['requirements'] ) ) {
            $object_data['requirements'] = $original_object_data['requirements'];
        }
    }

    if ( isset( $object_data['title'] ) ) {
        $object_data['title'] = sanitize_text_field( $object_data['title'] );
    }

    if ( isset( $object_data['points_amount'] ) ) {
        $object_data['points_amount'] = intval( $object_data['points_amount'] );
    }

    if ( isset( $object_data['points_type'] ) ) {
        $object_data['points_type'] = sanitize_text_field( $object_data['points_type'] );
    }

    if ( isset( $object_data['cycle_amount'] ) ) {
        $object_data['cycle_amount'] = intval( $object_data['cycle_amount'] );
    }

    if ( isset( $object_data['cycle_type'] ) ) {
        $object_data['cycle_type'] = sanitize_text_field( $object_data['cycle_type'] );
    }

    if ( isset( $object_data['cycle_day'] ) ) {
        $object_data['cycle_day'] = '' === $object_data['cycle_day'] ? 0 : intval( $object_data['cycle_day'] );
    }

    if ( isset( $object_data['cycle_month_day'] ) ) {
        $object_data['cycle_month_day'] = '' === $object_data['cycle_month_day'] ? 0 : intval( $object_data['cycle_month_day'] );
    }

    $rr_id = isset( $request_data['recurring_reward_id'] ) ? absint( $request_data['recurring_reward_id'] ) : 0;
    if ( $rr_id > 0 ) {
        add_filter( 'wp_redirect', function( $location ) use ( $rr_id ) {
            static $done = false;
            if ( ! $done ) {
                $done = true;
                gamipress_recurring_rewards_grant_access_to_existing_users( $rr_id );
            }
            return $location;
        }, 5 );
    }

    return $object_data;
}
add_filter( 'ct_insert_object_data', 'gamipress_recurring_rewards_prepare_ct_object_data', 10, 2 );

/**
 * Outputs a hidden `ct-save` field on the recurring rewards edit form.
 *
 * This preserves the expected submit key even when cached markup or scripts
 * change the visible button attributes.
 *
 * @since 1.0.0
 * @param object $object Current CT object.
 * @return void
 */
add_action( 'ct_edit_form_top', function( $object ) {
    global $ct_table;
    if ( ! is_a( $ct_table, 'CT_Table' ) || $ct_table->name !== 'gamipress_recurring_rewards' ) {
        return;
    }
    echo '<input type="hidden" name="ct-save" value="1">';
} );

/**
 * Renders the requirements meta box for recurring rewards.
 *
 * @since 1.0.0
 *
 * @param object $recurring_reward Current recurring reward object.
 * @return void
 */
function gamipress_recurring_rewards_requirements_meta_box( $recurring_reward ) {
    ct_setup_table( 'gamipress_recurring_rewards' );
    $requirements_json = isset( $recurring_reward->requirements ) ? $recurring_reward->requirements : '';
    ct_reset_setup_table();
    
    $requirements = array();
    if ( ! empty( $requirements_json ) ) {
        $decoded = json_decode( $requirements_json, true );
        if ( is_array( $decoded ) ) {
            $requirements = $decoded;
        }
    }
    ?>
    <div style="margin-bottom:15px; background: #f9f9f9; padding: 15px; border: 1px solid #ccd0d4;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div style="min-width: 150px;">
                <label style="display:block; margin-bottom:5px;"><strong>1. <?php _e('Type', 'gamipress-recurring-rewards'); ?></strong></label>
                <select id="requirement-type-selector" style="width:100%;">
                    <option value=""><?php _e('Select type', 'gamipress-recurring-rewards'); ?></option>
                    <option value="achievement"><?php _e('Achievement', 'gamipress-recurring-rewards'); ?></option>
                    <option value="rank"><?php _e('Rank', 'gamipress-recurring-rewards'); ?></option>
                </select>
            </div>
            
            <div style="flex-grow: 1; min-width: 250px;">
                <label style="display:block; margin-bottom:5px;"><strong>2. <?php _e('Search Achievement/Rank', 'gamipress-recurring-rewards'); ?></strong></label>
                <select id="requirement-item-selector" style="width:100%;">
                    <option value=""><?php _e('Select type first...', 'gamipress-recurring-rewards'); ?></option>
                </select>
            </div>

            <div style="padding-top: 20px;">
                <button type="button" id="add-requirement" class="button button-primary"><?php _e('Add Requirement', 'gamipress-recurring-rewards'); ?></button>
            </div>
        </div>
    </div>

    <div id="requirements-container">
        <?php foreach ($requirements as $index => $requirement): ?>
            <div class="requirement-item requirement-selected" style="margin-bottom: 10px; padding: 10px; border: 1px solid #0073aa; border-left-width: 4px; background: #fff; display: flex; justify-content: space-between; align-items: center;">
                <input type="hidden" name="requirements[<?php echo $index; ?>][type]" value="<?php echo esc_attr($requirement['type']); ?>">
                <input type="hidden" name="requirements[<?php echo $index; ?>][id]" value="<?php echo esc_attr($requirement['id']); ?>">
                <span>
                    <strong><?php echo $requirement['type'] === 'achievement' ? __('Achievement', 'gamipress-recurring-rewards') : __('Rank', 'gamipress-recurring-rewards'); ?>:</strong> 
                    <?php
                        $item = get_post($requirement['id']);
                        echo $item ? esc_html($item->post_title) : __('(Deleted)', 'gamipress-recurring-rewards');
                    ?>
                </span>
                <button type="button" class="remove-requirement button-link-delete"><?php _e('Remove', 'gamipress-recurring-rewards'); ?></button>
            </div>
        <?php endforeach; ?>
    </div>

    <input type="hidden" name="grr_requirements_processed" value="1">
    <?php
}

/**
 * Renders the users meta box for recurring rewards.
 *
 * @since 1.0.0
 *
 * @param object $recurring_reward Current recurring reward object.
 * @return void
 */
function gamipress_recurring_rewards_users_meta_box( $recurring_reward ) {

    global $wpdb;

    $ct_table = ct_setup_table( 'gamipress_recurring_reward_users' );

    $users = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$ct_table->db->table_name} WHERE recurring_reward_id = %d AND active = 1",
        $recurring_reward->recurring_reward_id
    ) );

    ct_reset_setup_table();

    if ( $users ) {
        echo '<ul>';
        foreach ( $users as $user ) {
            $user_info = get_userdata( $user->user_id );
            echo '<li>' . esc_html( $user_info->display_name ) . ' (' . esc_html( $user->date_unlocked ) . ') <a href="#" class="revoke-access" data-user-id="' . $user->user_id . '" data-reward-id="' . $recurring_reward->recurring_reward_id . '">' . __( 'Revoke', 'gamipress-recurring-rewards' ) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>' . __( 'No users have access yet.', 'gamipress-recurring-rewards' ) . '</p>';
    }
}

/**
 * Handles recurring reward access revocation requests.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_revoke_access_ajax() {

    check_ajax_referer( 'grr_admin_nonce', '_wpnonce' );

    if ( ! gamipress_recurring_rewards_current_user_can_manage() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-recurring-rewards' ), 403 );
    }

    $request_data = wp_unslash( $_POST );

    $user_id             = isset( $request_data['user_id'] ) ? absint( $request_data['user_id'] ) : 0;
    $recurring_reward_id = isset( $request_data['recurring_reward_id'] ) ? absint( $request_data['recurring_reward_id'] ) : 0;

    if ( $user_id <= 0 || $recurring_reward_id <= 0 ) {
        wp_send_json_error( __( 'Invalid request data.', 'gamipress-recurring-rewards' ) );
    }

    gamipress_recurring_rewards_revoke_access( $user_id, $recurring_reward_id );

    wp_send_json_success();
}

/**
 * Saves recurring reward form data received through AJAX.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_save_via_ajax() {

    check_ajax_referer( 'grr_admin_nonce', '_wpnonce' );

    if ( ! gamipress_recurring_rewards_current_user_can_manage() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-recurring-rewards' ), 403 );
    }

    $request_data = wp_unslash( $_POST );
    
    $recurring_reward_id = isset( $request_data['recurring_reward_id'] ) ? absint( $request_data['recurring_reward_id'] ) : 0;
    
    if ( $recurring_reward_id <= 0 ) {
        wp_send_json_error( __( 'Invalid recurring reward ID.', 'gamipress-recurring-rewards' ) );
    }

    $table_name = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_rewards' );

    if ( empty( $table_name ) ) {
        wp_send_json_error( __( 'Recurring rewards table is not available.', 'gamipress-recurring-rewards' ) );
    }

    global $wpdb;

    ct_setup_table( 'gamipress_recurring_rewards' );
    
    $update_data = array();
    $update_types = array();
    
    if ( isset( $request_data['title'] ) && '' !== trim( $request_data['title'] ) ) {
        $update_data['title'] = sanitize_text_field( $request_data['title'] );
        $update_types[] = '%s';
    }
    
    if ( isset( $request_data['points_amount'] ) && '' !== $request_data['points_amount'] ) {
        $update_data['points_amount'] = absint( $request_data['points_amount'] );
        $update_types[] = '%d';
        ct_update_object_meta( $recurring_reward_id, '_points_amount', $update_data['points_amount'] );
    }
    
    if ( isset( $request_data['points_type'] ) && '' !== $request_data['points_type'] ) {
        $update_data['points_type'] = sanitize_text_field( $request_data['points_type'] );
        $update_types[] = '%s';
        ct_update_object_meta( $recurring_reward_id, '_points_type', $update_data['points_type'] );
    }
    
    if ( isset( $request_data['cycle_amount'] ) && '' !== $request_data['cycle_amount'] ) {
        $update_data['cycle_amount'] = absint( $request_data['cycle_amount'] );
        $update_types[] = '%d';
        ct_update_object_meta( $recurring_reward_id, '_cycle_amount', $update_data['cycle_amount'] );
    }
    
    if ( isset( $request_data['cycle_type'] ) && '' !== $request_data['cycle_type'] ) {
        $update_data['cycle_type'] = sanitize_text_field( $request_data['cycle_type'] );
        $update_types[] = '%s';
        ct_update_object_meta( $recurring_reward_id, '_cycle_type', $update_data['cycle_type'] );
    }
    
    if ( isset( $request_data['cycle_day'] ) && '' !== $request_data['cycle_day'] ) {
        $update_data['cycle_day'] = absint( $request_data['cycle_day'] );
        $update_types[] = '%d';
        ct_update_object_meta( $recurring_reward_id, '_cycle_day', $update_data['cycle_day'] );
    }
    
    if ( isset( $request_data['cycle_month_day'] ) && '' !== $request_data['cycle_month_day'] ) {
        $update_data['cycle_month_day'] = absint( $request_data['cycle_month_day'] );
        $update_types[] = '%d';
        ct_update_object_meta( $recurring_reward_id, '_cycle_month_day', $update_data['cycle_month_day'] );
    }
    
    if ( isset( $request_data['requirements'] ) && is_array( $request_data['requirements'] ) && ! empty( $request_data['requirements'] ) ) {
        $requirements = array();
        foreach ( $request_data['requirements'] as $req ) {
            if ( ! is_array( $req ) ) {
                continue;
            }

            $requirement_type = isset( $req['type'] ) ? sanitize_text_field( $req['type'] ) : '';
            $requirement_id = isset( $req['id'] ) ? absint( $req['id'] ) : 0;

            if ( ! in_array( $requirement_type, array( 'achievement', 'rank' ), true ) || $requirement_id <= 0 ) {
                continue;
            }

            $requirements[] = array(
                'type' => $requirement_type,
                'id' => $requirement_id,
            );
        }

        $update_data['requirements'] = wp_json_encode( $requirements );
        $update_types[] = '%s';
        ct_update_object_meta( $recurring_reward_id, '_requirements', $update_data['requirements'] );
    } else {
        $update_data['requirements'] = wp_json_encode( array() );
        $update_types[] = '%s';
        ct_update_object_meta( $recurring_reward_id, '_requirements', $update_data['requirements'] );
    }
    
    if ( ! empty( $update_data ) ) {
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array( 'recurring_reward_id' => $recurring_reward_id ),
            $update_types,
            array( '%d' )
        );
        
        ct_reset_setup_table();

        if ( false !== $result ) {
            gamipress_recurring_rewards_grant_access_to_existing_users( $recurring_reward_id );

            wp_send_json_success( array(
                'message'             => __( 'Saved.', 'gamipress-recurring-rewards' ),
                'recurring_reward_id' => $recurring_reward_id,
                'timestamp'           => current_time( 'mysql' ),
            ) );
        } else {
            wp_send_json_error( __( 'Database update failed.', 'gamipress-recurring-rewards' ) );
        }
    } else {
        ct_reset_setup_table();
        wp_send_json_error( __( 'No data to update.', 'gamipress-recurring-rewards' ) );
    }
}

add_action( 'wp_ajax_gamipress_recurring_rewards_revoke_access', 'gamipress_recurring_rewards_revoke_access_ajax' );
add_action( 'wp_ajax_gamipress_recurring_rewards_save_metabox', 'gamipress_recurring_rewards_save_via_ajax' );

/**
 * Adds a shortcut button to the recurring rewards list screen.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_head', function() {
    global $pagenow;
    if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'gamipress_recurring_rewards' ) {
        $add_url = admin_url( 'admin.php?page=gamipress_recurring_rewards_add_custom' );
        echo '<style>.grr-add-btn{margin-bottom:16px;display:inline-block;}</style>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){
            var h1=document.querySelector(".wrap h1");
            if(h1){
                var btn=document.createElement("a");
                btn.href="' . esc_url( $add_url ) . '";
                btn.className="page-title-action grr-add-btn";
                btn.innerText="' . esc_html__( 'Add Recurring Reward', 'gamipress-recurring-rewards' ) . '";
                h1.parentNode.insertBefore(btn,h1.nextSibling);
            }
        });</script>';
    }
});

/**
 * Registers the hidden admin page used to create recurring rewards.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_menu', function() {
    add_submenu_page(
        '',
        __( 'Add Recurring Reward', 'gamipress-recurring-rewards' ),
        __( 'Add Recurring Reward', 'gamipress-recurring-rewards' ),
        gamipress_recurring_rewards_get_manager_capability(),
        'gamipress_recurring_rewards_add_custom',
        'gamipress_recurring_rewards_add_custom_page_callback'
    );
});

/**
 * Renders and processes the custom recurring reward creation screen.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_add_custom_page_callback() {
    if ( isset( $_POST['grr_add_submit'] ) && check_admin_referer( 'grr_add_nonce' ) ) {

        if ( ! gamipress_recurring_rewards_current_user_can_manage() ) {
            wp_die( esc_html__( 'You are not allowed to perform this action.', 'gamipress-recurring-rewards' ) );
        }

        $request_data = wp_unslash( $_POST );
        
        ct_setup_table( 'gamipress_recurring_rewards' );
        
        $data = array(
            'title' => isset( $request_data['title'] ) ? sanitize_text_field( $request_data['title'] ) : '',
            'points_amount' => isset( $request_data['points_amount'] ) ? absint( $request_data['points_amount'] ) : 0,
            'points_type' => isset( $request_data['points_type'] ) ? sanitize_text_field( $request_data['points_type'] ) : '',
            'cycle_amount' => isset( $request_data['cycle_amount'] ) ? absint( $request_data['cycle_amount'] ) : 0,
            'cycle_type' => isset( $request_data['cycle_type'] ) ? sanitize_text_field( $request_data['cycle_type'] ) : '',
        );

        if ( isset( $request_data['cycle_day'] ) && '' !== $request_data['cycle_day'] ) {
            $data['cycle_day'] = absint( $request_data['cycle_day'] );
        }
        
        if ( isset( $request_data['cycle_month_day'] ) && '' !== $request_data['cycle_month_day'] ) {
            $data['cycle_month_day'] = absint( $request_data['cycle_month_day'] );
        }

        if ( isset( $request_data['requirements'] ) && is_array( $request_data['requirements'] ) ) {
            $requirements = array();

            foreach ( $request_data['requirements'] as $req ) {
                if ( ! is_array( $req ) ) {
                    continue;
                }

                $requirement_type = isset( $req['type'] ) ? sanitize_text_field( $req['type'] ) : '';
                $requirement_id = isset( $req['id'] ) ? absint( $req['id'] ) : 0;

                if ( ! in_array( $requirement_type, array( 'achievement', 'rank' ), true ) || $requirement_id <= 0 ) {
                    continue;
                }

                $requirements[] = array(
                    'type' => $requirement_type,
                    'id' => $requirement_id,
                );
            }

            $data['requirements'] = wp_json_encode( $requirements );
        } else {
            $data['requirements'] = wp_json_encode( array() );
        }

        $object_id = ct_insert_object( $data );

        if ( $object_id && is_numeric( $object_id ) && $object_id > 0 ) {

            ct_update_object_meta( $object_id, '_points_amount', $data['points_amount'] );
            ct_update_object_meta( $object_id, '_points_type', $data['points_type'] );
            ct_update_object_meta( $object_id, '_cycle_amount', $data['cycle_amount'] );
            ct_update_object_meta( $object_id, '_cycle_type', $data['cycle_type'] );
            
            if ( isset( $data['cycle_day'] ) ) {
                ct_update_object_meta( $object_id, '_cycle_day', $data['cycle_day'] );
            }
            
            if ( isset( $data['cycle_month_day'] ) ) {
                ct_update_object_meta( $object_id, '_cycle_month_day', $data['cycle_month_day'] );
            }
            
            if ( isset( $requirements ) ) {
                ct_update_object_meta( $object_id, '_requirements', $data['requirements'] );
            } else {
                ct_update_object_meta( $object_id, '_requirements', wp_json_encode( array() ) );
            }
            
            ct_reset_setup_table();

            gamipress_recurring_rewards_grant_access_to_existing_users( $object_id );

            echo '<div class="notice notice-success"><p>' . esc_html__( 'Recurring Reward added successfully.', 'gamipress-recurring-rewards' ) . '</p></div>';
        } else {
            ct_reset_setup_table();
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Error adding Recurring Reward', 'gamipress-recurring-rewards' ) . '</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Add Recurring Reward', 'gamipress-recurring-rewards' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'grr_add_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="title"><?php _e( 'Title', 'gamipress-recurring-rewards' ); ?></label></th>
                    <td><input type="text" name="title" id="title" required class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="points_amount"><?php _e( 'Points Amount', 'gamipress-recurring-rewards' ); ?></label></th>
                    <td><input type="number" name="points_amount" id="points_amount" required /></td>
                </tr>
                <tr>
                    <th><label for="points_type"><?php _e( 'Points Type', 'gamipress-recurring-rewards' ); ?></label></th>
                    <td>
                        <select name="points_type" id="points_type" required>
                            <?php foreach( gamipress_get_points_types() as $slug => $data ) : ?>
                                <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $data['plural_name'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="cycle_amount"><?php _e( 'Cycle Amount', 'gamipress-recurring-rewards' ); ?></label></th>
                    <td><input type="number" name="cycle_amount" id="cycle_amount" required min="1" /></td>
                </tr>
                <tr>
                    <th><label for="cycle_type"><?php _e( 'Cycle Type', 'gamipress-recurring-rewards' ); ?></label></th>
                    <td>
                        <select name="cycle_type" id="cycle_type_add" required>
                            <option value="second"><?php _e( 'Second', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="minute"><?php _e( 'Minute', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="day"><?php _e( 'Day', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="week"><?php _e( 'Week', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="month" selected><?php _e( 'Month', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="year"><?php _e( 'Year', 'gamipress-recurring-rewards' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="cycle_day_row_add" style="display:none;">
                    <th><label for="cycle_day"><?php _e( 'Day of Week', 'gamipress-recurring-rewards' ); ?></label></th>
                    <td>
                        <select name="cycle_day" id="cycle_day_add">
                            <option value="1"><?php _e( 'Monday', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="2"><?php _e( 'Tuesday', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="3"><?php _e( 'Wednesday', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="4"><?php _e( 'Thursday', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="5"><?php _e( 'Friday', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="6"><?php _e( 'Saturday', 'gamipress-recurring-rewards' ); ?></option>
                            <option value="7"><?php _e( 'Sunday', 'gamipress-recurring-rewards' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="cycle_month_day_row_add" style="display:table-row;">
                    <th><label for="cycle_month_day"><?php _e( 'Day of Month', 'gamipress-recurring-rewards' ); ?></label></th>
                    <td><input type="number" name="cycle_month_day" id="cycle_month_day_add" min="1" max="31" /></td>
                </tr>
            </table>
            
     <h3><?php _e('Unlock Requirements', 'gamipress-recurring-rewards'); ?></h3>
            <p><?php _e('Select the achievements and ranks required to unlock this recurring reward', 'gamipress-recurring-rewards'); ?></p>
            
            <div style="margin-bottom:10px; display: flex; gap: 10px; align-items: center;">
                <select id="requirement-type-selector-add" style="width:150px;">
                    <option value=""><?php _e('Select type', 'gamipress-recurring-rewards'); ?></option>
                    <option value="achievement"><?php _e('Achievement', 'gamipress-recurring-rewards'); ?></option>
                    <option value="rank"><?php _e('Rank', 'gamipress-recurring-rewards'); ?></option>
                </select>

                <select id="requirement-item-selector-add" style="width:250px;">
                    <option value=""><?php _e('Select...', 'gamipress-recurring-rewards'); ?></option>
                </select>

                <button type="button" id="add-requirement-add" class="button button-primary"><?php _e('Add Requirement', 'gamipress-recurring-rewards'); ?></button>
            </div>
            
            <div id="requirements-container-add" style="margin-top:15px;"></div>
            
            <?php submit_button( __( 'Add Recurring Reward', 'gamipress-recurring-rewards' ), 'primary', 'grr_add_submit' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Executes the recurring reward award cycle through AJAX.
 *
 * @since 1.0.0
 * @return void
 */
function gamipress_recurring_rewards_execute_award_cycle_ajax() {

    check_ajax_referer( 'grr_admin_nonce', '_wpnonce' );

    if ( ! gamipress_recurring_rewards_current_user_can_manage() ) {
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-recurring-rewards' ), 403 );
    }

    gamipress_recurring_rewards_award_points();
    wp_send_json_success( array(
        'message' => __( 'Award cycle executed.', 'gamipress-recurring-rewards' ),
        'timestamp' => current_time( 'mysql' )
    ) );
}
add_action( 'wp_ajax_gamipress_recurring_rewards_execute_award_cycle', 'gamipress_recurring_rewards_execute_award_cycle_ajax' );

/**
 * Displays recurring rewards statistics on the admin list screen.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_notices', function() {
    global $pagenow;
    if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'gamipress_recurring_rewards' ) {
        if ( ! gamipress_recurring_rewards_current_user_can_manage() ) {
            return;
        }

        global $wpdb;

        $rewards_table = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_rewards' );
        $users_table = gamipress_recurring_rewards_get_table_name( 'gamipress_recurring_reward_users' );

        if ( empty( $rewards_table ) || empty( $users_table ) ) {
            return;
        }

        $reward_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$rewards_table}" );
        $user_access_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$users_table} rru INNER JOIN {$rewards_table} rr ON rru.recurring_reward_id = rr.recurring_reward_id WHERE rru.active = 1" );

        echo '<div class="notice notice-info" style="margin:15px 0;padding:15px;"><p>';
        echo '<strong>' . __( 'Recurring Rewards:', 'gamipress-recurring-rewards' ) . '</strong> ';
        echo sprintf( __( '%d active rewards, %d users with access', 'gamipress-recurring-rewards' ), $reward_count, $user_access_count );
        echo '</p></div>';
    }
} );
