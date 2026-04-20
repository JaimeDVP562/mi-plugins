<?php
/**
 * Row
 *
 * @package     BBForms\BBCode\Row
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Row extends BBForms_BBCode {

    public $bbcode = 'row';
    public $pattern = "[row]\n\t[column]\n\t\tCONTENT\n\t[/column]\n[/row]\n";

    public function init() {
        $this->name = __( 'Columns', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        // Process columns
        $html_content = preg_replace_callback('/\[column(.*?)\](.*?)\[\/column\]/is', function($row_match) {
            $width = str_replace( array( '=', '%', '"', "'" ), '', $row_match[1] );
            $width = ( empty( $width ) ? '' : 'max-width:' . absint( $width ) . '%' );
            $content = $row_match[2];
            $content = trim( $content );
            //$content = wpautop( $content );
            return sprintf('<div class="bbforms-column" style="flex: 1;%s">%s</div>', $width, $content);
        }, $content);

        if( ! isset( $attrs['class'] ) ) {
            $attrs['class'] = '';
        }

        $attrs['class'] = 'bbforms-row' . ( empty( $attrs['class'] ) ? '' : ' ' . $attrs['class'] );

        // Returns table in html
        return sprintf(
            '<div %2$s>%1$s</div>',
            $html_content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Row();