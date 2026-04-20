<?php
/**
 * Editor Controls
 *
 * @package     BBForms\Editor_Controls
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Render editor controls
 *
 * @since 1.0.0
 */
function bbforms_render_editor_controls( $for = 'form' ) {

    $sections = array();

    switch ( $for ) {
        case 'form':
            $sections['fields'] = bbforms_get_fields();
            break;
        case 'actions':
            $sections['actions'] = bbforms_get_actions();
            break;
        case 'options':
            $sections['options'] = bbforms_get_options();
            break;
    }

    $sections = apply_filters( 'bbforms_editor_controls_main_sections', $sections, $for );

    switch ( $for ) {
        case 'form':
            $sections['bbcodes'] = bbforms_get_bbcodes();
            $sections['tags'] = bbforms_get_tags();
            unset( $sections['tags']['fields'] );
            break;
        case 'actions':
            $sections['bbcodes'] = bbforms_get_bbcodes();
            $sections['tags'] = bbforms_get_tags();
            break;
        case 'options':
            $sections['bbcodes'] = bbforms_get_bbcodes();
            $sections['tags'] = bbforms_get_tags();
            unset( $sections['tags']['fields'] );
            break;
    }

    $sections = apply_filters( 'bbforms_editor_controls_sections', $sections, $for );

    $groups = array(
        'fields' => array(
            'label' => __( 'Fields', 'bbforms' ),
            'icon' => 'edit',
        ),
        'actions' => array(
            'label' => __( 'Actions', 'bbforms' ),
            'icon' => 'controls-forward',
        ),
        'options' => array(
            'label' => __( 'Options', 'bbforms' ),
            'icon' => 'admin-tools',
        ),
        'bbcodes' => array(
            'label' => __( 'BBCodes', 'bbforms' ),
            'icon' => 'bbforms-bbcodes',
        ),
        'tags' => array(
            'label' => __( 'Tags', 'bbforms' ),
            'icon' => 'bbforms-tags',
        ),
    );

    $groups = apply_filters( 'bbforms_editor_controls_groups', $groups, $for );

    $fields_sections    = bbforms_editor_controls_get_fields_sections_labels( $for );
    $fields_order       = bbforms_editor_controls_get_fields_order( $for );
    $bbcodes_sections   = bbforms_editor_controls_get_bbcodes_sections_labels( $for );
    $bbcodes_order      = bbforms_editor_controls_get_bbcodes_order( $for );
    $actions_sections   = bbforms_editor_controls_get_actions_sections_labels( $for );
    $actions_order      = bbforms_editor_controls_get_actions_order( $for );

    ?>

    <div class="bbforms-editor-controls">

        <?php // Titles ?>
        <div class="bbforms-editor-control-section-titles">
            <?php foreach ( $groups as $group => $args ) : ?>
                <?php if( isset( $sections[$group] ) ) :
                    $label = $args['label'];
                    $icon = $args['icon'];
                    $is_main_group = in_array( $group, array( 'fields', 'actions', 'options' ) );
                    $dashicon = $is_main_group ? 'arrow-down' : 'arrow-up' ?>
                    <span class="bbforms-editor-control-section-title bbforms-editor-control <?php if ( $is_main_group ) : ?>bbforms-editor-control-active<?php endif; ?>"
                          data-toggle=".bbforms-editor-controls-<?php echo esc_attr( $group ) ; ?>-section-<?php echo esc_attr( $for ) ; ?>"
                    ><?php echo bbforms_dashicon( $icon ) . ' ' . esc_html( $label ) . ' ' . bbforms_dashicon( $dashicon ); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php // Controls ?>

        <?php do_action( 'bbforms_editor_controls_before_render_main_sections', $sections, $for ); ?>


        <?php if( isset( $sections['fields'] ) ) : ?>
            <?php bbforms_editor_control_render_editor_section( $sections['fields'], $fields_sections, $fields_order, $for, 'fields' ); ?>
        <?php endif; ?>

        <?php if( isset( $sections['actions'] ) ) : ?>
            <?php bbforms_editor_control_render_editor_section( $sections['actions'], $actions_sections, $actions_order, $for, 'actions' ); ?>
        <?php endif; ?>

        <?php if( isset( $sections['options'] ) ): ?>
            <div class="bbforms-editor-controls-section bbforms-editor-controls-options-section bbforms-editor-controls-options-section-<?php echo esc_attr( $for ) ; ?>">
                <span class="bbforms-editor-controls-options">
                    <span class="bbforms-editor-control bbforms-editor-control-option"
                          data-option="all"
                          data-open=".bbforms-load-default-options-dialog"
                          title="<?php echo esc_attr( __( 'Restore all form options to their default configuration.', 'bbforms' ) ); ?>"
                    ><?php echo esc_html( __( 'Load Default Options', 'bbforms' ) ); ?></span>
                    <span class="bbforms-editor-controls-group-title">|</span>
                    <span class="bbforms-editor-control bbforms-editor-control-option"
                          data-option="info"
                          data-open=".bbforms-options-help-dialog"
                          title="<?php echo esc_attr( __( 'Options Help', 'bbforms' ) ); ?>"
                    ><?php echo esc_html( __( 'Help', 'bbforms' ) ) . ' ' . bbforms_dashicon( 'editor-help' ); ?></span>
                </span>
            </div>
        <?php endif; ?>

        <?php do_action( 'bbforms_editor_controls_after_render_main_sections', $sections, $for ); ?>

        <?php do_action( 'bbforms_editor_controls_before_render_sections', $sections, $for ); ?>

        <?php if( isset( $sections['bbcodes'] ) ): ?>
            <?php bbforms_editor_control_render_editor_section( $sections['bbcodes'], $bbcodes_sections, $bbcodes_order, $for, 'bbcodes' ); ?>
        <?php endif; ?>

        <?php if( isset( $sections['tags'] ) ) : ?>
            <div class="bbforms-editor-controls-section bbforms-editor-controls-tags-section bbforms-editor-controls-tags-section-<?php echo esc_attr( $for ) ; ?>" style="display: none;">
            <span class="bbforms-editor-controls-tags">
                <?php foreach ( $sections['tags'] as $tag_group => $tag_group_args ) : ?>

                    <?php echo '<span class="bbforms-editor-control bbforms-editor-control-field" '
                        . 'data-tag-group="' . esc_attr( $tag_group ) . '" '
                        . 'data-toggle=".bbforms-editor-control-dropdown-' . esc_attr( $tag_group ) . '-' . esc_attr( $for ) . '" '
                        . 'title="' . esc_attr( $tag_group_args['label'] ) . '" '
                        .'>' . bbforms_dashicon( $tag_group_args['icon'] ) . ' ' . esc_html( $tag_group_args['label'] )  . ''; ?>

                    <?php echo '<span class="bbforms-editor-control-dropdown bbforms-editor-control-dropdown-' . esc_attr( $tag_group ) . '-' . esc_attr( $for ) . '" style="display: none;">'; ?>

                    <?php foreach ( $tag_group_args['tags'] as $tag_key => $tag ) :
                            if( $tag_key === 'field.FIELD_NAME' ) continue;
                            // ensure required elements
                            if( ! isset( $tag['label'] ) ) $tag['label'] = '{' . $tag_key . '}';
                            if( ! isset( $tag['preview'] ) ) $tag['preview'] = '';
                            ?>
                        <?php echo '<span class="bbforms-editor-control bbforms-editor-control-dropdown-option bbforms-editor-control-dropdown-' . esc_attr( $tag_key ) . '-' . esc_attr( $for ) . '-option cm-s-default" '
                            . 'data-insert="' . esc_attr( '{' . $tag_key . '}' ) . '" '
                            . 'title="' . esc_attr( $tag['label'] ) . '" '
                            .'>'
                            . '<small>' . esc_html( $tag['label'] ) . ': </small>'
                            . '<span class="cm-def">{' . esc_html( $tag_key ) . '}</span>'
                            . '<br>'
                            . '<small>' . esc_html__( 'Preview', 'bbforms' ) . ': </small>'
                            . '<small class="cm-comment">' . esc_html( $tag['preview'] ) . '</small>'
                            . '</span>'; ?>
                    <?php endforeach; ?>
                    <?php echo '</span>'; ?>
                    <?php echo '</span>'; ?>
                <?php endforeach; ?>
                <span class="bbforms-editor-controls-group-title">|</span>
                <span class="bbforms-editor-control bbforms-editor-control-option"
                      data-option="info"
                      data-open=".bbforms-tags-help-dialog"
                      title="<?php echo esc_attr( __( 'Tags Help', 'bbforms' ) ); ?>"
                ><?php echo esc_html( __( 'Help', 'bbforms' ) ) . ' ' . bbforms_dashicon( 'editor-help' ); ?></span>
            </span>
            </div>
        <?php endif; ?>


        <?php do_action( 'bbforms_editor_controls_after_render_sections', $sections, $for ); ?>

    </div>

    <?php

}

