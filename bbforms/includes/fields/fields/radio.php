<?php
/**
 * Radio
 *
 * @package     BBForms\Field_Field\Radio
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// [radio* name="NAME" options="Option 1|Option 2|Option 3" options_values="1|2|3" inline="yes"]
// IMPORTANT: In HTML, radios are ALWAYS required
class BBForms_Field_Radio extends BBForms_Field {

    public $bbcode = 'radio';
    public $default_attrs = array(
        'options'           => '',      // The values to display
        'options_values'    => '',      // The values to store, if not defined, will use a sanitize_key() on options
        'inline'            => 'yes',   // To show options inline like [ ] Option 1 [ ] Option 2
    );
    public $pattern = '[radio* name="" inline="yes"]' . "\n"
    . 'CONTENT' . "\n"
    . '[/radio]' . "\n";

    public function init() {
        $this->name = __( 'Radio Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[radio name="" value=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/radio]' . "\n",
                'label' => __( 'Basic radio field', 'bbforms' ),
            ),
            array(
                'pattern' => '[radio* name="" value=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/radio]' . "\n",
                'label' => __( 'Required radio field', 'bbforms' ),
            ),
            array(
                // translators: %d: Number
                'pattern' => '[radio name="" value="' . sprintf( __( 'Option %d', 'bbforms' ), 2 ) . '" inline="yes"]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/radio]' . "\n",
                'label' => __( 'Radio field with options displayed inline + Option 2 checked', 'bbforms' ),
            ),
            array(
                'pattern' => '[radio name="" value="2"]' . "\n"
                    . 'CONTENT_VALUE' . "\n"
                    . '[/radio]' . "\n",
                'label' => __( 'Radio field where you define the internal value of each option + Option 2 checked', 'bbforms' ),
            ),
            array(
                'pattern' => '[radio* name="" value="" inline="" label="" desc="" id="" class=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/radio]' . "\n",
                'label' => __( 'Radio field with several attributes', 'bbforms' ),
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

        // Checked attribute
        if( $value == $selected || ( is_array( $selected ) && in_array( $value, $selected ) ) ) {
            $attrs['checked'] = 'checked';
        } else if( isset( $attrs['checked'] ) ) {
            unset( $attrs['checked'] );
        }

        $option_wrap_class = 'bbforms-option bbforms-' . esc_attr( $this->bbcode ) . '-option bbforms-' . esc_attr( $id ) . '-option';

        $output .= ( $inline
            ? '<span class="' . $option_wrap_class . ' bbforms-' . esc_attr( $this->bbcode ) . '-option-inline">'
            : '<div class="' . $option_wrap_class . '">'
        );

        $output .= sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/> <label for="%5$s" id="%5$s-label" class="bbforms-%6$s-label bbforms-%5$s-label">%7$s</label> ',
            'input',
            'radio',
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
new BBForms_Field_Radio();