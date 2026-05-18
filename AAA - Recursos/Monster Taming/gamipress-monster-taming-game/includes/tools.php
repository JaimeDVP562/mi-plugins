<?php
/**
 * Tools
 *
 * @package     GamiPress\Monster_Taming_Game\Tools
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register tools section
 *
 * @since 1.0.0
 *
 * @param array $sections
 *
 * @return array
 */
function gamipress_monster_taming_game_tools_sections( $sections ) {

    $sections['addons'] = array(
        'title' => __( 'Add-ons', 'gamipress-monster-taming-game' ),
        'icon' => 'dashicons-admin-plugins',
    );

    return $sections;
}
add_filter( 'gamipress_tools_sections', 'gamipress_monster_taming_game_tools_sections' );

/**
 * Register tool meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_monster_taming_game_tool_meta_boxes( $meta_boxes ) {

    $meta_boxes['monster-taming-game'] = array(
        'title' => gamipress_dashicon( 'pets' ) . __( 'Monster Taming Game', 'gamipress-monster-taming-game' ),
        'fields' => apply_filters( 'gamipress_monster_taming_game_tool_fields', gamipress_monster_taming_game_tool_fields() )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_tools_addons_meta_boxes', 'gamipress_monster_taming_game_tool_meta_boxes' );

/**
 * Tool fields
 *
 * @since  1.0.0
 *
 * @return array
 */
