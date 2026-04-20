<?php
/**
 * Settings
 *
 * @package     BBForms\Settings
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get registered options
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_options() {

    return apply_filters( 'bbforms_options', array(
        'theme' => array(
            'type' => 'select',
            'desc' => __( 'The form theme.', 'bbforms' ),
            'options' => array(
                'default' => __( 'Default', 'bbforms' ),
                'dark' => __( 'Dark', 'bbforms' ),
                'none' => __( 'None', 'bbforms' ),
            ),
            'default' => 'default',
        ),
        'show_form_title' => array(
            'type' => 'checkbox',
            'desc' => __( 'Show the form title when the form gets displayed.', 'bbforms' ),
            'default' => '',
        ),
        'form_title_tag' => array(
            'type' => 'select',
            'desc' => __( 'The form title HTML tag.', 'bbforms' ),
            'options' => array(
                'h1' => 'h1',
                'h2' => 'h2',
                'h3' => 'h3',
                'h4' => 'h4',
                'h5' => 'h5',
                'h6' => 'h6',
                'div' => 'div',
                'span' => 'span',
                'p' => 'p',
            ),
            'default' => 'h3',
        ),
        'show_required_fields_notice' => array(
            'type' => 'checkbox',
            // translators: %s: Text displayed
            'desc' => sprintf( __( 'Show the text "%s" at top of the form.', 'bbforms' ),
                // translators: %s: * (asterisk)
                sprintf( __( 'Fields marked with an %s are required', 'bbforms' ), '<span class="bbforms-required">*</span>' )
            ),
            'default' => '',
        ),
        'field_desc_after_label' => array(
            'type' => 'checkbox',
            // translators: %s: Option value
            'desc' => sprintf( __( 'Show field descriptions after their label, if is set to %s, field description will appear after the field.', 'bbforms' ), '<code>no</code>' ),
            'default' => '',
        ),
        'hide_form_on_success' => array(
            'type' => 'checkbox',
            'desc' => __( 'Hide the form when it gets submitted is successfully.', 'bbforms' ),
            'default' => 'yes',
        ),
        'clear_form_on_success' => array(
            'type' => 'checkbox',
            'desc' => __( 'Clear all fields when it gets submitted is successfully.', 'bbforms' ),
            'default' => 'yes',
        ),
        'disable_honeypot' => array(
            'type' => 'checkbox',
            'desc' => __( 'Add a honeypot field to prevent bots submissions.', 'bbforms' )
            // translators: %1$s: Option value %2$s: Field
            . '<br>' . sprintf( __( 'Set this option to %1$s to disable it. You can place custom honeypots using the field %2$s in the "Form" tab.', 'bbforms' ), '<code>no</code>', '<code>[honeypot]</code>' ),
            'default' => '',
        ),
        'require_login' => array(
            'type' => 'checkbox',
            'desc' => __( 'Make the form only accessible to logged in users.', 'bbforms' ),
            'default' => '',
        ),
        'require_login_message' => array(
            'type' => 'textarea',
            'desc' => __( 'Message displayed to not logged in users.', 'bbforms' )
                // translators: %1$s: Option %2$s: Option value
            . '<br>' . sprintf( __( 'BBForms only displays this message if %1$s is set to %2$s and the user is not logged in.', 'bbforms' ), '<code>require_login</code>', '<code>yes</code>' ),
            'default' => '{settings.require_login_message}',
        ),
        'unique_field' => array(
            'type' => 'text',
            'desc' => __( 'Limit form submission with a unique field value. With this option you can force a field to be unique, so no new submissions will be accepted for duplicated values of this field (ej: making an email field unique).', 'bbforms' )
            . '<br>' . __( 'Leave it empty to disable this option.', 'bbforms' )
            . '<br>'
            // translators: %s: Option value
            . '<br>' . sprintf( __( 'IMPORTANT: This option requires the action %s in order to work.', 'bbforms' ), '<code>[record]</code>' ),
            'default' => '',
        ),
        'unique_field_message' => array(
            'type' => 'textarea',
            'desc' => __( 'Message displayed when a duplicated value is found for a unique field.', 'bbforms' )
                // translators: %s: Option value
                . '<br>' . sprintf( __( 'BBForms only displays this message if %s is configured and there is a form submission with the same value for that field.', 'bbforms' ), '<code>unique_field</code>' ),
            'default' => '{settings.unique_field_message}',
        ),
        'submissions_limit' => array(
            'type' => 'integer',
            // translators: %s: Option value
            'desc' => sprintf( __( 'Add a limit of submissions to the form. Set it to %s to disable this option.', 'bbforms' ), '<code>0</code>' )
                . '<br>'
                // translators: %s: Option value
                . '<br>' . sprintf( __( 'IMPORTANT: This option requires the action %s in order to work.', 'bbforms' ), '<code>[record]</code>' ),
            'default' => '0',
        ),
        'submissions_limit_message' => array(
            'type' => 'textarea',
            'desc' => __( 'Message displayed when the form has reached the submissions limit.', 'bbforms' )
            // translators: %s: Option value
            . '<br>' . sprintf( __( 'BBForms only displays this message if %s is configured and the form has reached the submissions limit.', 'bbforms' ), '<code>submissions_limit</code>' ),
            'default' => '{settings.submissions_limit_message}',
        ),
    ) );

}

/**
 * Parse options
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_do_options( $form, $user_id, $content ) {

    $options = array();

    $registered_options = bbforms_get_options();

    $array = explode( "\n", $content );
    $in_option = false;

    // Extract the options
    foreach( $array as $line ) {

        // Restore break lines
        $line .= "\n";

        if( strpos( $line, '=' ) !== false ) {
            $parts = explode(  "=", $line );

            if( isset( $parts[0] ) && isset( $registered_options[$parts[0]] ) ) {
                $in_option = $parts[0];
                $options[$parts[0]] = $parts[1];
            }
        } else if( $in_option !== false && isset( $options[$in_option] ) ) {
            $options[$in_option] .= $line;
        }
    }

    // Ensure that all options are defined
    foreach( $registered_options as $key => $args ) {
        // Bail if option defined
        if( isset( $options[$key] ) ) continue;

        switch( $args['type'] ) {
            case 'checkbox':
            case 'boolean':
                // Not define a boolean means that is disables
                $options[$key] = false;
                break;
            case 'text':
            case 'textarea':
            case 'select':
                // To prevent do tags, leave not defined text and textarea empty
                $options[$key] = '';
                break;
            default:
                $options[$key] = ( isset($args['default']) ) ? $args['default'] : '';
                break;
        }
    }

    // Sanitize options
    foreach( $registered_options as $key => $args ) {
        $value = $options[$key];

        // Sanitize
        switch( $args['type'] ) {
            case 'checkbox':
            case 'boolean':
                $value = trim( $value );
                // Accept 1, yes, on & true as values
                if( $value === '1' || $value === 'yes' || $value === 'on' || $value === 'true' ) {
                    $value = true;
                } else {
                    $value = false;
                }
                break;
            case 'integer':
                $value = trim( $value );
                $value = intval( $value );
                break;
            case 'float':
                $value = trim( $value );
                $value = floatval( $value );
                break;
            case 'textarea':
                $value = wp_kses_post( $value );
                break;
            case 'text':
            case 'select':
                $value = sanitize_text_field( $value );
                break;
        }

        // Update value sanitized
        $options[$key] = $value;
    }

    // Parse tags
    foreach( $options as $key => $value ) {
        bbforms_do_tags( $form, $user_id, $content );
    }

    return apply_filters( 'bbforms_do_options', $options );

}

/**
 * Helper to get registered options default values
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_options_defaults() {

    $defauls = array();
    $options = bbforms_get_options();

    foreach( $options as $key => $args ) {

        if( isset( $args['default'] ) ) {
            $defauls[$key] = $args['default'];
        } else {
            $defauls[$key] = '';
        }

        if( $args['type'] == 'checkbox' ||  $args['type'] == 'boolean' ) {
            if( $args['default'] === '' || $args['default'] === 'no' || $args['default'] === '0' ) {
                $defauls[$key] = 'no';
            } else {
                $defauls[$key] = 'yes';
            }
        }


    }

    return apply_filters( 'bbforms_options_defaults', $defauls );

}

/**
 * Helper to get options in a string format for the code editor
 *
 * @since 1.0.0
 *
 * @param array $overrides Array of options to override the default values
 *
 * @return string
 */
function bbforms_get_options_code( $overrides = array() ) {

    $output = '';
    $options_defaults = bbforms_get_options_defaults();

    foreach( $options_defaults as $key => $value ) {

        if( isset( $overrides[$key] ) ) {
            $value = $overrides[$key];
        }

        $output .= $key . '=' . $value . "\n";
    }

    return $output;

}