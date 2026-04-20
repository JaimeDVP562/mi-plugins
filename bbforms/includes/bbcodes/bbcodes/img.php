<?php
/**
 * Img
 *
 * @package     BBForms\BBCode\Img
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Img extends BBForms_BBCode {

    public $bbcode = 'img';
    public $pattern = '[img="VALUE"]CONTENT[/img]';
    public $default_attrs = array(
        'width' => '',
        'height' => '',
    );

    public function init() {
        $this->name = __( 'Image', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['value'] ) ) $attrs['value'] = '';

        if( $attrs['value'] === '' && $content !== null ) {
            $attrs['value'] = $content;
            $content = null;
        }

        return sprintf(
            '<%1$s src=\'%3$s\' alt=\'%2$s\' %4$s/>',
            'img',
            ( $content !== null ? esc_attr( $content ) : '' ),
            esc_attr( $attrs['value'] ),
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Img();