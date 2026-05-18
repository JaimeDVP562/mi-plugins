<?php
/**
 * Points-Based Ranks Tool
 *
 * @package     GamiPress\Admin\Tools\Points_Based_Ranks
 *
 * This file defines the meta box configuration for the Points-Based Ranks Builder tool.
 * It creates the form fields displayed in GamiPress → Tools page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Points-Based Ranks Builder meta box.
 *
 * Creates a meta box configuration that adds the rank creation tool to the
 * GamiPress Tools page. Defines all form fields for configuring rank generation.
 *
 * @since 1.0.0
 *
 * @param array $meta_boxes Existing meta boxes array from GamiPress.
 * @return array Modified meta boxes array with our tool added.
 */
function gamipress_points_based_ranks_tool_meta_boxes( $meta_boxes ) {

    $points_types_options = array(
        '' => 'Choose a points type',
    );

    foreach ( gamipress_get_points_types() as $slug => $data ) {
        $points_types_options[ $slug ] = $data['plural_name'];
    }

    $rank_types_options = array(
        '' => 'Choose a rank type',
    );

    foreach ( gamipress_get_rank_types() as $slug => $data ) {
        $rank_types_options[ $slug ] = $data['plural_name'];
    }

    $meta_boxes['points-based-ranks'] = array(
        'title' => gamipress_dashicon( 'admin-tools' ) . 'Points-Based Ranks Builder',
        'fields' => array(
            'bulk_award_points_based_ranks_points_type' => array(
                'name' => 'Points Type',
                'tooltip' => 'Select the points type used for rank requirements.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'select',
                'options' => $points_types_options,
            ),
            'bulk_award_points_based_ranks_rank_type' => array(
                'name' => 'Rank Type',
                'tooltip' => 'Select the rank type where new ranks will be created.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'select',
                'options' => $rank_types_options,
            ),
            'bulk_award_points_based_ranks_step' => array(
                'name' => 'Points Step',
                'tooltip' => 'Enter the points increment between each rank requirement.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => 1,
                ),
                'default' => '1',
            ),
            'bulk_award_points_based_ranks_count' => array(
                'name' => 'Number of Ranks',
                'tooltip' => 'The total number of ranks to create, including the default rank.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'min' => 1,
                ),
                'default' => '1',
            ),
            'bulk_award_points_based_ranks_name_pattern' => array(
                'name' => 'Name Pattern',
                'tooltip' => 'Name label with {number} and {letter}. Example: "Level {number}" or "Level {letter}".',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'text',
                'default' => 'Level {number}',
            ),
            'bulk_award_points_based_ranks_generate_images' => array(
                'name' => 'Generate Rank Images',
                'tooltip' => 'Create rank images using the GamiPress badge builder.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'checkbox',
                'default' => false,
            ),
            'bulk_award_points_based_ranks_badge_color' => array(
                'name' => 'Primary Color',
                'tooltip' => 'Choose the primary color for the generated image.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'colorpicker',
                'default' => '#2196f3',
            ),
            'bulk_award_points_based_ranks_badge_stroke_color' => array(
                'name' => 'Secondary Color',
                'tooltip' => 'Choose the secondary color for the border and icon.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'colorpicker',
                'default' => '#ffffff',
            ),
            'bulk_award_points_based_ranks_badge_text_color' => array(
                'name' => 'Text Color',
                'tooltip' => 'Choose the text color for the generated image.',
                'label_cb' => 'cmb_tooltip_label_cb',
                'type' => 'colorpicker',
                'default' => '#ffffff',
            ),
            'bulk_award_points_based_ranks_button' => array(
                'label' => 'Execute',
                'type' => 'button',
                'button' => 'primary',
            ),
        ),
    );

    return $meta_boxes;
}
