<?php
/**
 * URL
 *
 * @package     BBForms\Field_Field\URL
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_URL extends BBForms_Field {

    public $bbcode = 'url';
    public $default_attrs = array(
        'pattern' => '',
    );
    public $pattern = '[url* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'URL Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[url name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic URL field', 'bbforms' ),
            ),
            array(
                'pattern' => '[url* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required URL field', 'bbforms' ),
            ),
            array(
                'pattern' => '[url name="" value="CONTENT" pattern="https://.*"]' . "\n",
                'label' => __( 'URL field allowing only secure URLs (https)', 'bbforms' ),
            ),
            array(
                'pattern' => '[url name="" value="CONTENT" min="0" max="100"]' . "\n",
                'label' => __( 'Limited length URL field', 'bbforms' ),
            ),
            array(
                'pattern' => '[url* name="" value="CONTENT" label="" desc="" placeholder="" min="" max="" pattern="" id="" class=""]' . "\n",
                'label' => __( 'Text field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'url',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

    public function validate() {
        // Do not validate if field is optional and value is empty
        if( ! $this->is_required() && $this->value === '' ) {
            return true;
        }

        return ( filter_var( $this->value, FILTER_VALIDATE_URL ) );
    }

    public function get_error_message() {
        return bbforms_get_error_message( 'url_error' );
    }

}
new BBForms_Field_URL();