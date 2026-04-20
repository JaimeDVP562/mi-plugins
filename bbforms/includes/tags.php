<?php
/**
 * Tags
 *
 * @package     BBForms\Tags
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get registered tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_tags() {

    $tags = array();

    // ---------------------------------
    // Form tags
    // ---------------------------------
// TODO: For the future
//    $tags['form'] = array(
//        'label' => __( 'Form', 'bbforms' ),
//        'tags'  => array(),
//        'icon'  => 'edit',
//    );
//
//    $tags['form']['tags']['form.title'] = array(
//        'label'     => __( 'Form title', 'bbforms' ),
//        'type'      => 'text',
//        'preview'   => __( 'The form title.', 'bbforms' ),
//    );

    // ---------------------------------
    // Fields tags
    // ---------------------------------

    $tags['fields'] = array(
        'label' => __( 'Fields', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'edit',
    );

    $tags['fields']['tags']['field.FIELD_NAME'] = array(
        'label'     => __( 'Field value', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Field value, replace "FIELD_NAME" by the field name.', 'bbforms' ),
    );

    $tags['fields']['tags']['fields'] = array(
        'label'     => __( 'Comma-separated list of form fields', 'bbforms' ),
        'type'      => 'text',
        'preview'   => 'field_1: value_1, field_2: value_2, ...',
    );

    // TODO: For the future
//    $tags['fields']['tags']['fields_array'] = array(
//        'label'     => __( 'Fields array', 'bbforms' ),
//        'type'      => 'text',
//        'preview'   => 'array( "field_1" => "value_1", "field_2" => "value_2" )',
//    );

    $tags['fields']['tags']['fields_table'] = array(
        'label'     => __( 'Fields table', 'bbforms' ),
        'type'      => 'text',
        'preview'   => '<table><tr><td>field_1</td><td>value_1</td></tr></table>',
    );

    // ---------------------------------
    // Site tags
    // ---------------------------------

    $tags['site'] = array(
        'label' => __( 'Site', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'admin-site',
    );

    $tags['site']['tags']['site.name'] = array(
        'label'     => __( 'Site name', 'bbforms' ),
        'type'      => 'text',
        'preview'   => get_bloginfo( 'name' ),
    );

    $tags['site']['tags']['site.url'] = array(
        'label'     => __( 'Site URL', 'bbforms' ),
        'type'      => 'text',
        'preview'   => get_site_url(),
    );

    $tags['site']['tags']['site.admin_email'] = array(
        'label'     => __( 'Admin email', 'bbforms' ),
        'type'      => 'email',
        'preview'   => get_bloginfo( 'admin_email' ),
    );

    $tags['site']['tags']['site.privacy_policy_url'] = array(
        'label'     => __( 'Privacy policy page URL', 'bbforms' ),
        'type'      => 'text',
        'preview'   => get_privacy_policy_url(),
    );

    // ---------------------------------
    // User tags
    // ---------------------------------

    $tags['user'] = array(
        'label' => __( 'User', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'admin-users',
    );

    $tags['user']['tags']['user.id'] = array(
        'label'     => __( 'User ID', 'bbforms' ),
        'type'      => 'integer',
        'preview'   => '123',
    );

    $tags['user']['tags']['user.login'] = array(
        'label'     => __( 'Username', 'bbforms' ),
        'type'      => 'text',
        'preview'   => 'bbforms',
    );

    $tags['user']['tags']['user.email'] = array(
        'label'     => __( 'Email', 'bbforms' ),
        'type'      => 'text',
        'preview'   => 'contact@bbforms.com',
    );

    $tags['user']['tags']['user.display_name'] = array(
        'label'     => __( 'Display name', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'BBForms Plugin', 'bbforms' ),
    );

    $tags['user']['tags']['user.first_name'] = array(
        'label'     => __( 'First name', 'bbforms' ),
        'type'      => 'text',
        'preview'   => 'BBForms',
    );

    $tags['user']['tags']['user.last_name'] = array(
        'label'     => __( 'Last name', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'Plugin', 'bbforms' ),
    );

    $tags['user']['tags']['user.url'] = array(
        'label'     => __( 'User\'s website URL', 'bbforms' ),
        'type'      => 'text',
        'preview'   => 'https://bbforms.com',
    );

    $tags['user']['tags']['user.avatar'] = array(
        'label'     => __( 'Avatar URL', 'bbforms' ),
        'type'      => 'text',
        'preview'   => get_option( 'home' ) . '/wp-content/uploads/avatar.jpg',

    );

    $tags['user']['tags']['user.avatar_img'] = array(
        'label'     => __( 'Avatar Image', 'bbforms' ),
        'type'      => 'text',
        'preview'   => '<img src="' . get_option( 'home' ) . '/wp-content/uploads/avatar.jpg'  . '"/>',
    );

    $tags['user']['tags']['user.reset_password_url'] = array(
        'label'     => __( 'Reset password URL', 'bbforms' ),
        'type'      => 'text',
        'preview'   => get_option( 'home' ) . '/wp-login.php?action=rp',
    );

    $tags['user']['tags']['user.reset_password_link'] = array(
        'label'     => __( 'Reset password link', 'bbforms' ),
        'type'      => 'text',
        'preview'   => '<a href="' . get_option( 'home' ) . '/wp-login.php?action=rp' . '">' . __( 'Click here to reset your password', 'bbforms' ) . '</a>',
    );

    $tags['user']['tags']['user.meta.META_KEY'] = array(
        'label'     => __( 'User Meta', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'User meta value, replace "META_KEY" by the user meta key.', 'bbforms' ),
    );

    // ---------------------------------
    // Datetime tags
    // ---------------------------------

    $tags['date'] = array(
        'label' => __( 'Datetime', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'clock',
    );

    $tags['date']['tags']['date'] = array(
        'label'     => __( 'Datetime', 'bbforms' ),
        'type'      => 'text',
        'preview'   => gmdate( 'Y-m-d H:i:s' ) . '. ' . __( 'The current date and time formatted as "Y-m-d H:i:s".', 'bbforms' ),
    );

    $tags['date']['tags']['date.FORMAT'] = array(
        'label'     => __( 'Formatted datetime', 'bbforms' ),
        'type'      => 'text',
        'preview'   => gmdate( 'Y-m-d H:i:s' ) . '. ' .__( 'The current date and time, replace "FORMAT" by the date format. Default format is "Y-m-d H:i:s".', 'bbforms' ),
    );

    $tags['date']['tags']['date.FORMAT.VALUE'] = array(
        'label'     => __( 'Relative datetime', 'bbforms' ),
        'type'      => 'text',
        'preview'   => gmdate( 'Y-m-d H:i:s', strtotime('+1 day') ) . '. ' . __( 'The relative date and time, replace "FORMAT" by the date format and "VALUE" by the relative date. Default format is "Y-m-d H:i:s" and default value is "now".', 'bbforms' ),
    );

    $tags['date']['tags']['timestamp'] = array(
        'label'     => __( 'Timestamp', 'bbforms' ),
        'type'      => 'int',
        'preview'   => strtotime( 'now', current_time( 'timestamp' ) ) . '. ' . __( 'The current timestamp.', 'bbforms' ),
    );

    $tags['date']['tags']['timestamp.VALUE'] = array(
        'label'     => __( 'Relative timestamp', 'bbforms' ),
        'type'      => 'int',
        'preview'   => strtotime( '+1 day', current_time( 'timestamp' ) ) . '. ' . __( 'The relative timestamp, replace "VALUE" by the relative date. Default value is "now".', 'bbforms' ),
    );

    // ---------------------------------
    // URL tags
    // ---------------------------------

    $tags['url'] = array(
        'label' => __( 'URL', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'admin-links',
    );

    $tags['url']['tags']['url.PARAM'] = array(
        'label'     => __( 'URL Parameter', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'URL parameter value, replace "PARAM" by the parameter name.', 'bbforms' ),
    );

    // ---------------------------------
    // Settings
    // ---------------------------------

    $tags['settings'] = array(
        'label' => __( 'Settings', 'bbforms' ),
        'tags'  => array(),
        'icon'  => 'admin-generic',
    );

    $tags['settings']['tags']['settings.SETTING_NAME'] = array(
        'label'     => __( 'Setting value', 'bbforms' ),
        'type'      => 'text',
        'preview'   => __( 'BBForms setting value, replace "SETTING_NAME" by the setting name.', 'bbforms' ),
    );

    $form_messages = bbforms_get_form_messages();
    $form_messages_labels = bbforms_get_form_messages_labels();

    foreach ( $form_messages as $key => $form_message ) {

        $tags['settings']['tags']['settings.' . $key] = array(
            // translators: %s: Message name
            'label'     => ( isset( $form_messages_labels[$key] ) ? sprintf( __( '%s Message', 'bbforms' ), strip_tags( $form_messages_labels[$key] ) )  : $key ),
            'type'      => 'text',
            'preview'   => bbforms_get_form_message( $key ),
        );

    }

    /**
     * Filter tags
     *
     * @since 1.0.0
     *
     * @param array $tags The tags
     *
     * @return array
     */
    return apply_filters( 'bbforms_get_tags', $tags );

}

