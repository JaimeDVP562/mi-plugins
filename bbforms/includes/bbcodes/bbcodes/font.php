<?php
/**
 * Font
 *
 * @package     BBForms\BBCode\Font
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Font extends BBForms_BBCode {

    public $bbcode = 'font';
    public $pattern = '[font="Arial"]CONTENT[/font]';

    public function init() {
        $this->name = __( 'Font', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['style'] ) ) $attrs['style'] = '';

        $attrs['style'] .= 'font-family:' . esc_attr( $attrs['value'] ) . ';';

        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'span',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Font();