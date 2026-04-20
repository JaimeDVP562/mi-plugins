<?php
/**
 * Form Preview
 *
 * @package     BBForms\Form_Preview
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Form preview
 *
 * @since 1.0.0
 */
function bbforms_form_preview_content() {

    if( ! current_user_can( bbforms_get_manager_capability() ) ) {
        return;
    }

    if( ! isset( $_REQUEST['bbforms_preview'] ) ) {
        return;
    }

    if( $_REQUEST['bbforms_preview'] !== 'yes' ) {
        return;
    }

    if( ! isset( $_REQUEST['id'] ) ) {
        return;
    }

    $id = absint( $_REQUEST['id'] );

    if( $id <= 0 ) {
        return;
    }

    $form = new BBForms_Form( $id );

    if( ! $form->exists() ) {
        return;
    }

    // Override form settings for the preview
    if( isset( $_REQUEST['form'] ) ) {
        $form_content = ( isset( $_REQUEST['form'] ) ? wp_kses_post( wp_unslash( $_REQUEST['form'] ) ) : '' );
        $actions_content = ( isset( $_REQUEST['actions'] ) ? wp_kses_post( wp_unslash( $_REQUEST['actions'] ) ) : '' );
        $options_content = ( isset( $_REQUEST['options'] ) ? wp_kses_post( wp_unslash( $_REQUEST['options'] ) ) : '' );

        $form->form = $form_content;
        $form->actions = $actions_content;
        $form->options_raw = $options_content;
        $form->options = bbforms_do_options( $form, $form->user_id, $options_content );
    }

    define( 'BBFORMS_DOING_PREVIEW', true );
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <link type="text/css" rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
        <?php wp_head();?>
        <style>
            #bbforms-form-preview {
                width: 100%;
                margin: 0 auto;
            }
        </style>
    </head>
    <body <?php body_class( 'wp-singular page-template-default page' ); ?>>
    <div id="page" class="site">
        <div id="content" class="site-content">
            <div id="bbforms-form-preview" class="page type-page status-publish hentry post post-content">
                <div class="inside-article">
                    <?php echo bbforms_do_form( $form ); ?>
                </div>
            </div>
        </div>
    </div>
    </body>
    <?php wp_footer();?>
    </html>
    <?php

    exit;

}
add_action( 'wp_loaded', 'bbforms_form_preview_content', 99999 );

/**
 * Register form preview scripts
 *
 * @since 1.0.0
 */
function bbforms_register_preview_scripts() {

    if( ! isset( $_REQUEST['bbforms_preview'] ) ) {
        return;
    }

    if( $_REQUEST['bbforms_preview'] !== 'yes' ) {
        return;
    }

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_register_style( 'bbforms-preview-css', BBFORMS_URL . 'assets/css/bbforms-preview' . $suffix . '.css', array( ), BBFORMS_VER, 'all' );


}
add_action( 'init', 'bbforms_register_preview_scripts' );

/**
 * Enqueue form preview scripts
 *
 * @since 1.0.0
 */
function bbforms_enqueue_preview_scripts() {

    if( ! isset( $_REQUEST['bbforms_preview'] ) ) {
        return;
    }

    if( $_REQUEST['bbforms_preview'] !== 'yes' ) {
        return;
    }

    bbforms_enqueue_scripts();

    // Stylesheets
    wp_enqueue_style( 'bbforms-preview-css' );

}
add_action( 'wp_enqueue_scripts', 'bbforms_enqueue_preview_scripts' );