function gamipress_monster_taming_game_tool_fields() {

    $points_types = gamipress_get_points_types();
    $achievement_types = gamipress_get_achievement_types();

    $points_types_options = array( '' => __( 'Create a new type', 'gamipress-monster-taming-game' ) );

    foreach( $points_types as $slug => $data ) {
        $points_types_options[$slug] = $data['plural_name'];
    }

    $achievement_types_options = array( '' => __( 'Create a new type', 'gamipress-monster-taming-game' ) );

    foreach( $achievement_types as $slug => $data ) {
        $achievement_types_options[$slug] = $data['plural_name'];
    }

    if( ! gamipress_monster_taming_game_is_license_valid() ) {
        return array(
            'monster_taming_game_licensing' => array(
                'type' => 'text',
                'render_row_cb' => 'gamipress_monster_taming_game_licensing_render_row',
            ),
        );
    }

    if( ! gamipress_monster_taming_game_resources_exists() ) {
        return array(
            'monster_taming_game_update_actions' => array(
                'desc' => __( 'Thanks for installing GamiPress - Monster Taming Game plugin. First you need to download the resources to proceed.', 'gamipress-monster-taming-game' ),
                'type' => 'multi_buttons',
                'buttons' => array(
                    'monster_taming_game_update_resources' => array(
                        'label' => __( 'Download Resources', 'gamipress-monster-taming-game' ),
                        'type' => 'button',
                        'button' => 'primary',
                    ),
                ),
            ),
        );
    }

    if( gamipress_monster_taming_game_should_update_resources() ) {
        return array(
            'monster_taming_game_update_actions' => array(
                'desc' => __( 'New resources version is available! Please, click the update button to update them.', 'gamipress-monster-taming-game' ),
                'type' => 'multi_buttons',
                'buttons' => array(
                    'monster_taming_game_update_resources' => array(
                        'label' => __( 'Update Resources', 'gamipress-monster-taming-game' ),
                        'type' => 'button',
                        'button' => 'primary',
                    ),
                ),
            ),
        );
    }

    $fields = array(
        'points_config' => array(
            'name' => __( 'Points Configuration', 'gamipress-monster-taming-game' ),
            'tooltip'   => __( 'The points configuration decides the number of points types to use in the taming system.', 'gamipress-monster-taming-game' ),
            'label_cb' => 'cmb_tooltip_label_cb',
            'type' => 'radio',
            'classes' => 'gamipress-monster-taming-game-column-options',
            'options_cb' => 'gamipress_monster_taming_game_points_config_options_cb',
            'default' => '1',
        ),
        'points_style' => array(
            'name' => __( 'Points Style', 'gamipress-monster-taming-game' ),
            'tooltip'   => __( 'Set the points style.', 'gamipress-monster-taming-game' ),
            'label_cb' => 'cmb_tooltip_label_cb',
            'type' => 'radio',
            'classes_cb' => 'cmb_conditional_fields_classes_cb',
            'custom_classes'   => 'gamipress-monster-taming-game-column-options',
            'hide_if' => array( 'points_config' => '1' ),
            'options_cb' => 'gamipress_monster_taming_game_points_style_options_cb',
            'default' => 'gachapon',
        ),
        'points_multiplier' => array(
            'name' => __( 'Points Multiplier', 'gamipress-monster-taming-game' ),
            'tooltip'   => __( 'Factor to multiply the points value.', 'gamipress-monster-taming-game' ),
            'label_cb' => 'cmb_tooltip_label_cb',
            'type' => 'text_small',
            'attributes' => array(
                'type' => 'number',
                'min' => '1',
                'step' => '1',
            ),
            'default' => '1',

        ),
        'preview' => array(
            'name' => __( 'Preview', 'gamipress-monster-taming-game' ),
            'tooltip'   => __( 'Preview how the monsters will be configured based on the points type(s) options and multiplier.', 'gamipress-monster-taming-game' ),
            'label_cb' => 'cmb_tooltip_label_cb',
            'type' => 'text',
            'attributes' => array(
                'type' => 'hidden',
            ),
            'after_field' => 'gamipress_monster_taming_game_preview_after_field',
        ),
        'points_type_title' => array(
            'name' => __( 'Points Types', 'gamipress-monster-taming-game' ),
            'type' => 'title',
        ),
    );

    for( $i = 0; $i < 6; $i++ ) {

        $fields['points_type_' . $i] = array(
            'name' => '<span class="points-type-' . $i . '"></span> ',
            'tooltip'   => sprintf( __( 'Choose the points type for %s.', 'gamipress-monster-taming-game' ), '<span class="points-type-' . $i . '"></span>' )
                . '<br><br>' . __( 'Leave "Create a new type" for the first run or choose another type if you want to setup this points under a desired type.', 'gamipress-monster-taming-game' ),
            'label_cb' => 'cmb_tooltip_label_cb',
            'classes' => 'gamipress-monster-taming-game-row gamipress-monster-taming-game-row-first',
            'type' => 'select',
            'options' => $points_types_options,
            'default' => '',
        );
        $fields['points_type_' . $i . '_titles'] = array(
            'name' => __( 'Update', 'gamipress-monster-taming-game' ),
            'desc' => __( 'Labels', 'gamipress-monster-taming-game' ),
            'tooltip'   => __( 'Check the fields to update on the points type selected.', 'gamipress-monster-taming-game' ),
            'label_cb' => 'cmb_tooltip_label_cb',
            'type' => 'checkbox',
            'classes_cb' => 'cmb_conditional_fields_classes_cb',
            'custom_classes'   => 'gamipress-switch gamipress-monster-taming-game-row',
            'hide_if' => array( 'points_type_' . $i => '' ),
            'default' => 'yes',

        );
        $fields['points_type_' . $i . '_slugs'] = array(
            'desc' => __( 'Slug', 'gamipress-monster-taming-game' ),
            'type' => 'checkbox',
            'classes_cb' => 'cmb_conditional_fields_classes_cb',
            'custom_classes'   => 'gamipress-switch gamipress-monster-taming-game-row',
            'hide_if' => array( 'points_type_' . $i => '' ),
            'default' => 'yes',

        );
        $fields['points_type_' . $i . '_images'] = array(
            'desc' => __( 'Image', 'gamipress-monster-taming-game' ),
            'type' => 'checkbox',
            'classes_cb' => 'cmb_conditional_fields_classes_cb',
            'custom_classes'   => 'gamipress-switch gamipress-monster-taming-game-row',
            'hide_if' => array( 'points_type_' . $i => '' ),
            'default' => 'yes'
        );

    }

    $fields['achievement_type_title'] = array(
        'name' => __( 'Achievement Types', 'gamipress-monster-taming-game' ),
        'type' => 'title',
        'classes' => 'clear',
    );
    $fields['achievement_type'] = array(
        'name' => '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . '/monsters/66.png">' . __( 'Monsters', 'gamipress-monster-taming-game' ),
        'tooltip'   => __( 'Choose the achievement type for Monsters.', 'gamipress-monster-taming-game' )
         . '<br>' . __( '100 monsters will be created (or updated) as achievements.', 'gamipress-monster-taming-game' )
         . '<br><br>' . __( 'Leave "Create a new type" for the first run or choose another type if you want to setup the monsters under a desired type.', 'gamipress-monster-taming-game' ),
        'label_cb' => 'cmb_tooltip_label_cb',
        'classes' => 'gamipress-monster-taming-game-row gamipress-monster-taming-game-row-first',
        'type' => 'select',
        'options' => $achievement_types_options,
        'default' => '',
    );
    $fields['achievement_type_titles'] = array(
        'name' => __( 'Update', 'gamipress-monster-taming-game' ),
        'desc' => __( 'Titles', 'gamipress-monster-taming-game' ),
        'tooltip'   => __( 'Check the fields to update when duplicates are found.', 'gamipress-monster-taming-game' ),
        'label_cb' => 'cmb_tooltip_label_cb',
        'type' => 'checkbox',
        'classes_cb' => 'cmb_conditional_fields_classes_cb',
        'custom_classes'   => 'gamipress-switch gamipress-monster-taming-game-row',
        'hide_if' => array( 'achievement_type' => '' ),
        'default' => 'yes',

    );

    $fields['achievement_type_contents'] = array(
        'desc' => __( 'Descriptions', 'gamipress-monster-taming-game' ),
        'type' => 'checkbox',
        'classes_cb' => 'cmb_conditional_fields_classes_cb',
        'custom_classes'   => 'gamipress-switch gamipress-monster-taming-game-row',
        'hide_if' => array( 'achievement_type' => '' ),
        'default' => 'yes',

    );

    $fields['achievement_type_images'] = array(
        'desc' => __( 'Images', 'gamipress-monster-taming-game' ),
        'type' => 'checkbox',
        'classes_cb' => 'cmb_conditional_fields_classes_cb',
        'custom_classes'   => 'gamipress-switch gamipress-monster-taming-game-row',
        'hide_if' => array( 'achievement_type' => '' ),
        'default' => 'yes'
    );

    $fields['monster_taming_game_actions'] = array(
        'type' => 'multi_buttons',
        'buttons' => array(
            'monster_taming_game_run' => array(
                'label' => __( 'Run now', 'gamipress-monster-taming-game' ),
                'type' => 'button',
                'button' => 'primary',
            ),
        ),
    );

    return $fields;

}

