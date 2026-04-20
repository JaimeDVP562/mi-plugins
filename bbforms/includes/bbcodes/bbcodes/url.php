<?php
/**
 * URL
 *
 * @package     BBForms\BBCode\URL
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_URL extends BBForms_BBCode {

    public $bbcode = 'url';
    public $pattern = '[url="VALUE"]CONTENT[/url]';
    public $default_attrs = array(
        'target' => '',
    );

    public function init() {
        $this->name = __( 'URL Link', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['value'] ) ) $attrs['value'] = '';

        if( $attrs['value'] === '' && $content !== null ) $attrs['value'] = $content;

        return sprintf(
            '<%1$s href=\'%3$s\' %4$s/>%2$s</%1$s>',
            'a',
            $content,
            esc_attr( $attrs['value'] ),
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_URL();