<?php
/**
 * Email
 *
 * @package     BBForms\Field_Field\Email
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Email extends BBForms_Field {

    public $bbcode = 'email';
    public $default_attrs = array(
        'pattern' => '',
    );
    public $pattern = '[email* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Email Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[email name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic email field', 'bbforms' ),
            ),
            array(
                'pattern' => '[email* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required email field', 'bbforms' ),
            ),
            array(
                'pattern' => '[email name="" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '" desc="' . __( 'Your email', 'bbforms' ) . '" placeholder="' . __( 'Enter your email here', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'Email field (autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[email* name="" value="CONTENT" label="" desc="" placeholder="" min="" max="" pattern="" id="" class=""]' . "\n",
                'label' => __( 'Email field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'email',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

    public function sanitize() {
        $this->sanitized_value = ( ! is_array( $this->value ) ? sanitize_email( $this->value ) : array_map( 'sanitize_email', $this->value ) );
    }

    public function validate() {
        // Do not validate if field is optional and value is empty
        if( ! $this->is_required() && $this->value === '' ) {
            return true;
        }

        return ( filter_var( $this->value, FILTER_VALIDATE_EMAIL ) );
    }

    public function get_error_message() {
        return bbforms_get_error_message( 'email_error' );
    }

}
new BBForms_Field_Email();