/**
 * Get bbcodes order and icons for the editor controls
 *
 * @since 1.0.0
 *
 * @param string $for
 *
 * @return array
 */
function bbforms_editor_controls_get_bbcodes_sections_labels( $for = 'form' ) {

    $bbcodes_sections_labels = array(
        'layout' => array(
            'label'         => __( 'Layout', 'bbforms' ),
            'icon'          => 'screenoptions',
        ),
        'alignment' => array(
            'label'         => __( 'Alignment', 'bbforms' ),
            'icon'          => 'editor-alignleft',
        ),
        'decoration' => array(
            'label'         => __( 'Decoration', 'bbforms' ),
            'icon'          => 'editor-bold',
        ),
        'formatting' => array(
            'label'         => __( 'Formatting', 'bbforms' ),
            'icon'          => 'color-picker',
        ),
        'embed' => array(
            'label'         => __( 'Embed', 'bbforms' ),
            'icon'          => 'bbforms-submit',
        ),
        'add_ons' => array(
            'label'         => __( 'Add-ons', 'bbforms' ),
            'icon'          => 'admin-plugins',
        ),
    );

    return apply_filters( 'bbforms_editor_controls_bbcodes_sections_labels', $bbcodes_sections_labels, $for );
}

/**
 * Get BBCodes order and icons for the editor controls
 *
 * @since 1.0.0
 *
 * @param string $for
 *
 * @return array
 */
