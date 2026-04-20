<?php
/**
 * Hidden
 *
 * @package     BBForms\Field_Field\Hidden
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Hidden extends BBForms_Field {

    public $bbcode = 'hidden';
    public $default_attrs = array();
    public $pattern = '[hidden name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Hidden Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[hidden name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic hidden field', 'bbforms' ),
            ),
            array(
                'pattern' => '[hidden name="" value="{user.id}"]' . "\n",
                'label' => __( 'Hidden user ID field (autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[hidden name="" value="{user.login}"]' . "\n",
                'label' => __( 'Hidden user username field (autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[hidden name="" value="{user.email}"]' . "\n",
                'label' => __( 'Hidden user email field (autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[hidden* name="" value="CONTENT" id="" class=""]' . "\n",
                'label' => __( 'Hidden field with custom id and class attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'hidden',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

}
new BBForms_Field_Hidden();