/**
 * Licensing row
 *
 * @since  1.0.0
 */
function gamipress_monster_taming_game_licensing_render_row() {

    echo '<p><strong>'
            . __( 'You need to enter a valid license', 'gamipress-monster-taming-game' )
        . '</strong></p>';
    echo '<p>'
        . __( 'This tool requires a valid license to download the required resources.', 'gamipress-monster-taming-game' )
        . '&nbsp;'
        . sprintf( __( 'Please, %s.', 'gamipress-monster-taming-game' ),
            '<a href="' . esc_attr( admin_url( 'admin.php?page=gamipress_licenses' ) ) . '">' . __( 'enter and activate your license here', 'gamipress-monster-taming-game' ) . '</a>' )
        . '</p>';

}

/**
 * Points config options
 *
 * @since  1.0.0
 *
 * @return array
 */
function gamipress_monster_taming_game_points_config_options_cb() {

    $html_1 = '<strong>' . __( '1 Points Type (Simple)', 'gamipress-monster-taming-game' ) . '</strong>'
        . '<span class="cmb-desc">' . __( 'The most simple version, just 1 points type to unlock monsters.', 'gamipress-monster-taming-game' ) . '</span>'
        . '<small class="cmb-desc">' . __( 'Preview:', 'gamipress-monster-taming-game' ) . '</small>'
        . '<span>' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/coin.png">' . __( 'Monster Coins', 'gamipress-monster-taming-game' ) . '</span>'
    ;

    $html_5 = '<strong>' . __( '5 Points Types (Advanced)', 'gamipress-monster-taming-game' ) . '</strong>'
        . '<span class="cmb-desc">' . __( 'Monster get unlocked using 5 different points types.', 'gamipress-monster-taming-game' ) . '</span>'
        . '<small class="cmb-desc">' . __( 'Preview:', 'gamipress-monster-taming-game' ) . '</small>'
        . '<span>' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/1.png">'
        . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/2.png">'
        . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/3.png">'
        . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/4.png">'
        . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/5.png">'  . '</span>'
    ;

    $html_6_desc = '';

    if( class_exists( 'GamiPress_Points_Exchanges' ) ) {
        $html_6_desc = sprintf( __( 'Exchanges for %s add-on will be configured automatically!', 'gamipress-monster-taming-game' ),
            '<a href="https://gamipress.com/add-ons/gamipress-points-exchanges/">'  .__( 'Points Exchanges', 'gamipress-monster-taming-game' ) . '</a>');
    } else {
        $html_6_desc = sprintf( __( '%s compatible! If you do not have this add-on, you may need to define rules on each type for the exchange.', 'gamipress-monster-taming-game' ),
            '<a href="https://gamipress.com/add-ons/gamipress-points-exchanges/">'  .__( 'Points Exchanges', 'gamipress-monster-taming-game' ) . '</a>');
    }

    $html_6 = '<strong>' . __( '6 Points Types (Mixed)', 'gamipress-monster-taming-game' ) . '</strong>'
        . '<span class="cmb-desc">' . __( '1 points type acts like money to acquire the other types.', 'gamipress-monster-taming-game' ) . '</span>'
        . '<small class="cmb-desc">' . __( 'Preview:', 'gamipress-monster-taming-game' ) . '</small>'
        . '<span>'
            . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/potion/2.png">' . __( 'Red Potion', 'gamipress-monster-taming-game' )
            . ' ' . __( 'for', 'gamipress-monster-taming-game' )
            . ' 10 <img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/coin.png">' . __( 'Monster Coins', 'gamipress-monster-taming-game' ) . '</span>'
        . '<span class="cmb-desc">'
            . $html_6_desc
        . '</span>'
    ;

    return  array(
        '1' => $html_1,
        '5' => $html_5 . '<div class="clear"></div>',
        '6' => $html_6,
    );
}

