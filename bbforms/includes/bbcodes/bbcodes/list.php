<?php
/**
 * List
 *
 * @package     BBForms\BBCode\List
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_List extends BBForms_BBCode {

    public $bbcode = 'list';
    public $pattern = "[list]\n[*] CONTENT\n[/list]\n";

    public function init() {
        $this->name = __( 'List', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        $attrs['value'] = strtolower( trim( $attrs['value'] ) );

        if ( in_array( $attrs['value'], array( '0', '1', 'a', 'A', 'i', 'I' ) ) ) {
            $list_tag = 'ol';

            switch ( $attrs['value'] ) {
                case '0':
                    $attrs['style'] .= 'list-style-type: decimal-leading-zero;';
                    break;
                case '1':
                    $attrs['style'] .= 'list-style-type: decimal;';
                    break;
                case 'a':
                    $attrs['style'] .= 'list-style-type: lower-alpha;';
                    break;
                case 'A':
                    $attrs['style'] .= 'list-style-type: upper-alpha;';
                    break;
                case 'i':
                    $attrs['style'] .= 'list-style-type: lower-roman;';
                    break;
                case 'I':
                    $attrs['style'] .= 'list-style-type: upper-roman;';
                    break;
            }
        } else {
            $list_tag = 'ul';
        }

        // Turn [*] into <li>
        $items_html = preg_replace_callback('/\[\*\](.*?)((?=\[\*\])|$)/is', function ($matches) {
            $content = trim( $matches[1] );
            return sprintf( '<li>%s</li>', $content );
        }, $content);

        // Return list
        return sprintf(
            '<%1$s %3$s>%2$s</%1$s>',
            $list_tag,
            $items_html,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_List();