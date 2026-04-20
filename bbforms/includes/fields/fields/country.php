<?php
/**
 * Country
 *
 * @package     BBForms\Field_Field\Country
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// [country* name="NAME" options="Option 1|Option 2|Option 3" options_values="1|2|3" multiple="yes"]
class BBForms_Field_Country extends BBForms_Field {

    public $bbcode = 'country';
    public $default_attrs = array(
        'options'           => '',      // The values to display
        'options_values'    => '',      // The values to store, if not defined, will use a sanitize_key() on options
        'multiple'          => '',      // Select multiple
        'save_as'           => 'code',  // Accepts code, code_lower, name, name_lower
    );
    public $pattern = '[country* name="" save_as="code" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Country Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[country name="" save_as="code" value="CONTENT"]' . "\n",
                'label' => __( 'Basic country field', 'bbforms' ),
            ),
            array(
                'pattern' => '[country* name="" save_as="code" value="CONTENT"]' . "\n",
                'label' => __( 'Required country field', 'bbforms' ),
            ),
            array(
                'pattern' => '[country name="" save_as="code" value="CONTENT" multiple="yes"]' . "\n",
                'label' => __( 'Country field that allows select multiple options', 'bbforms' ),
            ),
            array(
                'pattern' => '[country name="" save_as="code_lower" value="CONTENT"]' . "\n",
                'label' => __( 'Country field that stores the country code in lower case', 'bbforms' ),
            ),
            array(
                'pattern' => '[country name="" save_as="name" value="CONTENT"]' . "\n",
                'label' => __( 'Country field that stores the country name', 'bbforms' ),
            ),
            array(
                'pattern' => '[country name="" save_as="name_lower" value="CONTENT"]' . "\n",
                'label' => __( 'Country field that stores the country name in lower case', 'bbforms' ),
            ),
            array(
                'pattern' => '[country* name="" save_as="code" value="CONTENT" multiple="" placeholder="" label="" desc="" id="" class=""]' . "\n",
                'label' => __( 'Country field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( isset( $attrs['multiple'] ) ) {

            if( bbforms_is_option_enabled( $attrs['multiple'] ) ) {
                $attrs['multiple'] = 'multiple';
                $attrs['name'] = $attrs['name'] . '[]';
            } else {
                unset( $attrs['multiple'] );
            }

        }

        // Options
        $countries = bbforms_get_countries();
        $options = array();

        if( ! isset( $attrs['save_as'] ) ) {
            $attrs['save_as'] = 'code';
        }

        switch( $attrs['save_as'] ) {
            case 'code_lower':
                // es -> Spain
                foreach( $countries as $code => $name ) {
                    $options[strtolower( $code )] = $name;
                }
                break;
            case 'name':
                // Spain -> Spain
                foreach( $countries as $code => $name ) {
                    $options[$name] = $name;
                }
                break;
            case 'name_lower':
                // spain -> Spain
                foreach( $countries as $code => $name ) {
                    $options[strtolower( $name )] = $name;
                }
                break;
            case 'code':
            default:
                // ES => Spain
                $options = $countries;
                break;
        }

        $options_html = '';

        if( ! empty( $attrs['placeholder'] ) ) {
            $options_html .= '<option value="" disabled="disabled" ' . ( empty( $attrs['value'] ) ? 'selected="selected"' : '' ) . '>' . $attrs['placeholder'] . '</option>';
        }

        foreach( $options as $value => $option ) {
            $selected = $attrs['value'];

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

        if( isset( $attrs['multiple'] ) ) {
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
new BBForms_Field_Country();