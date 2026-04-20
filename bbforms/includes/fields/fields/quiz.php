<?php
/**
 * Quiz
 *
 * @package     BBForms\Field_Field\Quiz
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// [select* name="NAME" options="Option 1|Option 2|Option 3" options_values="1|2|3" multiple="yes"]
class BBForms_Field_Quiz extends BBForms_Field {

    public $bbcode = 'quiz';
    public $default_attrs = array(
        'options'           => '',      // The values to display
        'options_values'    => '',      // The values to store, if not defined, will use a sanitize_key() on options
    );
    public $pattern = '[quiz* name=""]' . "\n"
    . 'CONTENT' . "\n"
    . '[/quiz]' . "\n";

    public $question;
    public $answer;
    public $question_as_label = false;

    public function init() {
        $this->name = __( 'Quiz Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[quiz name=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/quiz]' . "\n",
                'label' => __( 'Basic quiz field', 'bbforms' ),
            ),
            array(
                'pattern' => '[quiz* name=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/quiz]' . "\n",
                'label' => __( 'Required quiz field', 'bbforms' ),
            ),
            array(
                'pattern' => '[quiz name=""]' . "\n"
                    . 'CONTENT_MATH' . "\n"
                    . '[/quiz]' . "\n",
                'label' => __( 'Auto-generated math quiz', 'bbforms' ),
            ),
            array(
                'pattern' => '[quiz* name="" label="" desc="" id="" class=""]' . "\n"
                    . 'CONTENT' . "\n"
                    . '[/quiz]' . "\n",
                'label' => __( 'Quiz field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function parse_attrs( $attrs, $content = null ) {
        parent::parse_attrs( $attrs, $content );

        // Parse options
        $options = $this->parse_options( $this->attrs, $content );

        // Ensure options as array
        if( ! is_array( $options ) ) {
            $options = array( $options );
        }

        // If value give, check if is a valid answer to set it as question and answer
        $picked_from_value = false;
        if( ! empty( $this->attrs['value'] ) ) {
            foreach( $options as $question => $answer ) {
                if( strtolower( $this->attrs['value'] ) === strtolower( $answer ) ) {
                    $this->question = $question;
                    $this->answer = $answer;

                    $picked_from_value = true;
                }
            }

        }

        // Pick a random question
        if( ! $picked_from_value ) {
            if( count( $options ) === 1 ) {
                // There is only one question, so we can only pick this
                $key = 0;
            } else {
                // Pick a random question
                $questions = array_keys( $options );
                $key = array_rand( $questions, 1 );
            }

            $this->question = $questions[$key];
            $this->answer = $options[$this->question];
        }

        if( $this->attrs['label'] === '' ) {
            $this->attrs['label'] = $this->question;
            $this->question_as_label = true;
        } else {
            $this->question_as_label = false;
        }
    }

    public function render_field( $attrs = array(), $content = null ) {

        $output = '';

        if( ! $this->question_as_label ) {
            $output .= '<span class="bbforms-quiz-question">' . $this->question . ' </span>';
        }

        $output .= sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"><%1$s type="hidden" name="%5$s_hash" value="%6$s">',
            'input',
            'text',
            bbforms_concat_attrs( $attrs, array( 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
            $attrs['name'],
            wp_hash( strtolower( $this->answer ) ),
        );

        return $output;
    }

    public function validate() {
        global $bbforms_request;

        // Do not validate if field is optional and value is empty
        if( ! $this->is_required() && $this->value === '' ) {
            return true;
        }

        if( ! isset( $bbforms_request[$this->attrs['name'] . '_hash'] ) ) {
            return false;
        }

        // Check if given answer hash matches with solution hash
        return ( wp_hash( strtolower( $this->value ) ) === $bbforms_request[$this->attrs['name'] . '_hash'] );
    }

    public function get_error_message() {
        return bbforms_get_error_message( 'quiz_error' );
    }

}
new BBForms_Field_Quiz();