<?php
/**
 * Functions
 *
 * @package     AutomatorWP\FireBox\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Options callback for select2 fields assigned to forms (FireBox campaigns)
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_firebox_options_cb_form($field)
{

    $value      = $field->escaped_value;
    $none_value = 'any';
    $none_label = __('any form', 'automatorwp-firebox');
    $options    = automatorwp_options_cb_none_option($field, $none_value, $none_label);

    if (!empty($value)) {
        if (!is_array($value)) {
            $value = array($value);
        }

        foreach ($value as $post_id) {

            if ($post_id === $none_value) {
                continue;
            }

            $options[$post_id] = automatorwp_firebox_get_form_title($post_id);
        }
    }

    return $options;

}

/**
 * Get the FireBox campaign title by post ID
 *
 * @since 1.0.0
 *
 * @param int $post_id
 *
 * @return string
 */
function automatorwp_firebox_get_form_title($post_id)
{

    if (absint($post_id) === 0) {
        return '';
    }

    $title = get_the_title($post_id);

    return ($title ? $title : '');

}

/**
 * Normalize form field values from a FireBox submission
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return array
 */
function automatorwp_firebox_get_form_fields_values($fields)
{

    if (!is_array($fields)) {
        return array();
    }

    $form_fields = array();

    foreach ($fields as $field_name => $field_value) {

        if (is_array($field_value)) {

            foreach ($field_value as $subfield_name => $subfield_value) {
                if (is_string($subfield_name)) {
                    $form_fields[$subfield_name] = $subfield_value;
                }
            }

        } else {
            $form_fields[$field_name] = $field_value;
        }

    }

    if (function_exists('automatorwp_utilities_pull_array_values')) {
        $form_fields = automatorwp_utilities_pull_array_values($form_fields);
    }

    return $form_fields;

}

/**
 * Custom tags replacements for FireBox form fields
 *
 * @since 1.0.0
 *
 * @param string $parsed_content
 * @param array  $replacements
 * @param int    $automation_id
 * @param int    $user_id
 * @param string $content
 *
 * @return string
 */
function automatorwp_firebox_parse_automation_tags($parsed_content, $replacements, $automation_id, $user_id, $content)
{

    $new_replacements = array();

    $triggers = automatorwp_get_automation_triggers($automation_id);

    foreach ($triggers as $trigger) {

        $trigger_args = automatorwp_get_trigger($trigger->type);

        if ($trigger_args['integration'] !== 'firebox') {
            continue;
        }

        $log = automatorwp_get_user_last_completion($trigger->id, $user_id, 'trigger');

        if (!$log) {
            continue;
        }

        ct_setup_table('automatorwp_logs');
        $form_fields = ct_get_object_meta($log->id, 'form_fields', true);
        ct_reset_setup_table();

        if (!is_array($form_fields)) {
            continue;
        }

        // Replace {t:ID:form_field:NAME}
        preg_match_all("/\{t:" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches);

        if (is_array($matches) && isset($matches[1])) {
            foreach ($matches[1] as $field_name) {
                if (isset($form_fields[$field_name])) {
                    $new_replacements['{t:' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }
        }

        // Replace {ID:form_field:NAME} (legacy format)
        preg_match_all("/\{" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches);

        if (is_array($matches) && isset($matches[1])) {
            foreach ($matches[1] as $field_name) {
                if (isset($form_fields[$field_name])) {
                    $new_replacements['{' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }
        }

    }

    if (count($new_replacements)) {
        $parsed_content = str_replace(array_keys($new_replacements), $new_replacements, $parsed_content);
    }

    return $parsed_content;

}
add_filter('automatorwp_parse_automation_tags', 'automatorwp_firebox_parse_automation_tags', 10, 5);
