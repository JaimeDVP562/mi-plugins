<?php
/**
 * Youtube
 *
 * @package     BBForms\BBCode\Youtube
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// [youtube]CONTENT[/youtube]
// [youtube width="" height="" start="" autoplay="" muted="" loop="" controls=""]CONTENT[/youtube]
class BBForms_BBCode_Youtube extends BBForms_BBCode {

    public $bbcode = 'youtube';
    public $default_attrs = array(
        'width' => '560',
        'height' => '315',
        'start' => '0',
        'autoplay' => 'no',
        'muted' => 'no',
        'loop' => 'no',
        'controls' => 'yes',
    );

    public function init() {
        $this->name = __( 'Youtube Video', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['value'] ) ) $attrs['value'] = '';

        if( $attrs['value'] === '' && $content !== null ) $attrs['value'] = $content;

        $video_id = $this->get_youtube_id( $attrs['value'] );

        if ( ! $video_id ) {
            return '';
        }

        $attrs['frameborder'] = '0';

        $video_id .= '?origin=' . site_url('/');

        $attrs['allow'] = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        $attrs['referrerpolicy'] = 'strict-origin-when-cross-origin';

        if( ! bbforms_is_option_enabled( $attrs['controls'] ) ) {
            $video_id .= '&controls=0';
        }

        if( bbforms_is_option_enabled( $attrs['autoplay'] ) ) {
            $video_id .= '&autoplay=1';
        }

        if( bbforms_is_option_enabled( $attrs['muted'] ) ) {
            $video_id .= '&muted=1';
        }

        if( bbforms_is_option_enabled( $attrs['loop'] ) ) {
            $video_id .= '&loop=1';
        }

        if( absint( $attrs['start'] ) > 0 ) {
            $video_id .= '&start=' . absint( $attrs['start'] );
        }

        return sprintf(
            '<iframe src=\'https://www.youtube.com/embed/%1$s\' %2$s allowfullscreen></iframe>',
            esc_attr( $video_id ),
            bbforms_concat_attrs( $attrs, array( 'value', 'controls', 'autoplay', 'start', 'loop', 'muted' ), "'" )
        );
    }

    public function get_youtube_id( $url ) {
        if ( empty($url) || !is_string($url) ) {
            return false;
        }

        if ( preg_match( '#youtu\.be/([\w-]+)#', $url, $matches ) ) {
            return $matches[1];
        }

        if ( preg_match( '#youtube\.com.*[?&]v=([\w-]+)#', $url, $matches ) ) {
            return $matches[1];
        }

        if ( preg_match( '#youtube\.com/embed/([\w-]+)#', $url, $matches ) ) {
            return $matches[1];
        }

        return false;
    }

}
new BBForms_BBCode_Youtube();