function bbforms_get_all_tags() {

    $tags = bbforms_get_tags();
    $all_tags = array();

    foreach ( $tags as $group => $group_args ) {

        foreach( $group_args['tags'] as $tag => $tag_args ) {
            $all_tags[$tag] = $tag_args;
        }

    }

    return $all_tags;

}

/**
 * Parse tags
 *
 * @since 1.0.0
 *
 * @param BBForms_Form  $form               The form
 * @param int           $user_id            The user ID
 * @param string|array  $content            The content to parse
 *
 * @return string|array
 */
function bbforms_do_tags( $form, $user_id, $content ) {

    // Check if content given is an array to parse each array element
    if( is_array( $content ) ) {

        foreach( $content as $k => $v ) {
            // Replace all tags on this array element
            $content[$k] = bbforms_do_tags( $form, $user_id, $v );
        }

        return $content;

    }

    $parsed_content = $content;

    // Replace tags inside tags first
    $parsed_content = bbforms_replace_tags_inside_tags( $form, $user_id, $parsed_content );

    // Get all tags replacements
    $replacements = bbforms_get_tags_replacements( $form, $user_id, $parsed_content );

    $tags = array_keys( $replacements );

    /**
     * Available filter to setup custom replacements
     *
     * @since 1.0.0
     *
     * @param string    $parsed_content     Content parsed
     * @param array     $replacements       Tags replacements
     * @param stdClass  $form               The form
     * @param int       $user_id            The user ID
     * @param string    $content            The content to parse
     *
     * @return string
     */
    $parsed_content = apply_filters( 'bbforms_do_tags', $parsed_content, $replacements, $form, $user_id, $content );

    // Parse only found tags
    $parsed_content = str_replace( $tags, $replacements, $parsed_content );

    /**
     * Available filter to setup custom replacements (with parsed content parsed)
     *
     * @since 1.0.0
     *
     * @param string    $parsed_content     Content parsed
     * @param array     $replacements       Tags replacements
     * @param stdClass  $form               The form
     * @param int       $user_id            The user ID
     * @param string    $content            The content to parse
     *
     * @return string
     */
    $parsed_content = apply_filters( 'bbforms_post_do_tags', $parsed_content, $replacements, $form, $user_id, $content );

    return $parsed_content;

}

