<?php
/**
 * Select
 *
 * @package     BBForms\Field_Field\Select
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// [select* name="NAME" options="Option 1|Option 2|Option 3" options_values="1|2|3" multiple="yes"]
class BBForms_Field_Select extends BBForms_Field {

    public $bbcode = 'select';
    public $default_attrs = array(
        'options'           => '',      // The values to display
        'options_values'    => '',      // The values to store, if not defined, will use a sanitize_key() on options
        'multiple'          => '',      // Select multiple
    );
    public $pattern = '[select* name=""]' . "\n"
    . 'CONTENT' . "\n"
    . '[/select]' . "\n";

    public function init() {
        $this->name = __( 'Select Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[select name="" value=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/select]' . "\n",
                'label' => __( 'Basic select field', 'bbforms' ),
            ),
            array(
                'pattern' => '[select* name="" value=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/select]' . "\n",
                'label' => __( 'Required select field', 'bbforms' ),
            ),
            array(
                'pattern' => '[select name="" value="" multiple="yes"]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/select]' . "\n",
                'label' => __( 'Select field that allows select multiple options', 'bbforms' ),
            ),
            array(
                'pattern' => '[select name="" value=""]' . "\n"
                    . 'CONTENT_VALUE' . "\n"
                    . '[/select]' . "\n",
                'label' => __( 'Select field where you define the internal value of each option', 'bbforms' ),
            ),
            array(
                'pattern' => '[select* name="" value="" multiple="" placeholder="" label="" desc="" id="" class=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/select]' . "\n",
                'label' => __( 'Select field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( isset( $attrs['multiple'] ) && bbforms_is_option_enabled( $attrs['multiple'] ) ) {
            $attrs['multiple'] = 'multiple';
            $attrs['name'] = $attrs['name'] . '[]';
        }

        $options = $this->parse_options( $attrs, $content );

        $options_html = '';

        if( ! empty( $attrs['placeholder'] ) ) {
            $options_html .= '<option value="" disabled="disabled" ' . ( empty( $attrs['value'] ) ? 'selected="selected"' : '' ) . '>' . $attrs['placeholder'] . '</option>';
        }

        $selected = $attrs['value'];

        foreach( $options as $value => $option ) {
            $options_html .= $this->render_option( $option, $value, $selected, $attrs );
        }

        return sprintf(
            '<%1$s %2$s>%3$s</%1$s>',
            'select',
            bbforms_concat_attrs( $attrs, array( 'value', 'label', 'desc', 'placeholder' ) ),
            $options_html
        );
    }

    public function render_option( $option, $value, $selected, $attrs ) {

        $is_selected = $value == $selected;

        if( isset( $attrs['multiple'] ) && bbforms_is_option_enabled( $attrs['multiple'] ) ) {
            if ( ! is_array( $selected ) && strpos( $selected, '|' ) !== false ) {
                $selected = explode( '|', $selected );
            }

            if( is_array( $selected ) ) {
                $is_selected = in_array( $value, $selected );
            }
        }

        return sprintf(
            '<%1$s value="%2$s" %3$s>%4$s</%1$s> ',
            'option',
            esc_attr( $value ),
            ( $is_selected ? 'selected="selected"' : '' ),
            $option,
        );

    }

}
new BBForms_Field_Select();