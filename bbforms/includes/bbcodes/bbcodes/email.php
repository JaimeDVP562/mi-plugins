<?php
/**
 * Email
 *
 * @package     BBForms\BBCode\Email
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Email extends BBForms_BBCode {

    public $bbcode = 'email';
    public $pattern = '[email="VALUE"]CONTENT[/email]';
    public $default_attrs = array(
        'target' => '',
    );

    public function init() {
        $this->name = __( 'Email Link', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['value'] ) ) $attrs['value'] = '';

        if( $attrs['value'] === '' && $content !== null ) $attrs['value'] = $content;

        return sprintf(
            '<%1$s href=\'mailto:%3$s\' %4$s/>%2$s</%1$s>',
            'a',
            $content,
            esc_attr( $attrs['value'] ),
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Email();