/**
 * Replace tags inside tags
 *
 * @since 1.0.0
 *
 * @param stdClass      $form               The form
 * @param int           $user_id            The user ID
 * @param string|array  $content            The content to parse
 *
 * @return string                           Content with tags inside other tags already parsed
 */
function bbforms_replace_tags_inside_tags( $form, $user_id, $content ) {

    // Look for tags inside tags
    preg_match_all( "/\{.*\{(.*?)\}.*\}/", $content, $matches );

    $replacements = array();
    $found_more_sub = false;

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $i => $tag_name ) {

            if( substr_count( $matches[0][$i], '{' ) > 2 && substr_count( $matches[0][$i], '}' ) > 2 ) {
                $found_more_sub = true;
            }

            $replacement = bbforms_do_tag( $tag_name, $form, $user_id, $content );

            if( $replacement !== 'bbforms_tag_not_found' ) {
                // Setup tags replacements
                $replacements['{' . $tag_name . '}'] = $replacement;
            }
        }

        if( count( $replacements ) ) {
            $content = str_replace( array_keys( $replacements ), $replacements, $content );
        }
    }

    if( $found_more_sub ) {
        $content = bbforms_replace_tags_inside_tags( $form, $user_id, $content );
    }

    return $content;

}

/**
 * Get tags replacements
 *
 * @since 1.0.0
 *
 * @param stdClass      $form               The form
 * @param int           $user_id            The user ID
 * @param string|array  $content            The content to parse
 *
 * @return string|array
 */
