<?php
/**
 * Help
 *
 * @package     BBForms\Help
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Fields help
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_fields_help() {

    $output = '';

    $fields_sections_labels = bbforms_editor_controls_get_fields_sections_labels();
    $fields_order_original  = bbforms_editor_controls_get_fields_order();
    $fields_order = array();

    foreach( $fields_order_original as $section => $fields ) {
        $fields_order = array_merge( $fields_order, $fields );
    }

    $output .= '<div class="bbforms-dialog-tabs">';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-1 bbforms-dialog-tab-active" data-toggle=".bbforms-fields-help-content-1">';
            $output .= esc_html__( 'Getting Started', 'bbforms' );
        $output .= '</div>';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-2" data-toggle=".bbforms-fields-help-content-2">';
            $output .= esc_html__( 'Fields List', 'bbforms' );
        $output .= '</div>';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-3" data-toggle=".bbforms-fields-help-content-3">';
            $output .= esc_html__( 'Label & Description', 'bbforms' );
        $output .= '</div>';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-4" data-toggle=".bbforms-fields-help-content-4">';
            $output .= esc_html__( 'HTML Attributes', 'bbforms' );
        $output .= '</div>';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-5" data-toggle=".bbforms-fields-help-content-5">';
            $output .= esc_html__( 'Editor Tricks', 'bbforms' );
        $output .= '</div>';
    $output .= '</div>';

    $output .= '<div class="bbforms-help bbforms-fields-help cm-s-default">';

    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-1 bbforms-dialog-tab-content-active">';

    // Getting Started

    $output .= esc_html__( 'Fields help you to setup your form in a easy way.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Basic field:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text name="your_text"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'IMPORTANT: Fields always require a unique name.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Required field (just add an * (asterisk) after it\'s BBCode):', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text* name="your_text"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Field with an initial value:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text* name="your_text" value="' . esc_html__( 'Hi!', 'bbforms' ) . '"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'User tag as initial value:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text* name="your_text" value="{user.email}"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'All fields accepts global HTML attributes:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text name="your_text" id="my-id" class="my-class" style="background: red;"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'IMPORTANT: Submit button is essential to let the user send the form so, don\'t forgive to place at least one!', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[submit value="Submit"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'EXTRA: Reset button let\'s user to clear the form:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[reset value="Reset"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Check the "Fields List" tab to meet all of them!', 'bbforms' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-2">';

    // Fields List

    $add_ons_output = apply_filters( 'bbforms_fields_help_fields_list_add_ons_section_content', '', $fields_order );

    $output .= '<div class="bbforms-dialog-tabs">';
    foreach( $fields_sections_labels as $i => $field_section ) {
        if( $i === 'add_ons' && $add_ons_output === '' ) {
            continue;
        }

        $active = ( $i === 'inputs' ? 'bbforms-dialog-tab-active' : '' );
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-help-content-' . esc_attr( $i ) . ' ' . $active . '" data-toggle=".bbforms-fields-help-content-2-section-' . esc_attr( $i ) . '">';
        $output .= bbforms_dashicon( $field_section['icon'] ) . ' ' . esc_html( $field_section['label'] );
        $output .= '</div>';
    }
    $output .= '</div>';

    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-2-section-inputs bbforms-dialog-tab-content-active">';

    $common_fields_attrs = array(
        'name' => array(
            'codes' => array(
                'name="unique_field_name"',
            ),
            'desc' => esc_html__( 'The field name. Needs to be unique in the form.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'value' => array(
            'codes' => array(
                'value="' . esc_html__( 'Text', 'bbforms' ) . '"',
                'value="{user.first_name}"',
                'value="{field.field_name}"',
            ),
            'desc' => esc_html__( 'The initial field value.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'placeholder' => array(
            'codes' => array(
                'placeholder="' . esc_html__( 'Insert text here', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Text displayed when the field has no value.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'min' => array(
            'codes' => array(
                'min="10"',
            ),
            'desc' => esc_html__( 'Minimum characters length allowed.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' )
                . '<br>'
                // translators: %s: attribute
                . '<br>' . sprintf( esc_html__( 'NOTE: Shortcut of %s attribute.', 'bbforms' ), '<code>minlength</code>' ),
        ),
        'max' => array(
            'codes' => array(
                'max="100"',
            ),
            'desc' => esc_html__( 'Maximum characters length allowed.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' )
                . '<br>'
                // translators: %s: attribute
                . '<br>' . sprintf( esc_html__( 'NOTE: Shortcut of %s attribute.', 'bbforms' ), '<code>maxlength</code>' ),
        ),
        'pattern' => array(
            'codes' => array(
                'pattern="' . esc_html__( 'Regular expression', 'bbforms' ) . '"',
                'pattern="[a-zA-Z0-9]+"',
                'pattern="\d+"',
            ),
            'desc' => esc_html__( 'A regular expression the field value should match.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    );

    $output .= '<h3>' . bbforms_dashicon( $fields_order['text'] ) . ' ' . esc_html__( 'Text', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[text name="your_text"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Text field.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( $common_fields_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['textarea'] ) . ' ' . esc_html__( 'Text Area', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[textarea name="your_textarea"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Text area field.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $textarea_field_attrs = $common_fields_attrs;
    $textarea_field_attrs['rows'] = array(
        'codes' => array(
            'rows="5"',
        ),
        'desc' => esc_html__( 'Defines the visible text lines.', 'bbforms' )
            . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
    );
    $textarea_field_attrs['cols'] = array(
        'codes' => array(
            'cols="5"',
        ),
        'desc' => esc_html__( 'Defines the visible width.', 'bbforms' )
            . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
    );
    $output .= bbforms_attrs_table( $textarea_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['email'] ) . ' ' . esc_html__( 'Email', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[email name="your_email"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Text field that forces to enter a valid email address.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $email_field_attrs = $common_fields_attrs;
    $email_field_attrs['value']['codes'] = array(
        'value="contact@bbforms.com"',
        'value="{user.email}"',
        'value="{field.email_123}"',
    );
    $email_field_attrs['placeholder']['codes'] = array(
        'value="' . esc_html__( 'Insert email here.', 'bbforms' ) . '"',
    );
    $email_field_attrs['pattern']['codes'] = array(
        'pattern="' . esc_html__( 'Regular expression', 'bbforms' ) . '"',
        'pattern="^.+@gmail\.com$"',
        'pattern="^.+@outlook\.com$"',
    );
    $output .= bbforms_attrs_table( $email_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['tel'] ) . ' ' . esc_html__( 'Phone', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[tel name="your_tel"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Commonly used with a pattern to force a phone format.', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'NOTE: "tel" is from "Telephone", the good-old days....', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $phone_field_attrs = $common_fields_attrs;
    $phone_field_attrs['value']['codes'] = array(
        'value="555-555-5555"',
        'value="999999999"',
        'value="{user.meta.phone}"',
        'value="{field.tel_123}"',
    );
    $phone_field_attrs['placeholder']['codes'] = array(
        'value="' . esc_html__( 'Insert phone here.', 'bbforms' ) . '"',
    );
    $phone_field_attrs['pattern']['codes'] = array(
        'pattern="' . esc_html__( 'Regular expression', 'bbforms' ) . '"',
        'pattern="[0-9]{3}-[0-9]{3}-[0-9]{3}"',
        'pattern="[0-9]{9}"',
    );
    $output .= bbforms_attrs_table( $phone_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['url'] ) . ' ' . esc_html__( 'URL', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[url name="your_url"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Forces to enter a valid URL.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $url_field_attrs = $common_fields_attrs;
    $url_field_attrs['value']['codes'] = array(
        'value="https://bbforms.com"',
        'value="{site.url}"',
        'value="{user.website}"',
        'value="{field.url_123}"',
    );
    $url_field_attrs['placeholder']['codes'] = array(
        'value="' . esc_html__( 'Insert URL here.', 'bbforms' ) . '"',
    );
    $url_field_attrs['pattern']['codes'] = array(
        'pattern="' . esc_html__( 'Regular expression', 'bbforms' ) . '"',
        'pattern="https://.*"',
        'pattern=".*\.your-site\.com.*"',
    );
    $output .= bbforms_attrs_table( $url_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['password'] ) . ' ' . esc_html__( 'Password', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[password name="your_text"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Password field. The entered value will be displayed as * (asterisk).', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( $common_fields_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['date'] ) . ' ' . esc_html__( 'Date', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[date name="your_date"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Commonly browsers show a date picker with the local date format but the value is always stored as YYYY-MM-DD.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $date_field_attrs = $common_fields_attrs;
    $date_field_attrs['value']['codes'] = array(
        'value="2025-01-01"',
        'value="2025-12-31"',
        'value="{field.date_123}"',
    );

    $date_field_attrs['min']['codes'] = array(
        'min="2025-01-01"',
    );
    $date_field_attrs['min']['desc'] = esc_html__( 'Minimum date allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    $date_field_attrs['max']['codes'] = array(
        'max="2025-12-31"',
    );
    $date_field_attrs['max']['desc'] = esc_html__( 'Maximum date allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    unset( $date_field_attrs['placeholder'] );
    unset( $date_field_attrs['pattern'] );
    $output .= bbforms_attrs_table( $date_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['time'] ) . ' ' . esc_html__( 'Time', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[time name="your_time"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Commonly browsers show a time picker in format 23:59.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $time_field_attrs = $common_fields_attrs;
    $time_field_attrs['value']['codes'] = array(
        'value="08:00"',
        'value="16:30"',
        'value="{field.time_123}"',
    );

    $time_field_attrs['min']['codes'] = array(
        'min="08:00"',
    );
    $time_field_attrs['min']['desc'] = esc_html__( 'Minimum time allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    $time_field_attrs['max']['codes'] = array(
        'max="16:30"',
    );
    $time_field_attrs['max']['desc'] = esc_html__( 'Maximum time allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    unset( $time_field_attrs['placeholder'] );
    unset( $time_field_attrs['pattern'] );
    $output .= bbforms_attrs_table( $time_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['file'] ) . ' ' . esc_html__( 'File', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[file name="your_file"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Field that allows to upload a file.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $file_field_attrs = $common_fields_attrs;

    $file_field_attrs['min']['codes'] = array(
        'min="1"',
        'min="1kb"',
        'min="1mb"',
    );
    $file_field_attrs['min']['desc'] = esc_html__( 'Minimum file size allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Accepts: Number + suffix (kb, mb, gb & tb) or only number to define the size in bytes.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    $file_field_attrs['max']['codes'] = array(
        'max="10"',
        'max="10kb"',
        'max="10mb"',
    );
    $file_field_attrs['max']['desc'] = esc_html__( 'Maximum file size allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Accepts: Number + suffix (kb, mb, gb & tb) or only number to define the size in bytes.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    $file_field_attrs['accept'] = array(
        'codes' => array(
            'accept=".txt"',
            'accept=".txt,image/jpeg"',
            'accept=".txt,image/jpeg,video/*"',
        ),
        'desc' => esc_html__( 'A comma-separated list of file types allowed.', 'bbforms' )
            . '<br>' . esc_html__( 'Accepts: File extensiones (.pdf), MIME types (application/pdf) and wildcard MIME types (application/*, which means that all subtypes of that MIME type are allowed).', 'bbforms' )
            . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
    );

    $file_field_attrs['capture'] = array(
        'codes' => array(
            'capture="user"',
            'capture="environment"',
        ),
        'desc' => esc_html__( 'For mobiles mainly, specifies which device should capture the media, for images, can be the user-facing camera (user) or the outward-facing camera (environment).', 'bbforms' )
            . '<br>' . esc_html__( 'Accepts: user or environment.', 'bbforms' )
            . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
    );

    $file_field_attrs['media'] = array(
        'codes' => array(
            'media="yes"',
            'media="no"',
        ),
        'desc' => esc_html__( 'Decides if file should be registered in the WordPress media. Recommended to leave it to yes to be able to access to the file details from the media library.', 'bbforms' )
            . '<br>' . esc_html__( 'Accepts: yes or no.', 'bbforms' )
            . '<br>' . esc_html__( 'Default:', 'bbforms' ) . ' yes.'
            . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
    );

    unset( $file_field_attrs['value'] );
    unset( $file_field_attrs['placeholder'] );
    unset( $file_field_attrs['pattern'] );
    $output .= bbforms_attrs_table( $file_field_attrs );
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-2-section-numerics">';

    $numeric_field_attrs = $common_fields_attrs;

    $numeric_field_attrs['min']['codes'] = array(
        'min="1"',
        'min="0.1"',
    );
    $numeric_field_attrs['min']['desc'] = esc_html__( 'Minimum number allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    $numeric_field_attrs['max']['codes'] = array(
        'max="100"',
        'max="1.0"',
    );
    $numeric_field_attrs['max']['desc'] = esc_html__( 'Maximum number allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Optional.', 'bbforms' );

    $numeric_field_attrs['step'] = array(
        'codes' => array(
            'step="1"',
            'step="5"',
            'step="0.1"',
            'step="0.5"',
        ),
        'desc' => esc_html__( 'Maximum number allowed.', 'bbforms' )
            . '<br>' . esc_html__( 'Default:', 'bbforms' ) . ' 1.'
            . '<br>' . esc_html__( 'Optional.', 'bbforms' )
    );

    unset( $numeric_field_attrs['pattern'] );

    $numeric_field_attrs['pattern'] = array(
        'codes' => array(
            'pattern="' . esc_html__( 'Regular expression', 'bbforms' ) . '"',
            'pattern="\d{3}"',
            'pattern="\d{1,10}"',
        ),
        'desc' => esc_html__( 'A regular expression the field value should match.', 'bbforms' )
            . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
    );


    $output .= '<h3>' . bbforms_dashicon( $fields_order['number'] ) . ' ' . esc_html__( 'Number', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[number name="your_number"]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[number name="your_decimal" step="0.1"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Allows to input a number.', 'bbforms' ) . ' ' . esc_html__( 'Supports decimal numbers using the "step" attribute.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( $numeric_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['range'] ) . ' ' . esc_html__( 'Range', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[range name="your_range" min="" max=""]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Allows to input a number between a range using a slider.', 'bbforms' ) . ' ' . esc_html__( 'Requires "min" and "max" attributes.', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Also supports decimal numbers, using the "step" attribute.', 'bbforms' ) . '<br>';
    $output .= '<br>';

    $range_field_attrs = $numeric_field_attrs;
    $range_field_attrs['min']['desc'] = esc_html__( 'Minimum number allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Required.', 'bbforms' );
    $range_field_attrs['max']['desc'] = esc_html__( 'Maximum number allowed.', 'bbforms' )
        . '<br>' . esc_html__( 'Required.', 'bbforms' );

    $output .= bbforms_attrs_table( $range_field_attrs );
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-2-section-options">';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['check'] ) . ' ' . esc_html__( 'Checkbox', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[check name="your_check"]Option 1[/check]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[check name="your_check_multiple"]' ) . '<br>';
    $output .= esc_html__( 'Option 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Option 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/check]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Checkbox field. Supports single and multiple options.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'name' => array(
            'codes' => array(
                'name="unique_field_name"',
            ),
            'desc' => esc_html__( 'The field name. Needs to be unique in the form.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'value' => array(
            'codes' => array(
                'value="' . esc_html__( 'Option 2', 'bbforms' ) . '"',
                'value="2"',
            ),
            'desc' => esc_html__( 'The initial checked option.', 'bbforms' )
                . '<br>' . esc_html__( 'Note: The value needs to match with the option value, so if you define an option like "2|Option 2", to check this option you need to set the value as value="2".', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'inline' => array(
            'codes' => array(
                'inline="no"',
                'inline="yes"',
            ),
            'desc' => esc_html__( 'Decides if options should be displayed inline.', 'bbforms' )
                . '<br>' . esc_html__( 'Default:', 'bbforms' ) . ' no.'
                . '<br>' . esc_html__( 'If set to "no", options will be displayed as:', 'bbforms' )
                . '<br>' . '<span class="dashicons dashicons-check"></span> ' . esc_html__( 'Option 1', 'bbforms' ) . ' <span class="dashicons dashicons-check"></span> ' . esc_html__( 'Option 2', 'bbforms' )
                . '<br>' . esc_html__( 'If set to "yes", options will be displayed as:', 'bbforms' )
                . '<br>' . '<span class="dashicons dashicons-check"></span> ' . esc_html__( 'Option 1', 'bbforms' )
                . '<br>' . '<span class="dashicons dashicons-check"></span> ' . esc_html__( 'Option 2', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[check]' . esc_html__( 'Option 1', 'bbforms' ) . '[/check]',
                '[check]' . '1|' . esc_html__( 'Option 1', 'bbforms' ) . '[/check]',
                '[check]' . '<br>'
                . esc_html__( 'Option 1', 'bbforms' ) . '<br>'
                . esc_html__( 'Option 2', 'bbforms' ) . '<br>'
                . '[/check]',
                '[check]' . '<br>'
                . '1|' . esc_html__( 'Option 1', 'bbforms' ) . '<br>'
                . '2|' . esc_html__( 'Option 2', 'bbforms' ) . '<br>'
                . '[/check]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as the field options.', 'bbforms' )
                . '<br>' . esc_html__( 'Options can be defined as "Text" or "Value|Text". When options are defined as "Text" the internal value will be the option text itself. When options are defined as "Value|Text" the internal value will be the "Value" part of the option and the option will be displayed as the "Text" part.', 'bbforms' )
                . '<br>' . esc_html__( 'Example: An option defined as "2|Option 2" will store the value "2" and show the text "Option 2".', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['radio'] ) . ' ' . esc_html__( 'Radio:', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[radio name="your_radio"]' ) . '<br>';
    $output .= esc_html__( 'Option 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Option 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/radio]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Radio field.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'name' => array(
            'codes' => array(
                'name="unique_field_name"',
            ),
            'desc' => esc_html__( 'The field name. Needs to be unique in the form.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'value' => array(
            'codes' => array(
                'value="' . esc_html__( 'Option 2', 'bbforms' ) . '"',
                'value="2"',
            ),
            'desc' => esc_html__( 'The initial checked option.', 'bbforms' )
                . '<br>' . esc_html__( 'Note: The value needs to match with the option value, so if you define an option like "2|Option 2", to check this option you need to set the value as value="2".', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'inline' => array(
            'codes' => array(
                'inline="no"',
                'inline="yes"',
            ),
            'desc' => esc_html__( 'Decides if options should be displayed inline.', 'bbforms' )
                . '<br>' . esc_html__( 'Default:', 'bbforms' ) . ' no.'
                . '<br>' . esc_html__( 'If set to "no", options will be displayed as:', 'bbforms' )
                . '<br>' . '<span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 1', 'bbforms' ) . ' <span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 2', 'bbforms' )
                . '<br>' . esc_html__( 'If set to "yes", options will be displayed as:', 'bbforms' )
                . '<br>' . '<span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 1', 'bbforms' )
                . '<br>' . '<span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 2', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[radio]' . esc_html__( 'Option 1', 'bbforms' ) . '[/radio]',
                '[radio]' . '1|' . esc_html__( 'Option 1', 'bbforms' ) . '[/radio]',
                '[radio]' . '<br>'
                . esc_html__( 'Option 1', 'bbforms' ) . '<br>'
                . esc_html__( 'Option 2', 'bbforms' ) . '<br>'
                . '[/radio]',
                '[radio]' . '<br>'
                . '1|' . esc_html__( 'Option 1', 'bbforms' ) . '<br>'
                . '2|' . esc_html__( 'Option 2', 'bbforms' ) . '<br>'
                . '[/radio]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as the field options.', 'bbforms' )
                . '<br>' . esc_html__( 'Options can be defined as "Text" or "Value|Text". When options are defined as "Text" the internal value will be the option text itself. When options are defined as "Value|Text" the internal value will be the "Value" part of the option and the option will be displayed as the "Text" part.', 'bbforms' )
                . '<br>' . esc_html__( 'Example: An option defined as "2|Option 2" will store the value "2" and show the text "Option 2".', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['select'] ) . ' ' .  esc_html__( 'Select', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[select name="your_select"]' ) . '<br>';
    $output .= esc_html__( 'Option 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Option 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/select]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Select field.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'name' => array(
            'codes' => array(
                'name="unique_field_name"',
            ),
            'desc' => esc_html__( 'The field name. Needs to be unique in the form.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'value' => array(
            'codes' => array(
                'value="' . esc_html__( 'Option 2', 'bbforms' ) . '"',
                'value="2"',
            ),
            'desc' => esc_html__( 'The initial selected option.', 'bbforms' )
                . '<br>' . esc_html__( 'Note: The value needs to match with the option value, so if you define an option like "2|Option 2", to select this option you need to set the value as value="2".', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'placeholder' => array(
            'codes' => array(
                'placeholder="' . esc_html__( 'Choose an option', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Adds a disabled option at the start of the options list to work as placeholder.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'multiple' => array(
            'codes' => array(
                'multiple="multiple"',
            ),
            'desc' => esc_html__( 'Allows user to select multiple options.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[select]' . '<br>'
                . esc_html__( 'Option 1', 'bbforms' ) . '<br>'
                . esc_html__( 'Option 2', 'bbforms' ) . '<br>'
                . '[/select]',
                '[select]' . '<br>'
                . '1|' . esc_html__( 'Option 1', 'bbforms' ) . '<br>'
                . '2|' . esc_html__( 'Option 2', 'bbforms' ) . '<br>'
                . '[/select]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as the field options.', 'bbforms' )
                . '<br>' . esc_html__( 'Options can be defined as "Text" or "Value|Text". When options are defined as "Text" the internal value will be the option text itself. When options are defined as "Value|Text" the internal value will be the "Value" part of the option and the option will be displayed as the "Text" part.', 'bbforms' )
                . '<br>' . esc_html__( 'Example: An option defined as "2|Option 2" will store the value "2" and show the text "Option 2".', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['country'] ) . ' ' .  esc_html__( 'Country', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[country name="your_select" save_as="code"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Country field.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'name' => array(
            'codes' => array(
                'name="unique_field_name"',
            ),
            'desc' => esc_html__( 'The field name. Needs to be unique in the form.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'save_as' => array(
            'codes' => array(
                'save_as="code"',
                'save_as="code_lower"',
                'save_as="name"',
                'save_as="name_lower"',
            ),
            'desc' => esc_html__( 'Defines what to save.', 'bbforms' )
                . '<br>code: ' . esc_html__( 'Saves the country code in upper case. Eg: "ES".', 'bbforms' )
                . '<br>code_lower: ' . esc_html__( 'Saves the country code in lower case. Eg: "es".', 'bbforms' )
                . '<br>name: ' . esc_html__( 'Saves the country name. Eg: "Spain".', 'bbforms' )
                . '<br>name_lower: ' . esc_html__( 'Saves the country name in lower case. Eg: "spain".', 'bbforms' )
                . '<br>'
                . '<br>' . esc_html__( 'Default:', 'bbforms' ) . ' code.'
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'value' => array(
            'codes' => array(
                'value="ES"',
                'value="' . esc_html__( 'Spain', 'bbforms' ) . '"',
                'value="' . esc_html__( 'spain', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'The initial selected option.', 'bbforms' )
                . '<br>' . esc_html__( 'Note: The value needs to match with "save_as" attribute value.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'placeholder' => array(
            'codes' => array(
                'placeholder="' . esc_html__( 'Choose your country', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Adds a disabled option at the start of the options list to work as placeholder.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'multiple' => array(
            'codes' => array(
                'multiple="multiple"',
            ),
            'desc' => esc_html__( 'Allows user to select multiple options.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['quiz'] ) . ' ' . esc_html__( 'Quiz', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[quiz name="your_quiz"]' ) . '<br>';
    $output .= esc_html__( 'Question 1', 'bbforms' ) . '|' . esc_html__( 'Answer 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Question 2', 'bbforms' ) . '|' . esc_html__( 'Answer 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/quiz]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Quiz field picks randomly one of the questions provided and forces to enter the correct answer.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'NOTE: For text answers, quiz will work case-insensitive, which means that answers "Answer 1", "answer 1" or "ANSWER 1" will work.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'RECOMMENDATION: Text questions are more difficult but more secure against bots while maths questions are internationally understood.', 'bbforms' ) . ' ';
    $output .= esc_html__( 'So, if a form is accessed by people who speak different languages, the best would be to use maths questions like 7 + 3.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'name' => array(
            'codes' => array(
                'name="unique_field_name"',
            ),
            'desc' => esc_html__( 'The field name. Needs to be unique in the form.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[quiz]' . '<br>'
                . esc_html__( 'Question 1', 'bbforms' ) . '|' . esc_html__( 'Answer 1', 'bbforms' ) . '<br>'
                . esc_html__( 'Question 2', 'bbforms' ) . '|' . esc_html__( 'Answer 2', 'bbforms' ) . '<br>'
                . '[/quiz]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as the field question/answer pairs.', 'bbforms' )
                . '<br>' . esc_html__( 'Options need to be defined as "Question|Answer".', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-2-section-specials">';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['hidden'] ) . ' ' . esc_html__( 'Hidden', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[hidden name="hidden_642"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'An invisible field that you can use to store any custom information of you choice, using tags or static values since users will not be able to fill it.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $hidden_field_attrs = $common_fields_attrs;
    unset( $hidden_field_attrs['min'] );
    unset( $hidden_field_attrs['max'] );
    unset( $hidden_field_attrs['placeholder'] );
    unset( $hidden_field_attrs['pattern'] );
    $hidden_field_attrs['value']['codes'] = array(
        'value="' . esc_html__( 'Text', 'bbforms' ) . '"',
        'value="{user.id}"',
    );
    $output .= bbforms_attrs_table( $hidden_field_attrs );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['honeypot'] ) . ' ' . esc_html__( 'Honeypot', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[honeypot name="honeypot_643"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'An invisible field used to detect bots since it does not accepts any value. If gets filled, it will return an error (like an email field when receives an invalid email address).', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Since is an invisible field humans will not fill it but bots read your website code trying to fill all fields and commonly they fall in this kind of trick (for that is called honeypot!).', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: %1$s: Option name  %2$s: enable value %3$s: disable value
    $output .= sprintf( esc_html__( 'BBForms adds a honeypot field to all forms with the option %1$s set to %2$s. You can turn it to %3$s to disable this default honeypot (the ones you placed in the form will continue working).', 'bbforms' ),
        '<code>disable_honeypot</code>', '<code>no</code>', '<code>yes</code>' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-2-section-buttons">';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['submit'] ) . ' ' . esc_html__( 'Submit', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[submit value="' . esc_html__( 'Submit', 'bbforms' ) . '"]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'value' => array(
            'codes' => array(
                'value="' . esc_html__( 'Submit', 'bbforms' ) . '"',
                'value="' . esc_html__( 'Send', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Defines the button label.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        )
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $fields_order['reset'] ) . ' ' . esc_html__( 'Reset', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[reset value="' . esc_html__( 'Reset', 'bbforms' ) . '"]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'value' => array(
            'codes' => array(
                'value="' . esc_html__( 'Reset', 'bbforms' ) . '"',
                'value="' . esc_html__( 'Clear', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Defines the button label.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        )
    ) );
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-2-section-add_ons">';

    $output .= $add_ons_output;

    $output .= '</div>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-6">';

    // TODO
    // Attributes

    $output .= esc_html__( 'There are some examples of the most common used attributes.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Limited length text field:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text name="your_text" min="10" max="100"]' ) . '<br>';
    $output .= '<br>';
    // translators: %1$s: attribute %2$s: attribute %3$s attribute %4$s: attribute
    $output .= sprintf( esc_html__( 'NOTE: %1$s and %2$s attributes are also supported, but to make it more easy to remember, we added %3$s and %4$s support to all fields as the standard way to limit the length or value of a field.', 'bbforms' ),
        '<code>minlength</code>', '<code>maxlength</code>', '<code>min</code>', '<code>max</code>' ). '<br>';
    $output .= '<br>';
    // translators: $s: attribute
    $output .= sprintf( esc_html__( 'Text area taller (using %s attribute):', 'bbforms' ),
        '<code>rows</code>' ). '<br>';
    $output .= bbforms_parse_pattern( '[textarea name="your_textarea" rows="10"]' ) . '<br>';
    $output .= '<br>';
    // translators: $s: attribute
    $output .= sprintf( esc_html__( 'Limited length text area (only accepts %s attribute):', 'bbforms' ),
        '<code>max</code>' ). '<br>';
    $output .= bbforms_parse_pattern( '[textarea name="your_textarea" max="300"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Email that only accepts "gmail.com" addresses:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email name="your_email" pattern="^[a-zA-Z0-9]+@gmail\.com$"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Phone that requires the format 555 555 555:', 'bbforms' ) . '<br>';
    $output .= str_replace( 'cm-def', '', bbforms_parse_pattern( '[tel name="your_tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{3}"]' ) ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'URL that only accepts "https://" URLs:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[url name="your_url" pattern="https://.*"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'URL that only accepts URLs from a domain:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[url name="your_url" pattern=".*\.your-site\.com.*"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Limited date:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[date name="your_date" min="2025-01-01" max="2025-12-31"]' ) . '<br>';
    $output .= esc_html__( 'Remember that date field internally works in the format YYYY-MM-DD.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Limited time:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[time name="your_time" min="08:00" max="16:00"]' ) . '<br>';
    $output .= esc_html__( 'Remember that time field internally works in the format 23:59.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Limited file type:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[file name="your_file" accept=".pdf,image/jpeg,video/*"]' ) . '<br>';
    $output .= esc_html__( 'File type needs a comma-separated list of file types allowed.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Accepts: File extensions (.pdf), MIME types (image/jpeg) and wildcard MIME types (video/* which means that all video MIME types are allowed).', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Limited file size:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[file name="your_file" min="1kb" max="10mb"]' ) . '<br>';
    $output .= esc_html__( 'File size supports size suffix (kb, mb, gb & tb) or number to define the size in bytes.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: $s: upload_max_filesize
    $output .= sprintf( esc_html__( 'IMPORTANT: You can not exceed your server maximum upload size (commonly defined in the %s of your PHP configuration).', 'bbforms' ),
        '<code>upload_max_filesize</code>' ). '<br>';
    $output .= '<br>';
    // translators: $s: attribute
    $output .= sprintf( esc_html__( 'File %s attribute:', 'bbforms' ),
            '<code>capture</code>' ). '<br>';
    $output .= bbforms_parse_pattern( '[file name="your_file" capture="user"]' ) . '<br>';
    $output .= esc_html__( 'Mainly for mobiles, specifies which device should capture the media, for images, can be the user-facing camera (user) or the outward-facing camera (environment).', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Accepts: user or environment.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Limited number:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[number name="your_number" min="10" max="100"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Limited decimal number:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[number name="your_number" step="0.1" min="0" max="1"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Decimal range:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[range name="your_range" step="0.1" min="0" max="1"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Select with placeholder:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[select name="your_select" placeholder="' . esc_html__( 'Choose an option', 'bbforms' ) . '"]' ) . '<br>';
    $output .= esc_html__( 'Option 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Option 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/select]' ) . '<br>';
    $output .= esc_html__( 'This will add a disabled option at the start of the options list to work as placeholder.', 'bbforms' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Checkboxes and radios inline', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'By default checkboxes (multiple) and radios are displayed inline:', 'bbforms' ) . '<br>';
    $output .= '<span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 1', 'bbforms' ) . ' <span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 2', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: $1$s: attribute $2$s: value
    $output .= sprintf( esc_html__( 'But if you place the attribute $1$s to $2$s like:', 'bbforms' ),
        '<code>inline</code>', '<code>no</code>'). '<br>';
    $output .= bbforms_parse_pattern( '[radio name="your_radio" inline="no"]' ) . '<br>';
    $output .= esc_html__( 'Option 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Option 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/radio]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Then the field options will be displayed in different lines:', 'bbforms' ) . '<br>';
    $output .= '<span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 1', 'bbforms' ) . '<br>';
    $output .= '<span class="dashicons dashicons-marker"></span> ' . esc_html__( 'Option 2', 'bbforms' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-3">';

    // Label & Description

    $output .= esc_html__( 'The old-fashioned way:', 'bbforms' ) . '<br>';
    $output .= '<span class="bbforms-code"><span class="cm-tag">&lt;label</span> <span class="cm-attribute">for</span><span class="cm-operator">=</span><span class="cm-string">"my_text"</span><span class="cm-tag">&gt;</span>'
        . esc_html__( 'Field', 'bbforms' )
        . '<span class="cm-tag">&lt;/label&gt;</span></span>' . '<br>';
    $output .= bbforms_parse_pattern( '[text name="my_text"]' ) . '<br>';
    $output .= '<span class="bbforms-code"><span class="cm-tag">&lt;p&gt;</span>' . esc_html__( 'Description', 'bbforms' ) . '<span class="cm-tag">&lt;/p&gt;</span></span>' . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'The cool way:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text name="my_text" label="Field" desc="Description"]' ) . '<br>';
    $output .= '<br>';
    // translators: %s: * (asterisk)
    $output .= sprintf( esc_html__( 'If field is required, will appear an %s in the label automatically:', 'bbforms' ), '<span class="bbforms-required">*</span>' ). '<br>';
    $output .= bbforms_parse_pattern( '[text* name="my_text" label="Field" desc="Description"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'In addition, if you provide a label attribute, this label will be used in the submissions view.', 'bbforms' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-4">';

    // HTML Attributes

    $output .= esc_html__( 'You can define common HTML attributes like id, class or style to any BBCode.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text id="my-id" class="my-class-1 my-class-2" style="color: red;"]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'id' => array(
            'codes' => array(
                'id="my-id"',
            ),
            'desc' => esc_html__( 'Defines the element id.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'class' => array(
            'codes' => array(
                'class="my-class"',
                'class="my-class-1 my-class-2"',
            ),
            'desc' => esc_html__( 'Defines the element CSS classes.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'style' => array(
            'codes' => array(
                'style="color: red;"',
                'style="color: red; background: blue;"',
            ),
            'desc' => esc_html__( 'Defines the element inline CSS style.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    ), false );

    $output .= '<br>';
    $output .= esc_html__( 'IMPORTANT: Javascript code is not allowed in attributes for security reasons.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'If you want to add a Javascript functionality to a BBCode, the best would be to give it a custom id or class and add your code in a separate .js file or through a custom code plugin.', 'bbforms' ) . '<br>';
    $output .= '<br>';

    $output .= esc_html__( 'Additionally, all fields have support for some global HTML attributes that all major browsers support.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'autocomplete' => array(
            'codes' => array(
                'autocomplete="on"',
                'autocomplete="off"',
            ),
            'desc' => esc_html__( 'Defines if field can be automatically completed by the browser.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'autocorrect' => array(
            'codes' => array(
                'autocorrect="on"',
                'autocorrect="off"',
            ),
            'desc' => esc_html__( 'Defines if automatic spelling correction and processing of text is enabled while the user is editing this field.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'disabled' => array(
            'codes' => array(
                'disabled="disabled"',
            ),
            'desc' => esc_html__( 'Defines that the user cannot modify the value of the field.', 'bbforms' )
                . ' ' . esc_html__( 'Some browsers prevent the user from clicking or selecting disabled fields.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'readonly' => array(
            'codes' => array(
                'readonly="readonly"',
            ),
            'desc' => esc_html__( 'Defines that the user cannot modify the value of the field.', 'bbforms' )
                . ' ' . esc_html__( 'Read only fields can be clicked or selected by the user.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'spellcheck' => array(
            'codes' => array(
                'spellcheck="true"',
                'spellcheck="false"',
                'spellcheck="default"',
            ),
            'desc' => esc_html__( 'Defines if field is subject to spell-checking by the underlying browser or Operating System (OS).', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'title' => array(
            'codes' => array(
                'title="' . esc_html__( 'Text', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Defines a text representing advisory information related to the field.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    ), false );

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-fields-help-content-5">';

    // Editor Tricks

    $output .= esc_html__( 'The editor is designed to help you build forms faster. There are some of the functionalities included:', 'bbforms' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Field buttons', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Clicking a field button will place a basic BBCode with an auto-generated "name" like:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text name="text_123" value=""]' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Arrow at side of a button', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Clicking the arrow at side of a field button will show you a list of common field patterns like:', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= '1. ' . esc_html__( 'A limited number field:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[number name="number_123" min="0" max="100"]' ) . '<br>';
    $output .= '<br>';
    $output .= '2. ' . esc_html__( 'An email field autofilled with the user email:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email name="email_123" value="{user.email}"]' ) . '<br>';
    $output .= '<br>';
    $output .= '3. ' . esc_html__( 'A phone field that only accepts numbers like 555-555-5555:', 'bbforms' ) . '<br>';
    $output .= str_replace( 'cm-def', '', bbforms_parse_pattern( '[tel name="tel_123" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"]' ) ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Mouse selection matters', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'You can select content in the editor with your mouse and on clicking a button, the editor will place it in the best position.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example, you have the content:', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 2', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 3', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: %s: BBCode
    $output .= sprintf( esc_html__( 'If you select those lines with your mouse and you click the %s field button, the selected content will be placed in the field options like:', 'bbforms' ),
        '<code>[radio]</code>' ). '<br>';
    $output .= bbforms_parse_pattern( '[radio name="radio_123"]' ) . '<br>';
    $output .= esc_html__( 'Line 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 2', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 3', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/radio]' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';

    $output .= '</div>';

    return $output;

}

/**
 * Actions help
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_actions_help() {

    $output = '';

    $actions_sections_labels = bbforms_editor_controls_get_actions_sections_labels();
    $actions_order_original  = bbforms_editor_controls_get_actions_order();
    $actions_order = array();

    foreach( $actions_order_original as $section => $actions ) {
        $actions_order = array_merge( $actions_order, $actions );
    }

    $output .= '<div class="bbforms-dialog-tabs">';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-1 bbforms-dialog-tab-active" data-toggle=".bbforms-actions-help-content-1">';
            $output .= esc_html__( 'Getting Started', 'bbforms' );
        $output .= '</div>';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-2" data-toggle=".bbforms-actions-help-content-2">';
            $output .= esc_html__( 'Actions List', 'bbforms' );
        $output .= '</div>';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-3" data-toggle=".bbforms-actions-help-content-3">';
            $output .= esc_html__( 'Success & Error Messages', 'bbforms' );
        $output .= '</div>';
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-4" data-toggle=".bbforms-actions-help-content-4">';
            $output .= esc_html__( 'Editor Tricks', 'bbforms' );
        $output .= '</div>';
    $output .= '</div>';

    $output .= '<div class="bbforms-help bbforms-actions-help cm-s-default">';

    $output .= '<div class="bbforms-dialog-tab-content bbforms-actions-help-content-1 bbforms-dialog-tab-content-active">';

    // Getting Started

    $output .= esc_html__( 'You can configure the actions of your choice like record the submission, send an email or redirect the form submitter to another URL.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'These actions are processed only when the form gets successfully submitted, which means that all form fields have been filled correctly.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'In the actions editor, you can place all the actions of your choice.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example placing the record and message actions:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[record]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[message type="success"]' . esc_html__( 'Form submitted successfully!', 'bbforms' ) . '[/message]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Even you can repeat the same action for different purposes.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: %s: attribute
    $output .= sprintf( esc_html__( 'Example sending an email to site admin and another email to form submitter (using tags for the %s attribute):', 'bbforms' ),
        '<code>to</code>' ). '<br>';
    $output .= bbforms_parse_pattern( '[email to="{site.admin_email}"]' . esc_html__( 'New submission!', 'bbforms' ) . '[/email]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email to="{field.email_123}"]' . esc_html__( 'Submission received!', 'bbforms' ) . '[/email]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'EXTRA: To meet with GDPR and other privacy laws, you have actions to register data export and deletion requests:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[export_request email="{field.email_123}"]' . esc_html__( 'Export request registered!', 'bbforms' ) . '[/export_request]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[delete_request email="{field.email_123}"]' . esc_html__( 'Deletion request registered!', 'bbforms' ) . '[/delete_request]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Check the "Actions List" tab to meet all of them!', 'bbforms' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-actions-help-content-2">';

    // Actions List
    $actions_sections_outputs = array();

    $output .= '<div class="bbforms-dialog-tabs">';
    foreach( $actions_sections_labels as $i => $action_section ) {

        if( ! in_array( $i, array( 'actions', 'personal_data' ) ) ) {
            $section_output = apply_filters( "bbforms_actions_help_actions_list_{$i}_section_content", '', $actions_order );

            if( $section_output === '' ) {
                continue;
            }

            $actions_sections_outputs[$i] = $section_output;
        }

        $active = ( $i === 'actions' ? 'bbforms-dialog-tab-active' : '' );
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-help-content-' . esc_attr( $i ) . ' ' . $active . '" data-toggle=".bbforms-actions-help-content-2-section-' . esc_attr( $i ) . '">';
        $output .= bbforms_dashicon( $action_section['icon'] ) . ' ' . esc_html( $action_section['label'] );
        $output .= '</div>';
    }
    $output .= '</div>';

    $output .= '<div class="bbforms-dialog-tab-content bbforms-actions-help-content-2-section-actions bbforms-dialog-tab-content-active">';

    $output .= '<h3>' . bbforms_dashicon( $actions_order['record'] ) . ' ' . esc_html__( 'Record submission', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[record]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Action to record the form submission.', 'bbforms' ) . '<br>';

    $output .= '<h3>' . bbforms_dashicon( $actions_order['email'] ) . ' ' . esc_html__( 'Send email', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[email from="" from_name="" to="" cc="" bcc="" reply_to="" subject=""]' . esc_html__( 'Email Body', 'bbforms' ) . '[/email]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Action to send an email.', 'bbforms' ) . ' ' . esc_html__( 'You can place this action multiple times to send multiples emails, for example, send a confirmation email to the form submitter and a notification email to the site admin.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'from' => array(
            'codes' => array(
                'from="contact@bbforms.com"',
                'from="{field.email_123}"',
                'from="{site.admin_email}"',
            ),
            'desc' => esc_html__( 'From email.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'from_name' => array(
            'codes' => array(
                'from_name="BBForms"',
                'from_name="{field.text_123}"',
                'from_name="{site.name}"',
            ),
            'desc' => esc_html__( 'From name.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'to' => array(
            'codes' => array(
                'to="contact@bbforms.com"',
                'to="email_1@bbforms.com,email_2@bbforms.com"',
                'to="{field.email_123}"',
            ),
            'desc' => esc_html__( 'Comma-separated list of email addresses to send the email.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'cc' => array(
            'codes' => array(
                'cc="contact@bbforms.com"',
                'cc="email_1@bbforms.com,email_2@bbforms.com"',
                'cc="{field.email_123}"',
            ),
            'desc' => esc_html__( 'Comma-separated list of email addresses to be used for carbon copy.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'bcc' => array(
            'codes' => array(
                'bcc="contact@bbforms.com"',
                'bcc="email_1@bbforms.com,email_2@bbforms.com"',
                'bcc="{field.email_123}"',
            ),
            'desc' => esc_html__( 'Comma-separated list of email addresses to be used for blink carbon copy.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'reply_to' => array(
            'codes' => array(
                'reply_to="contact@bbforms.com"',
                'reply_to="{field.email_123}"',
                'reply_to="{site.admin_email}"',
            ),
            'desc' => esc_html__( 'Email address to be used for when replying to this email.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'subject' => array(
            'codes' => array(
                'subject="' . esc_html__( 'New Submission', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'The email subject.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'attachments' => array(
            'codes' => array(
                'attachments="PATH/file.png"',
                'attachments="PATH/file_1.png,PATH/file_2.pdf"',
                'attachments="{field.file_123}"',
                'attachments="{field.file_1},{field.file_2}"',
            ),
            'desc' => esc_html__( 'Comma-separated list of file paths to attach.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[email]' . esc_html__( 'Email Body', 'bbforms' ) . '[/email]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as the email body.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $actions_order['redirect'] ) . ' ' . esc_html__( 'Redirect', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[redirect to=""]' . esc_html__( 'Redirect Message', 'bbforms' ) . '[/redirect]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Action to redirect the form submitter to another URL.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'to' => array(
            'codes' => array(
                'to="bbforms.com"',
                'to="https://bbforms.com"',
                'to="{site.url}"',
            ),
            'desc' => esc_html__( 'The URL to redirect.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[redirect]' . esc_html__( 'Redirecting...', 'bbforms' ) . '[/redirect]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as message while perform the redirection.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $actions_order['message'] ) . ' ' . esc_html__( 'Message', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[message type=""]' . esc_html__( 'Message', 'bbforms' ) . '[/message]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Action to display a message after submit the form.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'type' => array(
            'codes' => array(
                'type="info"',
                'type="success"',
                'type="warning"',
                'type="error"',
                'type="none"',
            ),
            'desc' => esc_html__( 'The message type.', 'bbforms' )
        . '<br>' . esc_html__( 'Accepts: info, success, warning, error and none.', 'bbforms' )
        . '<br>' . esc_html__( 'Default:', 'bbforms' ) . 'info.'
        . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[redirect]' . esc_html__( 'Redirecting...', 'bbforms' ) . '[/redirect]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as the message content.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-actions-help-content-2-section-personal_data">';

    $output .= '<h3>' . bbforms_dashicon( $actions_order['export_request'] ) . ' ' . esc_html__( 'Export Data Request', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[export_request email="" duplicated_message=""]' . esc_html__( 'Success Message', 'bbforms' ) . '[/export_request]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Action to register an email address in the WordPress export data tool.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'email' => array(
            'codes' => array(
                'email="contact@bbforms.com"',
                'email="{field.email_123}"',
            ),
            'desc' => esc_html__( 'The email address to register the request.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'duplicated_message' => array(
            'codes' => array(
                'duplicated_message="' . esc_html__( 'A request for this email address already exists.', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Message to be displayed in case there is another request for this email.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[export_request]' . esc_html__( 'Success Message', 'bbforms' ) . '[/export_request]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as a success message.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '<h3>' . bbforms_dashicon( $actions_order['delete_request'] ) . ' ' . esc_html__( 'Delete Data Request', 'bbforms' ) . '</h3>';
    $output .= bbforms_parse_pattern( '[delete_request email="" anonymize="" duplicated_message=""]' . esc_html__( 'Success Message', 'bbforms' ) . '[/delete_request]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Action to register an email address in the WordPress deletion data tool.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'email' => array(
            'codes' => array(
                'email="contact@bbforms.com"',
                'email="{field.email_123}"',
            ),
            'desc' => esc_html__( 'The email address to register the request.', 'bbforms' )
                . '<br>' . esc_html__( 'Required.', 'bbforms' ),
        ),
        'anonymize' => array(
            'codes' => array(
                'anonymize="no"',
                'anonymize="yes"',
            ),
            'desc' => esc_html__( 'Decides if the submission data for this request should be anonymized or deleted.', 'bbforms' )
                . '<br>' . esc_html__( 'Default:', 'bbforms' ) . ' no.'
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'duplicated_message' => array(
            'codes' => array(
                'duplicated_message="' . esc_html__( 'A request for this email address already exists.', 'bbforms' ) . '"',
            ),
            'desc' => esc_html__( 'Message to be displayed in case there is another request for this email.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        strtoupper( sanitize_key( esc_html__( 'CONTENT', 'bbforms' ) ) ) => array(
            'codes' => array(
                '[export_request]' . esc_html__( 'Success Message', 'bbforms' ) . '[/export_request]',
            ),
            'desc' => esc_html__( 'The content inside the BBCode will be used as a success message.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    ) );
    $output .= '<br>';

    $output .= '</div>';

    foreach( $actions_sections_outputs as $i => $action_section_output ) {
        if( $action_section_output === '' ) {
            continue;
        }

        $output .= '<div class="bbforms-dialog-tab-content bbforms-actions-help-content-2-section-' . esc_attr( $i ) . '">';

        $output .= $action_section_output;

        $output .= '</div>';

    }

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-actions-help-content-3">';

    // Success & Error Messages

    // translators: %1$s: attribute %2$s: attribute
    $output .= sprintf( esc_html__( 'All actions have support to %1$s and %2$s attributes as a short way to display a message after the form submission.', 'bbforms' ),
         '<code>success_message</code>', '<code>error_message</code>' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Long way:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email to="{site.admin_email}"][/email]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[message type="success"]' . esc_html__( 'Email sent to site admin!', 'bbforms' ) . '[/message]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Short way:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email to="{site.admin_email}" success_message="' . esc_html__( 'Email sent to site admin!', 'bbforms' ) . '"][/email]' ) . '<br>';
    $output .= '<br>';
    // translators: %1$s: attribute %2$s: attribute
    $output .= sprintf( esc_html__( 'In addition, you can use %1$s and %2$s to confirm to the form submitter if the action was processed successfully.', 'bbforms' ),
            '<code>success_message</code>', '<code>error_message</code>' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Messages about the send email action result:', 'bbforms' ) . '<br>';
    // translators: %s: email address
    $output .= bbforms_parse_pattern( '[email to="{field.email_123}" success_message="' . sprintf( esc_html__( 'Email sent to %s', 'bbforms' ), '{field.email_123}' ) . '" error_message="' . sprintf( esc_html__( 'Failed to send email to %s', 'bbforms' ), '{field.email_123}' ) . '"][/email]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Messages about the export data request action result:', 'bbforms' ) . '<br>';
    // translators: %s: email address
    $output .= bbforms_parse_pattern( '[export_request email="{field.email_123}" success_message="' . sprintf( esc_html__( 'Request for %s registered successfully', 'bbforms' ), '{field.email_123}' ) . '" error_message="' . sprintf( esc_html__( 'The email %s is not registered in our database', 'bbforms' ), '{field.email_123}' ) . '"][/export_request]' ) . '<br>';
    $output .= '<br>';
    // translators: %1$s: bbcode %2$s: bbcode %3$s: attribute
    $output .= sprintf( esc_html__( 'Remember that %1$s and %2$s actions have support for the %3$s attribute to display a message in case there is another request for that email address.', 'bbforms' ),
        '<code>[export_request]</code>', '<code>[delete_request]</code>', '<code>duplicated_message</code>' ) . '<br>';


    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-actions-help-content-4">';

    // Editor Tricks

    $output .= esc_html__( 'The editor is designed to help you setup the form actions faster. There are some of the functionalities included:', 'bbforms' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Action buttons', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Clicking an action button will place a basic BBCode of the action like:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email to="" subject=""][/email]' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Arrow at side of a button', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Clicking the arrow at side of an action button will show you a list of common action patterns like:', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= '1. ' . esc_html__( 'Email site admin about a form submission:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email to="{site.admin_email}" reply_to="{field.email_123}" subject="{site.name} - ' . esc_html__( 'New Submission', 'bbforms' ) . '"]{fields_table}[/email]' ) . '<br>';
    $output .= '<br>';
    $output .= '2. ' . esc_html__( 'Redirect form submitter to homepage:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[redirect to="{site.url}"]' . esc_html__( 'Redirecting...', 'bbforms' ) . '[/redirect]' ) . '<br>';
    $output .= '<br>';
    $output .= '3. ' . esc_html__( 'Export data request that notifies if there is a duplicated request:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[export_request email="" error_message="' . esc_html__( 'Invalid email address.', 'bbforms' ) . '" duplicated_message="' . esc_html__( 'A request for this email address already exists.', 'bbforms' ) . '"][/export_request]' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Mouse selection matters', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'You can select content in the editor with your mouse and on clicking a button, the editor will place it in the best position.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example, you have the content:', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 2', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 3', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: %s: BBCode
    $output .= sprintf( esc_html__( 'If you select those lines with your mouse and you click the %s action button, the selected content will be placed as the email body like:', 'bbforms' ) ,
        '<code>[email]</code>' ). '<br>';
    $output .= bbforms_parse_pattern( '[email to="" reply_to="" subject=""]' ) . '<br>';
    $output .= esc_html__( 'Line 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 2', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 3', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/email]' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';

    $output .= '</div>';

    return $output;

}

/**
 * BBCodes help
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_bbcodes_help() {

    $user_id = get_current_user_id();

    if( $user_id !== 0 ) {
        $user = get_userdata( $user_id );
        $user_name = ( ! empty( $user->first_name ) ) ? $user->first_name : $user->user_login;
    } else {
        $user_name = 'bbforms';
    }

    $bbcodes_sections_labels = bbforms_editor_controls_get_bbcodes_sections_labels();
    $bbcodes_order_original  = bbforms_editor_controls_get_bbcodes_order();
    $bbcodes_order = array();

    foreach( $bbcodes_order_original as $section => $bbcodes ) {
        $bbcodes_order = array_merge( $bbcodes_order, $bbcodes );
    }

    $output = '';

    $output .= '<div class="bbforms-dialog-tabs">';
    $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-1 bbforms-dialog-tab-active" data-toggle=".bbforms-bbcodes-help-content-1">';
        $output .= esc_html__( 'Getting Started', 'bbforms' );
    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-2" data-toggle=".bbforms-bbcodes-help-content-2">';
        $output .= esc_html__( 'BBCodes List', 'bbforms' );
    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-3" data-toggle=".bbforms-bbcodes-help-content-3">';
        $output .= esc_html__( 'HTML Attributes', 'bbforms' );
    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-4" data-toggle=".bbforms-bbcodes-help-content-4">';
        $output .= esc_html__( 'Editor Tricks', 'bbforms' );
    $output .= '</div>';
    $output .= '</div>';

    $output .= '<div class="bbforms-help bbforms-bbcodes-help cm-s-default">';

    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-1 bbforms-dialog-tab-content-active">';

    // Getting Started

    $output .= esc_html__( 'BBCodes helps you to format the content in a easy way.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'You can bold text:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( esc_html__( 'Hi', 'bbforms' ) . ' ' . '[b]' . $user_name . '[/b]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Align it:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( esc_html__( 'Hi', 'bbforms' ) . ' ' . '[right]' . $user_name . '[/right]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Combine multiples BBCodes:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[font="Arial"][size="16"]' . esc_html__( 'Hi', 'bbforms' ) . ' [b]' . $user_name . '[/b][/size][/font]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Use them on fields and actions attributes:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[text name="text_123" desc="' . esc_html__( 'Hi', 'bbforms' ) . ' [b]' . $user_name . '[/b]"][/email]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Even use them on fields and actions content.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example placing BBCodes in a field options:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[check name="check_123"]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[b]' . esc_html__( 'Option 1', 'bbforms' ) . '[/b]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[u]' . esc_html__( 'Option 2', 'bbforms' ) . '[/u]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/check]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example placing BBCodes in an action content:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email to=""]' . esc_html__( 'Hi', 'bbforms' ) . ' [b]' . $user_name . '[/b][/email]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'EXTRA: BBCodes also support HTML attributes:', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Hi', 'bbforms' ) . ' ' . bbforms_parse_pattern( '[b id="my_id" class="my_class" style="color: red;"]' . $user_name . '[/b]' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-2">';

    // BBCodes List

    $add_ons_output = apply_filters( 'bbforms_bbcodes_help_bbcodes_list_add_ons_section_content', '', $bbcodes_order );

    $output .= '<div class="bbforms-dialog-tabs">';
    foreach( $bbcodes_sections_labels as $i => $bbcode_section ) {
        if( $i === 'add_ons' && $add_ons_output === '' ) {
            continue;
        }

        $active = ( $i === 'layout' ? 'bbforms-dialog-tab-active' : '' );
        $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-help-content-' . esc_attr( $i ) . ' ' . $active . '" data-toggle=".bbforms-bbcodes-help-content-2-section-' . esc_attr( $i ) . '">';
        $output .= bbforms_dashicon( $bbcode_section['icon'] ) . ' ' . esc_html( $bbcode_section['label'] );
        $output .= '</div>';
    }
    $output .= '</div>';

    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-2-section-layout bbforms-dialog-tab-content-active">';

    // Columns & Tables

    $output .= '<h3>' . bbforms_dashicon( $bbcodes_order['row'] ) . ' ' . esc_html__( 'Columns', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Columns are the perfect way to divide the content in different sections in the same line:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[row]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . esc_html__( 'Column 1', 'bbforms' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . esc_html__( 'Column 2', 'bbforms' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . esc_html__( 'Column 3', 'bbforms' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/row]' ) . '<br>';
    $output .= '<br>';
    // translators: %1$s: BBCode %2$s: BBCode
    $output .= sprintf( esc_html__( 'IMPORTANT: %1$s always needs to be placed inside a %2$s.', 'bbforms' ),
        '<code>[column]</code>', '<code>[row]</code>' ). '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'They have a responsive design which means that previous example will show 3 columns in large screens (desktop, tablets, etc) but on small screens (commonly mobile) the columns will fit the entire screen width and they will look like 3 rows instead.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Of course, they are perfect for fields:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[row]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[text name="text_123"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[email name="email_123"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tel name="tel_123"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/row]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Even you can control the width of each individual column:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[row]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column="30%"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[text name="text_123"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column="50%"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[email name="email_123"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column="20%"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tel name="tel_123"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/column]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/row]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'You can define the columns of your choice:', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( '2 columns:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[row]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/row]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( '5 columns:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[row]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/row]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'The BBCode supports unlimited columns but you may need to think on visibility while placing a large number of them (on small screens them will work like rows, so no problem!).', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'IMPORTANT: Do not use columns on an email body, the best for compatibility is to use a table without border.', 'bbforms' ) . '<br>';

    $output .= '<h3>' . bbforms_dashicon( $bbcodes_order['table'] ) . ' '  . esc_html__( 'Tables', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tables lets you divide your content in columns in a static way:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[table]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 1', 'bbforms' ) . ' ' . esc_html__( 'Column 1', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 1', 'bbforms' ) . ' ' . esc_html__( 'Column 2', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 2', 'bbforms' ) . ' ' . esc_html__( 'Column 1', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 2', 'bbforms' ) . ' ' . esc_html__( 'Column 2', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/tr]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/table]' ) . '<br>';
    $output .= '<br>';
    // translators: %1$s: BBCode %2$s: BBCode %3$s: BBCode
    $output .= sprintf( esc_html__( 'IMPORTANT: You need to meet the structure of %1$s to wrap the entire table, %2$s to wrap the row and %3$s to wrap the row column.', 'bbforms' ),
            '<code>[table]</code>', '<code>[tr]</code>', '<code>[td]</code>' ). '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'They have a static design which means columns will be respected on any screen size.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Tables includes support for some attributes (other HTML attributes like id, class or style are also supported):', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[table align="right" border="2px solid" width="90%"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 1', 'bbforms' ) . ' ' . esc_html__( 'Column 1', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 1', 'bbforms' ) . ' ' . esc_html__( 'Column 2', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 2', 'bbforms' ) . ' ' . esc_html__( 'Column 1', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 2', 'bbforms' ) . ' ' . esc_html__( 'Column 2', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/tr]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/table]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Even you can define the width of each individual column:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[table align="right" border="2px solid" width="90%"]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 1', 'bbforms' ) . ' ' . esc_html__( 'Column 1', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td="30%"]' . esc_html__( 'Row 1', 'bbforms' ) . ' ' . esc_html__( 'Column 2', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[tr]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td]' . esc_html__( 'Row 2', 'bbforms' ) . ' ' . esc_html__( 'Column 1', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[td="30%"]' . esc_html__( 'Row 2', 'bbforms' ) . ' ' . esc_html__( 'Column 2', 'bbforms' ) . '[/td]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[/tr]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/table]' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-2-section-alignment">';

    $output .= '<h3>' . bbforms_dashicon( $bbcodes_order['left'] ) . ' ' . esc_html__( 'Alignment', 'bbforms' ) . '</h3>';

    $output .= bbforms_dashicon( $bbcodes_order['left'] ) . ' ' . esc_html__( 'Left:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[left]' . esc_html__( 'Text', 'bbforms' ) . '[/left]' ) . '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['center'] ) . ' ' . esc_html__( 'Center:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[center]' . esc_html__( 'Text', 'bbforms' ) . '[/center]' ) . '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['right'] ) . ' ' . esc_html__( 'Right:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[right]' . esc_html__( 'Text', 'bbforms' ) . '[/right]' ) . '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['justify'] ) . ' ' . esc_html__( 'Justify:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[justify]' . esc_html__( 'Text', 'bbforms' ) . '[/justify]' ) . '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-2-section-decoration">';

    $output .= '<h3>' . bbforms_dashicon( $bbcodes_order['b'] ) . ' ' . esc_html__( 'Text decoration', 'bbforms' ) . '</h3>';

    $output .= bbforms_dashicon( $bbcodes_order['b'] ) . ' ' . esc_html__( 'Bold:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[b]' . esc_html__( 'Text', 'bbforms' ) . '[/b]' ) . '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['i'] ) . ' ' . esc_html__( 'Italic:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[i]' . esc_html__( 'Text', 'bbforms' ) . '[/i]' ) . '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['u'] ) . ' ' .esc_html__( 'Underlined:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[u]' . esc_html__( 'Text', 'bbforms' ) . '[/u]' ) . '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['s'] ) . ' ' . esc_html__( 'Strikethrough:', 'bbforms' ) . ' ';
    $output .= bbforms_parse_pattern( '[s]' . esc_html__( 'Text', 'bbforms' ) . '[/s]' ) . '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-2-section-formatting">';

    $output .= '<h3>' . bbforms_dashicon( $bbcodes_order['color'] ) . ' ' . esc_html__( 'Text formatting', 'bbforms' ) . '</h3>';

    $output .= bbforms_dashicon( $bbcodes_order['font'] ) . ' ' . esc_html__( 'Font:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[font="Arial"]' . esc_html__( 'Text', 'bbforms' ) . '[/font]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[font="Georgia"]' . esc_html__( 'Text', 'bbforms' ) . '[/font]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['size'] ) . ' ' . esc_html__( 'Size:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[size="14"]' . esc_html__( 'Text', 'bbforms' ) . '[/size]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[size="24"]' . esc_html__( 'Text', 'bbforms' ) . '[/size]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['color'] ) . ' ' . esc_html__( 'Color:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[color="red"]' . esc_html__( 'Text', 'bbforms' ) . '[/color]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[color="#FF0000"]' . esc_html__( 'Text', 'bbforms' ) . '[/color]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['quote'] ) . ' ' . esc_html__( 'Quote:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[quote]' . esc_html__( 'Text', 'bbforms' ) . '[/quote]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[quote="' . esc_html__( 'Author says:', 'bbforms' ) . '"]' . esc_html__( 'Text', 'bbforms' ) . '[/quote]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['list'] ) . ' ' . esc_html__( 'List:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[list]' ) . '<br>';
    $output .= '<span class="cm-tag">[*]</span> ' . esc_html__( 'Item 1', 'bbforms' ) . '<br>';
    $output .= '<span class="cm-tag">[*]</span> ' . esc_html__( 'Item 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/list]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Ordered List (accepts 0, 1, a, A, i, I):', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[list="A"]' ) . '<br>';
    $output .= '<span class="cm-tag">[*]</span> ' . esc_html__( 'Item 1', 'bbforms' ) . '<br>';
    $output .= '<span class="cm-tag">[*]</span> ' . esc_html__( 'Item 2', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/list]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['code'] ) . ' ' . esc_html__( 'Code: ', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[code]var bbforms = "rocks";[/code]' ) . '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-2-section-embed">';

    $output .= '<h3>' . bbforms_dashicon( 'bbforms-submit' ) . ' ' . esc_html__( 'Embeds', 'bbforms' ) . '</h3>';

    $output .= bbforms_dashicon( $bbcodes_order['email'] ) . ' ' . esc_html__( 'Email:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email]contact@bbforms.com[/email]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email="contact@bbforms.com"]' . esc_html__( 'Contact us!', 'bbforms' ) . '[/email]' ) . '<br>';
    $output .= esc_html__( 'Displays a clickable mailto link.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['iframe'] ) . ' ' . esc_html__( 'Iframe:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[iframe src="https://maps.google.com/maps?q=Spain" width="600" height="450"]' ) . '<br>';
    $output .= esc_html__( 'Embeds an iframe.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['img'] ) . ' ' . esc_html__( 'Image:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[img]https://ps.w.org/bbforms/assets/icon-128x128.png[/img]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[img="https://ps.w.org/bbforms/assets/icon-128x128.png"]' . esc_html__( 'Alt Text', 'bbforms' ) . '[/img]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['url'] ) . ' ' . esc_html__( 'URL:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[url]https://bbforms.com/[/url]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[url="https://bbforms.com/"]' . esc_html__( 'Visit our website!', 'bbforms' ) . '[/url]' ) . '<br>';
    $output .= esc_html__( 'Displays a clickable link.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_dashicon( $bbcodes_order['youtube'] ) . ' ' . esc_html__( 'Youtube:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[youtube]https://youtu.be/8CcRMWx9EtA[/youtube]' ) . '<br>';
    $output .= esc_html__( 'Embeds a youtube video.', 'bbforms' ) . '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-2-section-add_ons">';

    $output .= $add_ons_output;

    $output .= '</div>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-3">';

    // HTML Attributes

    $output .= esc_html__( 'You can define common HTML attributes like id, class or style to any BBCode.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[b id="my-id" class="my-class-1 my-class-2" style="color: red;"]' . esc_html__( 'Text', 'bbforms' ) . '[/b]' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_attrs_table( array(
        'id' => array(
            'codes' => array(
                'id="my-id"',
            ),
            'desc' => esc_html__( 'Defines the element id.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'class' => array(
            'codes' => array(
                'class="my-class"',
                'class="my-class-1 my-class-2"',
            ),
            'desc' => esc_html__( 'Defines the element CSS classes.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
        'style' => array(
            'codes' => array(
                'style="color: red;"',
                'style="color: red; background: blue;"',
            ),
            'desc' => esc_html__( 'Defines the element inline CSS style.', 'bbforms' )
                . '<br>' . esc_html__( 'Optional.', 'bbforms' ),
        ),
    ), false );

    $output .= '<br>';
    $output .= esc_html__( 'IMPORTANT: Javascript code is not allowed in attributes for security reasons.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'If you want to add a Javascript functionality to a BBCode, the best would be to give it a custom id or class and add your code in a separate .js file or through a custom code plugin.', 'bbforms' ) . '<br>';


    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-bbcodes-help-content-4">';

    // Editor Tricks

    $output .= esc_html__( 'The editor is designed to help you place BBCodes faster. There are some of the functionalities included:', 'bbforms' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'BBCodes buttons', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Clicking a BBCode button will place a basic BBCode of the action like:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[row]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;" . bbforms_parse_pattern( '[column][/column]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/row]' ) . '<br>';

    $output .= '<h3>' . esc_html__( 'Mouse selection matters', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'You can select content in the editor with your mouse and on clicking a button, the editor will place it in the best position.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Example, you have the content:', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 1', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 2', 'bbforms' ) . '<br>';
    $output .= esc_html__( 'Line 3', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: %s: BBCode
    $output .= sprintf( esc_html__( 'If you select those lines with your mouse and you click the %s BBCode button, the selected content will be placed as the list items like:', 'bbforms' ),
        '<code>[list]</code>' ). '<br>';
    $output .= bbforms_parse_pattern( '[list]' ) . '<br>';
    $output .= '<span class="cm-tag">[*]</span> ' .esc_html__( 'Line 1', 'bbforms' ) . '<br>';
    $output .= '<span class="cm-tag">[*]</span> ' .esc_html__( 'Line 2', 'bbforms' ) . '<br>';
    $output .= '<span class="cm-tag">[*]</span> ' .esc_html__( 'Line 3', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[/list]' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';

    $output .= '</div>';

    return $output;

}

/**
 * Tags help
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_tags_help() {

    $user_id = get_current_user_id();

    if( $user_id !== 0 ) {
        $user = get_userdata( $user_id );
        $user_name = ( ! empty( $user->first_name ) ) ? $user->first_name : $user->user_login;
        $user_email = $user->user_email;
    } else {
        $user_name = 'bbforms';
        $user_email = 'contact@bbforms.com';
    }

    $tags = bbforms_get_tags();

    $output = '';

    $output .= '<div class="bbforms-dialog-tabs">';
    $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-1 bbforms-dialog-tab-active" data-toggle=".bbforms-tags-help-content-1">';
    $output .= esc_html__( 'Getting Started', 'bbforms' );
    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab bbforms-dialog-tab-2" data-toggle=".bbforms-tags-help-content-2">';
    $output .= esc_html__( 'Tags List', 'bbforms' );
    $output .= '</div>';
    $output .= '</div>';

    $output .= '<div class="bbforms-help bbforms-tags-help cm-s-default">';

    $output .= '<div class="bbforms-dialog-tab-content bbforms-tags-help-content-1 bbforms-dialog-tab-content-active">';

    // Getting Started


    $output .= esc_html__( 'Tags helps you to place dynamic information on your form.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: %1$s: tag code %2$s: email address
    $output .= sprintf( esc_html__( 'For example the tag %1$s will render the current user email that in your case is %2$s. If another user access to the form, they will see their email instead, making the tag to work dynamically for each user.', 'bbforms' ),
            '<span class="cm-def">{user.email}</span>', '<code>' . $user_email . '</code>' ). '<br>';
    $output .= '<br>';
    // translators: %1$s: [] %2$s: {} %3$s: tag code
    $output .= sprintf( esc_html__( 'While BBCodes are wrapped by %1$s, tags are wrapped by %2$s and they are colored in the editor like %3$s to be easily identificable.', 'bbforms' ),
        '<code>[]</code>', '<code>{}</code>', '<span class="cm-def">{user.email}</span>' ). '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'You can place them in HTML:', 'bbforms' ) . '<br>';
    $output .= '<span class="cm-tag">&lt;h3&gt;</span>' . esc_html__( 'Hi', 'bbforms' ) . ' <span class="cm-def">{user.first_name}</span>' . '<span class="cm-tag">&lt;/h3&gt;</span>' . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'On fields to autofill their value:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[url name="url_123" value="{site.url}"]' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email name="email_123" value="{user.email}"]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'On any field or BBCode attribute:', 'bbforms' ) . '<br>';
    // translators: %s: tag code
    $output .= bbforms_parse_pattern( '[check* name="check_123" label="' . sprintf( esc_html__( 'Required to continue using %s', 'bbforms' ), '{site.name}' ) . '"]' ) . '<br>';
    // translators: %s: tag code
    $output .= bbforms_parse_pattern( sprintf( esc_html__( 'I have read and agree with your %s.', 'bbforms' ), '[url="{site.privacy_policy_url}"]' . esc_html__( 'Privacy Policy', 'bbforms' ) . '[/url]' ) ) . '<br>';
    $output .= bbforms_parse_pattern( '[/check]' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Even on actions:', 'bbforms' ) . '<br>';
    $output .= bbforms_parse_pattern( '[email to="{site.admin_email}"]' ) . '<br>';
    // translators: %s: tag code
    $output .= sprintf( esc_html__( 'User %s has filled the form!', 'bbforms' ),
        '<span class="cm-def">{user.first_name}</span>' ) . '<br>';
    $output .= esc_html__( 'Here are the values entered:', 'bbforms' ) . '<br>';
    $output .= '<span class="cm-def">{fields_table}</span>' . '<br>';
    $output .= bbforms_parse_pattern( '[/email]' ) . '<br>';
    $output .= '<br>';

    $output .= '</div>';
    $output .= '<div class="bbforms-dialog-tab-content bbforms-tags-help-content-2">';

    // Tags List

    $output .= '<h3 style="margin-top: 0;">' . esc_html__( 'Fields tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'IMPORTANT: Only available on actions.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= esc_html__( 'Tags to display the values of the form. Every time you add a new field to your form, a new tag will appear in this group.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    // translators: %s tag
    $output .= sprintf( esc_html__( 'Includes the %s tag which lets you easily add a HTML table with all fields submitted.', 'bbforms' ),
        '<span class="cm-def">{fields_table}</span>' ). '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['fields']['tags'] );

    $output .= '<h3>' . esc_html__( 'Site tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tags to display information about your site.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['site']['tags'] );

    $output .= '<h3>' . esc_html__( 'User tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tags to display information about logged in user. If the user is not logged in, all tags will display an empty string.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['user']['tags'] );

    $output .= '<h3>' . esc_html__( 'URL tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tags to display information form the URL.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['url']['tags'] );

    $output .= '<h3>' . esc_html__( 'Datetime tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tags to display information about logged in user. If the user is not logged in, all tags will display an empty string.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['date']['tags'] );

    $output .= '<h3>' . esc_html__( 'Settings tags', 'bbforms' ) . '</h3>';

    $output .= esc_html__( 'Tags to display the value of a BBForms setting. Designed specifically to let you set global form messages to use them in the form options.', 'bbforms' ) . '<br>';
    $output .= '<br>';
    $output .= bbforms_tags_table( $tags['settings']['tags'] );

    $output = apply_filters( 'bbforms_tags_help_tags_list_content', $output );

    $output .= '</div>';

    $output .= '</div>';

    return $output;

}

/**
 * Options help
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_options_help() {

    $output = '';

    $options = bbforms_get_options();
    $defaults = bbforms_get_options_defaults();

    $output .= '<div class="bbforms-help bbforms-options-help">';

    foreach( $options as $key => $option ) {

        $output .= '<p class="bbforms-options-help-option">';

        $output .= '<b class="bbforms-options-help-option-title">' . esc_html( $key ) .'</b>';
        if( isset( $option['desc'] ) ) $output .= '<br><span class="bbforms-options-help-option-description">' . $option['desc'] . '</span>';

        $output .= '<br>';
        $output .= '<br>';
        $output .= '<span class="bbforms-options-help-option-accepts">';

        switch( $option['type'] ) {
            case 'checkbox':
                $output .= esc_html__( 'Accepts:', 'bbforms' ) . ' <code>yes</code> ' . esc_html__( 'or', 'bbforms' ) . ' <code>no</code>';
                break;
            case 'select':
                $options = array_keys( $option['options'] );
                $options = '<code> ' .implode( '</code>, <code>', $options ) . ' </code>';

                // Replace last comma
                $pos = strrpos( $options, ',' );
                $options = substr_replace( $options, ' ' . esc_html__( 'or', 'bbforms' ), $pos, strlen(',') );

                $output .= esc_html__( 'Accepts:', 'bbforms' ) . ' ' . $options;
                break;
            case 'integer':
                $output .= esc_html__( 'Accepts any integer number.', 'bbforms' );
                break;
            case 'text':
            case 'textarea':
                $output .= esc_html__( 'Accepts any text of your choice.', 'bbforms' );
                break;
        }

        $output .= '</span>';
        $output .= '<br>';

        $output .= '<span class="bbforms-options-help-option-default">' . esc_html__( 'Default:', 'bbforms' ) . '</span>'
            . '<code>' . bbforms_parse_options_pattern( $key . '=' . $defaults[$key] ) . '</code>';

        $output .= '</p>';

    }

    $output .= '</div>';

    return $output;

}