/**
 * Points style options
 *
 * @since  1.0.0
 *
 * @return array
 */
function gamipress_monster_taming_game_points_style_options_cb() {

    $html_gachapon = '<strong>' . __( 'Gachapons', 'gamipress-monster-taming-game' ) . '</strong>'
        . '<span class="cmb-desc">' . __( 'For a monster catching feel.', 'gamipress-monster-taming-game' ) . '</span>'
        . '<small class="cmb-desc">' . __( 'Preview:', 'gamipress-monster-taming-game' ) . '</small>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/1.png">' . __( 'Blue Gachapon', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/2.png">' . __( 'Red Gachapon', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/3.png">' . __( 'Purple Gachapon', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/4.png">' . __( 'Green Gachapon', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/gachapon/5.png">' . __( 'Yellow Gachapon', 'gamipress-monster-taming-game' )  . '</span>'
    ;

    $html_potion = '<strong>' . __( 'Potions', 'gamipress-monster-taming-game' ) . '</strong>'
        . '<span class="cmb-desc">' . __( 'For a potion alchemy feel.', 'gamipress-monster-taming-game' ) . '</span>'
        . '<small class="cmb-desc">' . __( 'Preview:', 'gamipress-monster-taming-game' ) . '</small>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/potion/1.png">' . __( 'Blue Potion', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/potion/2.png">' . __( 'Red Potion', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/potion/3.png">' . __( 'Purple Potion', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/potion/4.png">' . __( 'Green Potion', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/potion/5.png">' . __( 'Yellow Potion', 'gamipress-monster-taming-game' )  . '</span>'
    ;

    $html_food = '<strong>' . __( 'Food', 'gamipress-monster-taming-game' ) . '</strong>'
        . '<span class="cmb-desc">' . __( 'For a monster feeding feel.', 'gamipress-monster-taming-game' ) . '</span>'
        . '<small class="cmb-desc">' . __( 'Preview:', 'gamipress-monster-taming-game' ) . '</small>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/food/1.png">' . __( 'Fish', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/food/2.png">' . __( 'Meat', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/food/3.png">' . __( 'Vegetable', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/food/4.png">' . __( 'Fruit', 'gamipress-monster-taming-game' )  . '</span>'
        . '<span class="col-2">' . '<img src="' . GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL . 'points/food/5.png">' . __( 'Lacteal', 'gamipress-monster-taming-game' )  . '</span>'
        ;

    return  array(
        'gachapon' => $html_gachapon . '<div class="clear"></div>',
        'potion' => $html_potion . '<div class="clear"></div>',
        'food' => $html_food . '<div class="clear"></div>',
    );
}

/**
 * Preview
 *
 * @since  1.0.0
 */
function gamipress_monster_taming_game_preview_after_field() {
    ?>
    <div class="gamipress-monster-taming-game-preview">
        <div class="gamipress-monster-taming-game-preview-item">
            <div class="gamipress-monster-taming-game-preview-item-content">
                <img src="<?php echo GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL; ?>/monsters/3.png">
                <strong><?php esc_html_e( 'Batli', 'gamipress-monster-taming-game' ); ?></strong>
                <p><?php echo sprintf( __( 'Requires %s to unlock', 'gamipress-monster-taming-game' ), '<span class="points-preview-1"></span>' ); ?></p>
            </div>
        </div>
        <div class="gamipress-monster-taming-game-preview-item">
            <div class="gamipress-monster-taming-game-preview-item-content">
                <img src="<?php echo GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL; ?>/monsters/2.png">
                <strong><?php esc_html_e( 'Appligore', 'gamipress-monster-taming-game' ); ?></strong>
                <p><?php echo sprintf( __( 'Requires %s to unlock', 'gamipress-monster-taming-game' ), '<span class="points-preview-2"></span>' ); ?></p>
            </div>
        </div>
        <div class="gamipress-monster-taming-game-preview-item">
            <div class="gamipress-monster-taming-game-preview-item-content">
                <img src="<?php echo GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL; ?>/monsters/6.png">
                <strong><?php esc_html_e( 'Fluffy', 'gamipress-monster-taming-game' ); ?></strong>
                <p><?php echo sprintf( __( 'Requires %s to unlock', 'gamipress-monster-taming-game' ), '<span class="points-preview-3"></span>' ); ?></p>
            </div>
        </div>
    </div>
    <?php
}