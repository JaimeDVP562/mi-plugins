<?php
/**
 * Size
 *
 * @package     BBForms\BBCode\Size
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Size extends BBForms_BBCode {

    public $bbcode = 'size';
    public $pattern = '[size="14"]CONTENT[/size]';

    public function init() {
        $this->name = __( 'Size', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        // Turn 13 into 13px
        if( is_numeric( $attrs['value'] ) ) {
            $attrs['value'] .= 'px';
        }

        if( ! isset( $attrs['style'] ) ) $attrs['style'] = '';

        $attrs['style'] .= 'font-size:' . esc_attr( $attrs['value'] ) . ';';

        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'span',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Size();