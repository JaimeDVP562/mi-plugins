<?php
/**
 * Password
 *
 * @package     BBForms\Field_Field\Password
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Password extends BBForms_Field {

    public $bbcode = 'password';
    public $default_attrs = array(
        'min' => '',
        'max' => '',
        'minlength' => '',
        'maxlength' => '',
        'pattern' => '',
    );
    public $pattern = '[password* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Password Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[password name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic password field', 'bbforms' ),
            ),
            array(
                'pattern' => '[password* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required password field', 'bbforms' ),
            ),
            array(
                'pattern' => '[password name="" value="CONTENT" min="0" max="100"]' . "\n",
                'label' => __( 'Limited length password field', 'bbforms' ),
            ),
            array(
                'pattern' => '[password* name="" value="CONTENT" label="" desc="" placeholder="" min="" max="" pattern="" id="" class=""]' . "\n",
                'label' => __( 'Password field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        // min to minlength
        if( ! empty( $attrs['min'] ) && empty( $attrs['minlength'] ) ) {
            $attrs['minlength'] = $attrs['min'];
            unset( $attrs['min'] );
        }

        // max to maxlength
        if( ! empty( $attrs['max'] ) && empty( $attrs['maxlength'] ) ) {
            $attrs['maxlength'] = $attrs['max'];
            unset( $attrs['max'] );
        }

        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'password',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

}
new BBForms_Field_Password();