function bbforms_editor_controls_get_bbcodes_order( $for = 'form' ) {

    $bbcodes_order = array(
        'layout' => array(
            'row'       => 'screenoptions',
            'table'     => 'editor-table',
        ),
        'alignment' => array(
            'left'      => 'editor-alignleft',
            'center'    => 'editor-aligncenter',
            'right'     => 'editor-alignright',
            'justify'   => 'editor-justify',
        ),
        'decoration' => array(
            'b'         => 'editor-bold',
            'i'         => 'editor-italic',
            'u'         => 'editor-underline',
            's'         => 'editor-strikethrough',
        ),
        'formatting' => array(
            'font'      => 'editor-textcolor',
            'size'      => 'editor-paragraph',
            'color'     => 'color-picker',
            'quote'     => 'format-quote',
            'list'      => 'editor-ul',
            'code'      => 'shortcode',
        ),
        'embed' => array(
            'email'     => 'email',
            'iframe'    => 'editor-code',
            'img'       => 'images-alt',
            'url'       => 'admin-links',
            'youtube'   => 'youtube',
        ),
        'add_ons' => array(), // Section for add-ons
    );

    return apply_filters( 'bbforms_editor_controls_bbcodes_order', $bbcodes_order, $for );

}

/**
 * Get fields order and icons for the editor controls
 *
 * @since 1.0.0
 *
 * @param string $for
 *
 * @return array
 */
function bbforms_editor_controls_get_fields_sections_labels( $for = 'form' ) {

    $fields_sections_labels = array(
        'inputs' => array(
            'label'         => __( 'Inputs', 'bbforms' ),
            'icon'          => 'bbforms-text',
        ),
        'numerics' => array(
            'label'         => __( 'Numerics', 'bbforms' ),
            'icon'          => 'bbforms-number',
        ),
        'options' => array(
            'label'         => __( 'Options', 'bbforms' ),
            'icon'          => 'bbforms-check',
        ),
        'specials' => array(
            'label'         => __( 'Specials', 'bbforms' ),
            'icon'          => 'star-filled',
        ),
        'buttons' => array(
            'label'         => __( 'Buttons', 'bbforms' ),
            'icon'          => 'bbforms-submit',
        ),
        'add_ons' => array(
            'label'         => __( 'Add-ons', 'bbforms' ),
            'icon'          => 'admin-plugins',
        ),
    );

    return apply_filters( 'bbforms_editor_controls_fields_sections_labels', $fields_sections_labels, $for );
}

/**
 * Get fields order and icons for the editor controls
 *
 * @since 1.0.0
 *
 * @param string $for
 *
 * @return array
 */
