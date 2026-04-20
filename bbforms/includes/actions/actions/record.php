<?php
/**
 * Record
 *
 * @package     BBForms\Actions\Record
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Action_Record extends BBForms_Action {

    public $bbcode = 'record';
    public $default_attrs = array();

    public $pattern = '[record]' . "\n";

    public function init() {
        $this->name = __( 'Record form submission', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[record]' . "\n",
                'label' => __( 'Basic form submission record action', 'bbforms' ),
            ),
        );
    }

    public function process_action( $attrs = array(), $content = null ) {

        $values = array();
        $excluded_fields = apply_filters( 'bbforms_record_action_excluded_fields', array(
            '_bbforms',
            '_bbforms_version',
            '_bbforms_nonce',
            '_bbforms_post',
            '_bbforms_user',
            '_bbforms_hp_' . $this->form->id,
        ) );

        foreach ( $this->form->fields as $field_name => $field ) {

            if( ! in_array( $field_name , $excluded_fields ) ) {
                $values[$field_name] = $field->sanitized_value;
            }
        }

        $submission = apply_filters( 'bbforms_record_action_submission_data', array(
            'form_id'   => $this->form->id,
            'user_id'   => ( isset( $this->form->fields['_bbforms_user'] ) ? $this->form->fields['_bbforms_user']->sanitized_value : 0 ),
            'post_id'   => ( isset( $this->form->fields['_bbforms_post'] ) ? $this->form->fields['_bbforms_post']->sanitized_value : 0 ),
            'fields'   => json_encode( $values ),
        ) );

        // Insert submission
        bbforms_insert_submission( $submission );

        return true;
    }

}
new BBForms_Action_Record();