<?php
/**
 * Text
 *
 * @package     BBForms\Field_Field\Text
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Text extends BBForms_Field {

    public $bbcode = 'text';
    public $default_attrs = array(
        'min' => '',
        'max' => '',
        'minlength' => '',
        'maxlength' => '',
        'pattern' => '',
    );
    public $pattern = '[text* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Text Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[text name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic text field', 'bbforms' ),
            ),
            array(
                'pattern' => '[text* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required text field', 'bbforms' ),
            ),
            array(
                'pattern' => '[text name="" value="CONTENT" min="0" max="100"]' . "\n",
                'label' => __( 'Limited length text field', 'bbforms' ),
            ),
            array(
                'pattern' => '[text name="" value="{user.display_name}" label="' . __( 'Name', 'bbforms' ) . '" desc="' . __( 'Your name', 'bbforms' ) . '" placeholder="' . __( 'Enter your name here', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'User name field (autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[text name="" value="{user.first_name}" label="' . __( 'First Name', 'bbforms' ) . '" desc="' . __( 'Your first name', 'bbforms' ) . '" placeholder="' . __( 'Enter your first name here', 'bbforms' ) . '"]' . "\n\n"
                . '[text name="" value="{user.last_name}" label="' . __( 'Last Name', 'bbforms' ) . '" desc="' . __( 'Your last name', 'bbforms' ) . '" placeholder="' . __( 'Enter your last name here', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'First and last name fields (both autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[text* name="" value="CONTENT" label="" desc="" placeholder="" min="" max="" pattern="" id="" class=""]' . "\n",
                'label' => __( 'Text field with several attributes', 'bbforms' ),
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
            'text',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

}
new BBForms_Field_Text();