function bbforms_editor_controls_get_fields_order( $for = 'form' ) {

    $fields_order = array(
        'inputs' => array(
            'text'          => 'bbforms-text',
            'textarea'      => 'bbforms-textarea',
            'email'         => 'email',
            'tel'           => 'phone',
            'url'           => 'admin-links',
            'password'      => 'admin-network',
            'date'          => 'calendar-alt',
            'time'          => 'clock',
            'file'          => 'bbforms-file',
        ),
        'numerics' => array(
            'number'        => 'bbforms-number',
            'range'         => 'bbforms-range-circle',
        ),
        'options' => array(
            'check'         => 'bbforms-check',
            'radio'         => 'bbforms-radio',
            'select'        => 'bbforms-select',
            'country'       => 'flag',
            'quiz'          => 'bbforms-quiz',
        ),
        'specials' => array(
            'hidden'        => 'hidden',
            'honeypot'      => 'buddicons-replies',
        ),
        'buttons' => array(
            'submit'        => 'bbforms-submit',
            'reset'         => 'bbforms-reset',
        ),
        'add_ons' => array(), // Section for add-ons
    );

    return apply_filters( 'bbforms_editor_controls_fields_order', $fields_order, $for );

}

/**
 * Get actions order and icons for the editor controls
 *
 * @since 1.0.0
 *
 * @param string $for
 *
 * @return array
 */
function bbforms_editor_controls_get_actions_sections_labels( $for = 'form' ) {

    $actions_sections_labels = array(
        'actions' => array(
            'label'         => __( 'Core', 'bbforms' ),
            'icon'          => 'database',
        ),
        'personal_data' => array(
            'label'         => __( 'Personal Data', 'bbforms' ),
            'icon'          => 'bbforms-user',
        ),
        'add_ons' => array(
            'label'         => __( 'Add-ons', 'bbforms' ),
            'icon'          => 'admin-plugins',
        ),
    );

    return apply_filters( 'bbforms_editor_controls_actions_sections_labels', $actions_sections_labels, $for );
}

/**
 * Get actions order and icons for the editor controls
 *
 * @since 1.0.0
 *
 * @param string $for
 *
 * @return array
 */
function bbforms_editor_controls_get_actions_order( $for = 'form' ) {

    $actions_order = array(
        'actions' =>  array(
            'record'            =>    'database-add',
            'email'             =>    'email',
            'redirect'          =>    'external',
            'message'           =>    'admin-comments',
        ),
        'personal_data' =>  array(
            'export_request'    =>    'bbforms-user-export',
            'delete_request'    =>    'bbforms-user-delete',
        ),
        'add_ons' => array(), // Section for add-ons
    );

    return apply_filters( 'bbforms_editor_controls_actions_order', $actions_order, $for );

}

/**
 * Render editor section
 *
 * @since 1.0.0
 *
 * @param array     $bbcodes
 * @param array     $sections
 * @param array     $bbcodes_order
 * @param string    $for
 * @param string    $type
 */
