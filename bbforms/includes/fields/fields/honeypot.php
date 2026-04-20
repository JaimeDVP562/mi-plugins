<?php
/**
 * HoneyPot
 *
 * @package     BBForms\Field_Field\HoneyPot
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_HoneyPot extends BBForms_Field {

    public $bbcode = 'honeypot';
    public $default_attrs = array();
    public $pattern = '[honeypot name=""]' . "\n";

    public function init() {
        $this->name = __( 'Honeypot Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[honeypot name=""]' . "\n",
                'label' => __( 'Basic honeypot field', 'bbforms' ),
            ),
            array(
                'pattern' => '[honeypot name="" id="" class=""]' . "\n",
                'label' => __( 'Honeypot field with custom id and class attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'text',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }


    public function validate() {
        // Honeypot can not have any value in any way
        return ( empty( $this->value ) );
    }

    public function is_required() {
        // Honeypot can not be required
        return false;
    }

    public function get_error_message() {
        global $bbforms_response;

        // Honeypot error goes to form error messages
        $bbforms_response['messages'][] = array(
            'text' => bbforms_get_error_message( 'honeypot_error' ),
            'type' => 'error',
        );

        return '';
    }

}
new BBForms_Field_HoneyPot();