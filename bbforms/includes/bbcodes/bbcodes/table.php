<?php
/**
 * Table
 *
 * @package     BBForms\BBCode\Table
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Table extends BBForms_BBCode {

    public $bbcode = 'table';
    public $pattern = "[table]\n\t[tr]\n\t\t[td]CONTENT[/td]\n\t[/tr]\n[/table]\n";
    public $default_attrs = array(
        'align' => '',
        'border' => '',
        'width' => '',
    );

    public function init() {
        $this->name = __( 'Table', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        // Process columns
        $html_content = preg_replace_callback('/\[tr\](.*?)\[\/tr\]/is', function($tr_match) {


            $td_content = preg_replace_callback('/\[td(.*?)\](.*?)\[\/td\]/is', function($td_match) {
                $width = str_replace( array( '=', '%' ), '', $td_match[1] );
                $width = ( empty( $width ) ? 100 : absint( $width ) );
                $content = $td_match[2];
                $content = trim( $content );
                //$content = wpautop( $content );
                return sprintf('<td style=\'width: %s;\'>%s</td>', $width, $content);

            }, $tr_match[1] );

            return sprintf('<tr>%s</tr>', $td_content);
        }, $content);

        if( ! empty( $attrs['align'] ) ) {
            $attrs['style'] .= 'text-align:' . $attrs['align'] . ';';
            unset( $attrs['align'] );
        }

        if( ! empty( $attrs['border'] ) ) {
            $attrs['style'] .= 'border:' . $attrs['border'] . ';';
            unset( $attrs['border'] );
        }

        if( ! empty( $attrs['width'] ) ) {
            $attrs['style'] .= 'width:' . $attrs['width'] . ';';
            unset( $attrs['width'] );
        }

        // Returns table in html
        return sprintf(
            '<table %2$s>%1$s</table>',
            $html_content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Table();