function bbforms_editor_control_render_editor_section( $bbcodes, $sections, $order, $for, $type ) {

    $first_key = array_keys( $sections )[0];
    $control_type = '';
    $help_title = '';
    $style = '';

    switch( $type ) {
        case 'fields':
            $control_type = 'field';
            $help_title = __( 'Fields Help', 'bbforms' );
            break;
        case 'actions':
            $control_type = 'action';
            $help_title = __( 'Actions Help', 'bbforms' );
            break;
        case 'bbcodes':
            $control_type = 'bbcode';
            $help_title = __( 'BBCodes Help', 'bbforms' );
            $style = 'display: none;';
            break;
    }

    ?>
    <div class="bbforms-editor-controls-section bbforms-editor-controls-<?php echo esc_attr( $type ); ?>-section bbforms-editor-controls-<?php echo esc_attr( $type ); ?>-section-<?php echo esc_attr( $for ); ?>" style="<?php echo esc_attr( $style ); ?>">

        <span class="bbforms-editor-controls-<?php echo esc_attr( $type ); ?>">

            <?php // Sections ?>
            <div class="bbforms-editor-control-section-titles">
                <?php foreach ( $sections as $i => $section ) : ?>
                    <?php if( count( $order[$i] ) ) :
                        $is_main_group = $i === $first_key;
                        $dashicon = $is_main_group ? 'arrow-down' : 'arrow-up' ?>
                        <span class="bbforms-editor-control-section-title bbforms-editor-control <?php if ( $is_main_group ) : ?>bbforms-editor-control-active<?php endif; ?>"
                              data-toggle=".bbforms-editor-controls-<?php echo esc_attr( $type ); ?>-<?php echo esc_attr( $i ); ?>-section-<?php echo esc_attr( $for );  ?>"
                        ><?php echo bbforms_dashicon( $section['icon'] ) . ' ' . esc_html( $section['label'] ) . ' ' . bbforms_dashicon( $dashicon ); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
             </div>

            <?php // BBCodes ?>
            <?php foreach ( $order as $section => $codes ) : ?>
            <div class="bbforms-editor-controls-<?php echo esc_attr( $type ); ?>-<?php echo esc_attr( $section ); ?>-section-<?php echo esc_attr( $for ); ?>"
                <?php if( $section !== $first_key ) : ?>style="display: none;"<?php endif; ?>>

                <?php foreach ( $codes as $bbcode => $icon ) :
                    if( $icon === '|' ) {
                        echo '<span class="bbforms-editor-controls-group-title">|</span>';
                        continue;
                    }

                    $field = $bbcodes[$bbcode];
                    unset( $bbcodes[$bbcode] );
                    ?>
                    <?php bbforms_editor_control_render_editor_control( $bbcode, $field, $for, $control_type, $order[$section] ); ?>
                <?php endforeach; ?>

                <span class="bbforms-editor-controls-group-title">|</span>
                <span class="bbforms-editor-control bbforms-editor-control-option"
                      data-option="info"
                      data-open=".bbforms-<?php echo esc_attr( $type ); ?>-help-dialog"
                      title="<?php echo esc_attr( $help_title ); ?>"
                ><?php echo esc_html( __( 'Help', 'bbforms' ) ) . ' ' . bbforms_dashicon( 'editor-help' ); ?></span>
            </div>
            <?php endforeach; ?>

            <?php // BBCodes not rendered in any section ?>
            <?php if( ! empty( $bbcodes ) ) : ?>
                <?php foreach ( $bbcodes as $bbcode => $field ) : ?>
                    <?php bbforms_editor_control_render_editor_control( $bbcode, $field, $for, $control_type ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </span>
    </div>
    <?php

}

/**
 * Render editor controls
 *
 * @since 1.0.0
 *
 * @param string    $bbcode
 * @param stdClass  $object
 * @param string    $for
 * @param string    $type
 * @param string    $icon_list
 */
function bbforms_editor_control_render_editor_control( $bbcode, $object, $for, $type, $icon_list = array() ) {

    $label = ( isset( $icon_list[$bbcode] ) ? bbforms_dashicon( $icon_list[$bbcode] ) : $bbcode );

    if( $type === 'field' || $type === 'action' ) {
        $label .=  ' ' . $bbcode;
    }

    if( $type === 'bbcode' ) {
        $bbcodes_mapping = array(
            'row' => 'columns',
            'b' => 'bold',
            'i' => 'italic',
            'u' => 'underlined',
            's' => 'strikethrough',
        );

        if( isset( $bbcodes_mapping[$bbcode] ) ) {
            $label .=  ' ' . $bbcodes_mapping[$bbcode];
        } else {
            $label .=  ' ' . $bbcode;
        }

    }

    ?>
    <?php if( is_array( $object->pattern ) ) : ?>
        <?php echo '<div class="bbforms-editor-control-wrapper">'; ?>

        <?php echo '<span class="bbforms-editor-control bbforms-editor-control-has-dropdown-toggle bbforms-editor-control-' . esc_attr( $type ) . '" '
            . 'data-' . esc_attr( $type ) . '="' . esc_attr( $bbcode ) . '" '
            . 'data-insert="' . esc_attr( $object->pattern[0]['pattern'] ) . '" '
            . 'title="[' . esc_attr( $bbcode ) . '] ' . esc_attr( $object->name ) . '" '
            .'>' . $label . '</span>'; ?>
        <?php echo '<span class="bbforms-editor-control bbforms-editor-control-dropdown-toggle bbforms-editor-control-' . esc_attr( $type ) . '" '
            . 'data-' . esc_attr( $type ) . '="' . esc_attr( $bbcode ) . '" '
            . 'data-toggle=".bbforms-editor-control-dropdown-' . esc_attr( $bbcode ) . '-' . esc_attr( $for ) . '" '
            . 'title="' . esc_attr( $object->name ) . '" '
            .'>' . bbforms_dashicon( 'arrow-down' ) . ''; ?>

        <?php echo '<span class="bbforms-editor-control-dropdown bbforms-editor-control-dropdown-' . esc_attr( $bbcode ) . '-' . esc_attr( $for ) . ' cm-s-default" style="display: none;">'; ?>

        <?php foreach ( $object->pattern as $i => $pattern ) : ?>
            <?php echo '<span class="bbforms-editor-control bbforms-editor-control-dropdown-option bbforms-editor-control-dropdown-' . esc_attr( $bbcode ) . '-' . esc_attr( $for ) . '-option bbforms-editor-control-dropdown-' . esc_attr( $bbcode ) . '-' . esc_attr( $for ) . '-option-' . esc_attr( $i ) . '" '
                . 'data-' . esc_attr( $type ) . '="' . esc_attr( $bbcode ) . '" '
                . 'data-insert="' . esc_attr( $pattern['pattern'] ) . '" '
                . 'title="' . esc_attr( $pattern['label'] ) . '" '
                .'>'
                . '<small>' . $pattern['label'] . '</small>'
                . '<span>' . bbforms_parse_pattern( $pattern['pattern'] ) . '</span>'
                . '</span>'; ?>
        <?php endforeach; ?>

        <?php echo '</span>'; ?>
        <?php echo '</span>'; ?>

        <?php echo '</div>'; ?>
    <?php else : ?>
        <?php echo '<span class="bbforms-editor-control bbforms-editor-control-' . esc_attr( $type ) . '" '
            . 'data-' . esc_attr( $type ) . '="' . esc_attr( $bbcode ) . '" '
            . 'data-insert="' . esc_attr( $object->pattern ) . '" '
            . 'title="[' . esc_attr( $bbcode ) . '] ' . esc_attr( $object->name ) . '" '
            .'>' . $label . '</span>'; ?>
    <?php endif; ?>
    <?php
}

/**
 * Parses an options to make it look like in the editor
 *
 * @since 1.0.0
 *
 * @param string    $pattern
 * @param bool      $inline
 *
 * @return string
 */
function bbforms_parse_options_pattern( $pattern, $inline = true ) {

    $parts = explode( '=', $pattern );

    if( $parts[1] === 'yes' ) {
        $parts[1] = "<span class='cm-setting-enabled'>=yes</span>";
    } else if( $parts[1] === 'no' ) {
        $parts[1] = "<span class='cm-setting-disabled'>=no</span>";
    } else {

        $value = $parts[1];

        $value = str_replace( '{', '<span class="cm-def">{', $value );
        $value = str_replace( '}', '}</span>', $value );

        $value = "<span class='cm-string'>" . $value ."</span>";

        $parts[1] = "<span class='cm-operator'>=</span>" . $value;
    }

    $pattern = "<span class='cm-attribute'>" .$parts[0] ."</span>" . $parts[1];

    if( $inline ) {
        return '<span class="cm-s-default">' . $pattern . '</span>';
    }

    return $pattern;

}

/**
 * Parses a string (commonly with BBCodes inside= to make it look like in the editor
 *
 * @since 1.0.0
 *
 * @param string    $pattern
 *
 * @return string
 */
function bbforms_parse_pattern( $pattern ) {

    $fields = array_keys( bbforms_get_fields() );
    $actions = array_keys( bbforms_get_actions() );
    $bbcodes = array_keys( bbforms_get_bbcodes() );

    $bbcodes[] = 'column';
    $bbcodes[] = 'tr';
    $bbcodes[] = 'td';

    $bbcodes = apply_filters( 'bbforms_parse_pattern_bbcodes', $bbcodes );

    $attributes = array(
        'from_name',
        'name',
        'type',
        'value',
        'label',
        'desc',
        'placeholder',
        'id',
        'class',
        'step',
        'minlength',
        'maxlength',
        'min',
        'max',
        'pattern',
        'options',
        'options_values',
        'inline',
        'cols',
        'rows',
        'multiple',
        'style',
        'align',
        'border',
        'width',
        'height',
        'accept',
        'capture',
        'media',
        'title',
        'disabled',
        'readonly',
        'autocomplete',
        'autocorrect',
        'spellcheck',
        'save_as',
        'allow',
        'allowfullscreen',
        'allowpaymentrequest',
        'csp',
        'importance',
        'referrerpolicy',
        'sandbox',
        'seamless',
        'src',
        'srcdoc',
        'frameborder',
        'longdesc',
        'marginheight',
        'marginwidth',
        'scrolling',
        // Actions
        'from',
        'reply_to',
        'to',
        'bcc',
        'cc',
        'subject',
        'attachments',
        'email',
        'success_message',
        'error_message',
        'duplicated_message',
        'anonymize',
    );

    $attributes = apply_filters( 'bbforms_parse_pattern_attributes', $attributes );

    $attributes = array_unique( $attributes );

    rsort( $attributes, SORT_STRING );

    if( strpos( $pattern, '[check' ) !== false
        || strpos( $pattern, '[radio' ) !== false
        || strpos( $pattern, '[select' ) !== false ) {
        // Remove CONTENT_VALUE flag
        // translators: %d: number
        $replacement = sprintf( __( '1|Option %d', 'bbforms' ), 1 ) . "\n"
            // translators: %d: number
            . sprintf( __( '2|Option %d', 'bbforms' ), 2 );
        $pattern = str_replace( 'CONTENT_VALUE', $replacement, $pattern );

        // Remove CONTENT flag
        // translators: %d: number
        $replacement = sprintf( __( 'Option %d', 'bbforms' ), 1 ) . "\n"
            // translators: %d: number
            . sprintf( __( 'Option %d', 'bbforms' ), 2 ) . "\n";
        $pattern = str_replace( 'CONTENT' . "\n", $replacement, $pattern );

        // Remove CONTENT flag (Single option)
        // translators: %d: number
        $replacement = sprintf( __( 'Option %d', 'bbforms' ), 1 );
        $pattern = str_replace( 'CONTENT', $replacement, $pattern );
    }

    if( strpos( $pattern, '[quiz' ) !== false ) {
        // Remove CONTENT_MATH flag
        $replacement = 'X + Y=|Z' . "\n"
            . 'X + Y - Z=|Z';
        $pattern = str_replace( 'CONTENT_MATH', $replacement, $pattern );

        // Remove CONTENT flag
        // translators: %d: number
        $replacement = sprintf( __( 'Question %d', 'bbforms' ), 1 ) . '|' . sprintf( __( 'Answer %d', 'bbforms' ), 1 ) . "\n"
            // translators: %d: number
            . sprintf( __( 'Question %d', 'bbforms' ), 2 ) . '|' . sprintf( __( 'Answer %d', 'bbforms' ), 2 );
        $pattern = str_replace( 'CONTENT', $replacement, $pattern );

    }

    if( strpos( $pattern, '[email' ) !== false && strpos( $pattern, 'CONTENT_FIELDS_TABLE' ) !== false ) {
        $replacement = '{fields_table}';
        $pattern = str_replace( 'CONTENT_FIELDS_TABLE', $replacement, $pattern );
    }

    $pattern = str_replace( 'CONTENT', '', $pattern ); // Remove CONTENT flag
    $pattern = str_replace( '="', "=<span class='cm-string'>\"", $pattern );
    $pattern = str_replace( '" ', "\"</span> ", $pattern );
    $pattern = str_replace( '"]', "\"</span><span class='cm-literal cm-literal-close'>]</span>", $pattern );
    $pattern = str_replace( '*', "<span class='cm-required'>*</span>", $pattern );
    //$pattern = str_replace( '[/', "<span class='cm-tag'>[/</span>", $pattern );
    //$pattern = str_replace( '[', "<span class='cm-tag'>[</span>", $pattern );
    //$pattern = str_replace( ']', "<span class='cm-tag'>]</span>", $pattern );
    $pattern = str_replace( '{', '<span class="cm-def">{', $pattern );
    $pattern = str_replace( '}', '}</span>', $pattern );

    $bbcodes = array_merge( $fields, $actions, $bbcodes );

    $bbcodes = array_unique( $bbcodes );

    foreach ( $bbcodes as $bbcode ) {
        $pattern = str_replace( '[' . $bbcode . '<', "<span class='cm-tag'>[" . $bbcode . "</span><", $pattern );
        $pattern = str_replace( '[' . $bbcode . ']', "<span class='cm-tag'>[" . $bbcode . "]</span>", $pattern );
        $pattern = str_replace( '[' . $bbcode . ' ', "<span class='cm-tag'>[" . $bbcode . "</span> ", $pattern );
        $pattern = str_replace( '[' . $bbcode . '=', "<span class='cm-tag'>[" . $bbcode . "</span>=", $pattern );
        $pattern = str_replace( '[/' . $bbcode . ']', "<span class='cm-tag'>[/" . $bbcode . "]</span>", $pattern );
    }

    foreach ( $attributes as $attr ) {
        $pattern = str_replace( ' ' . $attr . '=<', " <span class='cm-attribute'>" . $attr . "</span><span class='cm-operator'>=</span><", $pattern );
        $pattern = str_replace( $attr . '=<', "<span class='cm-attribute'>" . $attr . "</span><span class='cm-operator'>=</span><", $pattern );
    }

    $pattern = preg_replace('/\n/', '<br>', $pattern, ( substr_count($pattern, '\n') - 1 ) );

    // Close an unclosed string
    if( substr( $pattern, -1 ) === '"' ) {
        $pattern .= '</span>';
    }

    return "<span class='bbforms-code'>" . $pattern . "</span>";
}

/**
 * Generates an attributes table from the given array of attributes
 *
 * @since 1.0.0
 *
 * @param array    $attrs
 *
 * @return string
 */
function bbforms_attrs_table( $attrs = array(), $toggle = true ) {

    $output = '';

    if( $toggle ) {
        $output .= '<a href="#" class="bbforms-attrs-table-toggle button" data-active="false">' . esc_html__( 'Show Attributes', 'bbforms' ) . '</a>';
    }

    $output .= '<table class="bbforms-attrs-table" ' . ( $toggle ? 'style="display: none;"' : '' ) . '>';

    // Column headers
    $output .= '<tr>'
        . '<td><b>' . esc_html__( 'Attribute', 'bbforms' ) . '</b></td>'
        . '<td><b>' . esc_html__( 'Examples', 'bbforms' ) . '</b></td>'
        . '<td><b>' . esc_html__( 'Description', 'bbforms' ) . '</b></td>'
     . '</tr>';

    foreach( $attrs as $attr => $a ) {

        $codes = ( isset( $a['codes'] ) ? $a['codes'] : '' );
        $codes = ( is_array( $codes ) ? implode( '<br>', array_map( 'bbforms_parse_pattern', $codes ) ) : bbforms_parse_pattern( $codes ) );

        $classes = ( isset( $a['classes'] ) ? $a['classes'] : 'cm-attribute' );
        $attr = '<span class="' . $classes . '">' . $attr . '</span>';
        $desc = ( isset( $a['desc'] ) ? $a['desc'] : '' );

        if( strpos( $desc, __( 'Required.', 'bbforms' ) ) !== false ) {
            $attr .= '<span class="cm-required">*</span>';
        }

        $output .= '<tr>'
            . '<td>' . $attr . '</td>'
            . '<td>' . $codes . '</td>'
            . '<td>' . $desc . '</td>'
            . '</tr>';

    }


    $output .= '</table>';

    return $output;

}

/**
 * Generates a tags table from the given array of tags
 *
 * @since 1.0.0
 *
 * @param array    $tags
 *
 * @return string
 */
function bbforms_tags_table( $tags = array() ) {

    $output = '<b>' . esc_html__( 'Tags in this group:', 'bbforms' ) . '</b>' . '<br><br>';

    $output .= '<table class="bbforms-tags-table">';

    // Column headers
    $output .= '<tr>'
        . '<td><b>' . esc_html__( 'Tag', 'bbforms' ) . '</b></td>'
        . '<td><b>' . esc_html__( 'Preview', 'bbforms' ) . '</b></td>'
        . '<td><b>' . esc_html__( 'Description', 'bbforms' ) . '</b></td>'
        . '</tr>';

    foreach( $tags as $tag => $t ) {

        $output .= '<tr>'
            . '<td>' . '<span class="cm-def">{' . esc_html( $tag ) . '}</span>' . '</td>'
            . '<td>' . '<span class="cm-comment">' . esc_html( $t['preview'] ) . '</span>' . '</td>'
            . '<td>' . esc_html( $t['label'] ) . '</td>'
            . '</tr>';

    }


    $output .= '</table>';

    return $output;

}