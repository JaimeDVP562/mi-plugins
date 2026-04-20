<?php
/**
 * Check
 *
 * @package     BBForms\Field_Field\Check
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// [check* name="NAME" options="Option 1|Option 2|Option 3" options_values="1|2|3" inline="yes"]
class BBForms_Field_Check extends BBForms_Field {

    public $bbcode = 'check';
    public $default_attrs = array(
        'options'           => '',      // The values to display
        'options_values'    => '',      // The values to store, if not defined, will use a sanitize_key() on options
        'inline'            => 'yes',   // To show options inline like [ ] Option 1 [ ] Option 2
    );
    public $pattern = '[check* name="" inline="no"]' . "\n"
    . 'CONTENT' . "\n"
    . '[/check]' . "\n";

    public function init() {
        $this->name = __( 'Checkbox Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[check name="" value=""]CONTENT[/check]' . "\n",
                'label' => __( 'Basic single checkbox field', 'bbforms' ),
            ),
            array(
                'pattern' => '[check name="" value=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/check]' . "\n",
                'label' => __( 'Basic multiple checkbox field', 'bbforms' ),
            ),
            array(
                'pattern' => '[check* name="" value=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/check]' . "\n",
                'label' => __( 'Required checkbox field', 'bbforms' ),
            ),
            array(
                // translators: %d: Number
                'pattern' => '[check name="" value="' . sprintf( __( 'Option %d', 'bbforms' ), 2 ) . '" inline="yes"]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/check]' . "\n",
                'label' => __( 'Checkbox field with options displayed inline + Option 2 checked', 'bbforms' ),
            ),
            array(
                'pattern' => '[check name="" value="2"]' . "\n"
                    . 'CONTENT_VALUE' . "\n"
                    . '[/check]' . "\n",
                'label' => __( 'Checkbox field where you define the internal value of each option + Options 2 checked', 'bbforms' ),
            ),
            array(
                'pattern' => '[check* name="" value="" inline="" label="" desc="" id="" class=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/check]' . "\n",
                'label' => __( 'Checkbox field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {

        $options = $this->parse_options( $attrs, $content );

        $output = '';

        foreach( $options as $value => $option ) {
            $selected = $attrs['value'];

            $output .= $this->render_option( $option, $value, $selected, $attrs );
        }

        return $output;
    }

    public function render_option( $option, $value, $selected, $attrs ) {

        if( $attrs['id'] === '' ) {
            $attrs['id'] = $attrs['name'];
        }

        $output = '';
        $inline = ( $attrs['inline'] == 'yes' || $attrs['inline'] == '1' || $attrs['inline'] == 'true' || $attrs['inline'] == 'on' );
        $id = str_replace( '_', '-', $attrs['id'] . '-' . $value );
        $id = str_replace( ' ', '-', $id );
        $id = sanitize_key( $id );

        // Setup option attributes
        $attrs['id'] = $id;
        $attrs['aria-labelledby'] = $id . '-label';
        $attrs['name'] = $attrs['name'] . '[]'; // Checkboxes accept multiple values

        // Checked attribute
        if( $value == $selected || ( is_array( $selected ) && in_array( $value, $selected ) ) ) {
            $attrs['checked'] = 'checked';
        } else if( isset( $attrs['checked'] ) ) {
            unset( $attrs['checked'] );
        }

        // Checkboxes can not have the required attribute
        if( isset( $attrs['required'] ) ) {
            unset( $attrs['required'] );
        }

        $option_wrap_class = 'bbforms-option bbforms-' . esc_attr( $this->bbcode ) . '-option bbforms-' . esc_attr( $id ) . '-option';

        $output .= ( $inline
            ? '<span class="' . $option_wrap_class . ' bbforms-' . esc_attr( $this->bbcode ) . '-option-inline">'
            : '<div class="' . $option_wrap_class . '">'
        );

        $output .= sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/> <label for="%5$s" id="%5$s-label" class="bbforms-%6$s-label bbforms-%5$s-label">%7$s</label> ',
            'input',
            'checkbox',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc', 'options', 'options_values', 'inline' ) ),
            esc_attr( $value ),
            esc_attr( $id ),
            esc_attr( $this->bbcode ),
            $option,
        );

        $output .= ( $inline ? '</span>' : '</div>' );

        return $output;

    }

}
new BBForms_Field_Check();