function bbforms_get_tags_replacements( $form, $user_id, $content ) {

    // Look for tags
    preg_match_all( "/\{\s*(.*?)\s*\}/", $content, $matches );

    $replacements = array();

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $tag_name ) {

            $replacement = bbforms_do_tag( $tag_name, $form, $user_id, $content );

            if( $replacement !== 'bbforms_tag_not_found' ) {
                // Setup tags replacements
                $replacements['{' . $tag_name . '}'] = $replacement;
            }
        }

    }

    /**
     * Available filter to setup custom replacements
     *
     * @since 1.0.0
     *
     * @param array     $replacements   Automation replacements
     * @param stdClass  $form           The form
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     *
     * @return array
     */
    return apply_filters( 'bbforms_get_tags_replacements', $replacements, $form, $user_id, $content );

}

/**
 * Parse tag
 *
 * @since 1.0.0
 *
 * @param string        $tag_name       The tag name (without "{}")
 * @param BBForms_Form  $form           The form
 * @param int           $user_id        The user ID
 * @param string        $content        The content to parse
 *
 * @return string
 */
function bbforms_do_tag( $tag_name = '', $form = null, $user_id = 0, $content = '' ) {

    // Flag to meet if tag have been found or not
    $replacement = 'bbforms_tag_not_found';

    // Common tags
    switch( $tag_name ) {
        // Site tags
        case 'site.name':
            $replacement = get_bloginfo( 'name' );
            break;
        case 'site.url':
            $replacement = get_site_url();
            break;
        case 'site.admin_email':
            $replacement = get_bloginfo( 'admin_email' );
            break;
        case 'site.privacy_policy_url':
            $replacement = get_privacy_policy_url();
            break;
        // Date and time tags
        case 'date':
            $replacement = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
            break;
        case 'timestamp':
            $replacement = current_time( 'timestamp' );
            break;
    }



    // Form tags
    if( $form ) {

        switch( $tag_name ) {
            // Site tags
            case 'form.title':
                $replacement = $form->title;
                break;
        }

    }

    // Fields tags
    if( $form ) {

        $form_fields = $form->get_fields_values();
        $excluded_fields = apply_filters( 'bbforms_tags_excluded_fields', array(
            '_bbforms',
            '_bbforms_version',
            '_bbforms_nonce',
            '_bbforms_post',
            '_bbforms_user',
            '_bbforms_hp_' . $form->id,
        ) );

        switch( $tag_name ) {
            case 'fields':
                $replacement = '';

                foreach( $form_fields as $name => $value ) {
                    if( in_array( $name, $excluded_fields ) ) {
                        continue;
                    }

                    if( is_array( $value ) ) {
                        $value = '[' . implode( ', ', $value ) . ']';
                    }

                    $replacement .= $name . ':' . $value . "\n";
                }
                break;
            case 'fields_array':
                $replacement = $form_fields;
                break;
            case 'fields_table':
                $replacement = '<table>';

                $excluded_bbcodes = apply_filters( 'bbforms_fields_table_tag_excluded_bbcodes', array(
                    'submit',
                    'reset',
                    'honeypot',
                ) );

                foreach( $form_fields as $name => $value ) {
                    // Skip field by name
                    if( in_array( $name, $excluded_fields ) ) {
                        continue;
                    }

                    $field = $form->get_field( $name );

                    // Skip field by BBCode
                    if( in_array( $field->bbcode, $excluded_bbcodes ) ) {
                        continue;
                    }

                    // Check if value is an array to format it's output
                    if( is_array( $value ) ) {

                        foreach ( $value as $key => $sub_value ) {

                            if( is_array( $sub_value ) ) {
                                foreach( $sub_value as $sub_key => $sub_sub_value ) {
                                    $sub_value[$sub_key] = bbforms_fields_table_get_field_value( $sub_sub_value, $field, $form, $name );
                                }

                                $value[$key] = '[' . implode( ', ', $sub_value ) . ']';
                            } else {
                                $value[$key] = bbforms_fields_table_get_field_value( $sub_value, $field, $form, $name );
                            }

                        }

                        $value = implode( '<br>', $value );
                    } else {
                        $value = bbforms_fields_table_get_field_value( $value, $field, $form, $name );
                    }



                    /**
                     * Filter to skip a field value in the  {fields_table} tag
                     *
                     * @since 1.0.0
                     *
                     * @param bool          $skip
                     * @param string        $value
                     * @param BBForms_Field $field
                     * @param BBForms_Form  $form
                     * @param string        $name
                     *
                     * @return bool
                     */
                    if( ! apply_filters( 'bbforms_fields_table_tag_skip_field_value', false, $value, $field, $form, $name ) ) {
                        $replacement .= '<tr><td><b>' . $form->get_field_label( $name ) . '</b></td><td>' . $value . "</td></tr>";

                    }

                }
                $replacement .= '</table>';
                break;
        }

        // field.FIELD_NAME tag
        if( bbforms_starts_with( $tag_name, 'field.' ) ) {

            $field_name = explode('.', $tag_name)[1];

            $replacement = isset( $form_fields[$field_name] ) ? $form_fields[$field_name] : '';
        }

    }

    // User tags
    if( bbforms_starts_with( $tag_name, 'user.' ) ) {

        $user = get_userdata( $user_id );

        switch( $tag_name ) {
            case 'user.id':
                $replacement = ( $user ? $user->ID : '' );
                break;
            case 'user.login':
                $replacement = ( $user ? $user->user_login : '' );
                break;
            case 'user.email':
                $replacement = ( $user ? $user->user_email : '' );
                break;
            case 'user.display_name':
                $replacement = ( $user ? $user->display_name : '' );
                break;
            case 'user.first_name':
                $replacement = ( $user ? $user->first_name : '' );
                break;
            case 'user.last_name':
                $replacement = ( $user ? $user->last_name : '' );
                break;
            case 'user.url':
                $replacement = ( $user ? $user->user_url : '' );
                break;
            case 'user.avatar':
            case 'user.avatar_img':
                $user_id = ( $user ? $user->ID : 0 );
                $url = get_avatar_url( $user_id, array( 'force_default' => true ) );
                $replacement = $url;

                if( $tag_name === 'avatar_img' ) {
                    $replacement = '<img src="' . $url . '"/>';
                }
                break;
            case 'user.reset_password_url':
            case 'user.reset_password_link':
                $key = ( $user ?  get_password_reset_key( $user ) : '' );
                $login = ( $user ?  rawurlencode( $user->user_login ) : '' );
                $url = ( $user ? network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . $login, 'login' ) : '' );
                $replacement = $url;

                if( $tag_name === 'reset_password_link' ) {
                    $replacement = '<a href="' . $url . '">' . __( 'Click here to reset your password', 'bbforms' ) . '</a>';
                }
                break;
        }

        // user.meta.META_KEY tag
        if( bbforms_starts_with( $tag_name, 'user.meta.' ) && $user ) {

            $meta_key = explode( '.', $tag_name )[2];

            if( strpos( $meta_key, '/' ) !== false ) {
                // user.meta.META_KEY/SUBKEY
                $keys = explode( "/", $meta_key );

                $value = get_user_meta( $user_id, $keys[0], true );
                $meta_value = '';

                for( $i=1; $i < count( $keys ); $i++ ) {
                    $key = $keys[$i];

                    if( ! empty( $key ) ) {
                        $meta_value = isset( $value[$key] ) ? $value[$key] : '';
                    }
                }
            } else {
                // user,meta.META_KEY
                $meta_value = get_user_meta( $user_id, $meta_key, true );
            }

            $replacement = $meta_value;

        }
    }

    // date.FORMAT
    // date.FORMAT.VALUE
    if( bbforms_starts_with( $tag_name, 'date.' ) ) {

        $params = explode('.', $tag_name);
        $format = isset( $params[1] ) ? $params[1] : 'Y-m-d H:i:s';
        $value = isset( $params[2] ) ? $params[2] : 'now';

        if( $format === 'FORMAT' ) $format = 'Y-m-d H:i:s';
        if( $value === 'VALUE' ) $format = 'now';

        $replacement = gmdate( $format, strtotime( $value, current_time( 'timestamp' ) ) );
    }

    // timestamp.VALUE tag
    if( bbforms_starts_with( $tag_name, 'timestamp.' ) ) {

        $value = explode('.', $tag_name)[1];

        $replacement = strtotime( ( $value === 'VALUE' ? 'now' : $value ), current_time( 'timestamp' ) );
    }

    // url.PARAM tag
    if( bbforms_starts_with( $tag_name, 'url.' ) ) {

        $param = explode('.', $tag_name)[1];

        $replacement = isset( $_REQUEST[$param] ) ? wp_unslash( $_REQUEST[$param] ) : '';
        $replacement = ! is_array( $replacement ) ? sanitize_text_field( $replacement ) : array_map( 'sanitize_text_field', $replacement );
    }

    // Settings tags
    if( bbforms_starts_with( $tag_name, 'settings.' ) ) {

        $setting = explode('.', $tag_name)[1];

        $form_messages = bbforms_get_form_messages();
        $error_messages = bbforms_get_error_messages();

        if( isset( $form_messages[$setting] ) ) {
            $replacement = bbforms_get_form_message( $setting );
        } else if( isset( $error_messages[$setting] ) ) {
            $replacement = bbforms_get_error_messages( $setting );
        } else {
            $replacement = bbforms_get_option( $setting, '' );
        }

    }

    /**
     * Filter the tag replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param stdClass  $form           The form
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     *
     * @return string
     */
    return apply_filters( 'bbforms_do_tag', $replacement, $tag_name, $form, $user_id, $content );

}

/**
 * Get the field value to show in the  {fields_table} tag
 *
 * @since 1.0.0
 *
 * @param string        $value
 * @param BBForms_Field $field
 * @param BBForms_Form  $form
 * @param string        $name
 *
 * @return string
 */
function bbforms_fields_table_get_field_value( $value, $field, $form, $name ) {

    //  File format
    if( $field->bbcode === 'file' && ! empty( $value ) ) {
        $parts = explode( '/' , $value );
        $filename = end( $parts );
        $file_url = str_replace( BBFORMS_UPLOAD_DIR, BBFORMS_UPLOAD_URL, $value );

        $value = '<a href="' . esc_attr( $file_url ) . '" target="_blank">' . esc_html( $filename ) . '</a>';
    }

    /**
     * Filter to override the field value to show in the  {fields_table} tag
     *
     * @since 1.0.0
     *
     * @param string        $value
     * @param BBForms_Field $field
     * @param BBForms_Form  $form
     * @param string        $name
     *
     * @return string
     */
    return apply_filters( 'bbforms_fields_table_tag_field_value', $value, $field, $form, $name );

}