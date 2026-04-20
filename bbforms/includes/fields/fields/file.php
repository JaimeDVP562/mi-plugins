<?php
/**
 * File
 *
 * @package     BBForms\Field_Field\File
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_File extends BBForms_Field {

    public $bbcode = 'file';
    public $default_attrs = array(
        'accept' => '',
        'min' => '',
        'max' => '',
        'capture' => '',
        'media' => 'yes',
    );
    public $pattern = '[file* name="CONTENT" accept="image/*,video/*,audio/*"]' . "\n";
    public $file;
    public $file_error = false;
    public $custom_uploads_dir = '';

    public function init() {
        $this->name = __( 'File Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[file name="CONTENT" accept=""]' . "\n",
                'label' => __( 'Basic file field', 'bbforms' ),
            ),
            array(
                'pattern' => '[file* name="CONTENT" accept=""]' . "\n",
                'label' => __( 'Required file field', 'bbforms' ),
            ),
            array(
                'pattern' => '[file name="CONTENT" accept="image/*,video/*,audio/*"]' . "\n",
                'label' => __( 'Limited file type (wildcard)', 'bbforms' ),
            ),
            array(
                'pattern' => '[file name="CONTENT" accept=".jpg,.pdf,.txt"]' . "\n",
                'label' => __( 'Limited file type by extension', 'bbforms' ),
            ),
            array(
                'pattern' => '[file name="CONTENT" accept="image/jpeg,application/pdf,text/plain"]' . "\n",
                'label' => __( 'Limited file type by MIME type', 'bbforms' ),
            ),
            array(
                'pattern' => '[file name="CONTENT" accept="" min="1mb" max="10mb"]' . "\n",
                'label' => __( 'Limited file size', 'bbforms' ),
            ),
            array(
                'pattern' => '[file* name="" value="CONTENT" label="" desc="" accept="" min="" max="" capture="" media="" id="" class=""]' . "\n",
                'label' => __( 'File field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        if( ! empty( $attrs['min'] ) ) {
            $attrs['min'] = bbforms_parse_file_size( $attrs['min'] );
        }

        if( ! empty( $attrs['max'] ) ) {
            $attrs['max'] = bbforms_parse_file_size( $attrs['max'] );
        }

        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'file',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

    public function set_value( $value ) {
        if( is_array( $value ) && isset( $value['tmp_name'] ) ) {
            // File
            if( empty( $value['tmp_name'] ) ) {
                $this->value = '';
            } else {
                $this->file = $value;
                $this->value = $value['tmp_name'];
            }
        } else {
            // String given
            $this->value = $value;
        }

    }

    public function validate_length() {

        $valid = true;

        $min = ( isset( $this->attrs['min'] ) && ! empty( $this->attrs['min'] ) ? $this->attrs['min'] : '' );
        $max = ( isset( $this->attrs['max'] ) && ! empty( $this->attrs['max'] ) ? $this->attrs['max'] : '' );

        // Support to minlength and maxlength
        // NOTE: attrs are always passed with strtolower()
        if( isset( $this->attrs['minlength'] ) && ! empty( $this->attrs['minlength'] ) )
            $min = $this->attrs['minlength'];

        if( isset( $this->attrs['maxlength'] ) && ! empty( $this->attrs['maxlength'] ) )
            $max = $this->attrs['maxlength'];

        if( $min === '' && $max === '' ) {
            return $valid;
        }

        $value = isset( $this->file['size'] ) ? absint( $this->file['size'] ) : '';
        $min_value = ( $min !== '' ? bbforms_parse_file_size( $min ) : '' );
        $max_value = ( $max !== '' ? bbforms_parse_file_size( $max ) : '' );

        // min
        if( $value < $min_value ) {
            $valid = false;
            $this->length_error = bbforms_get_error_message( 'file_size_min_error' );
        }

        // max
        if( $value > $max_value ) {
            $valid = false;
            $this->length_error = bbforms_get_error_message( 'file_size_max_error' );
        }

        return $valid;
    }

    public function validate() {

        // Do not validate if field is optional and value is empty
        if( ! $this->is_required() && $this->value === '' ) {
            return true;
        }

        // If empty, nothing to do here
        if( $this->value === '' ) {
            return true;
        }

        $accept = ( ! empty( $this->attrs['accept'] ) ? explode( ',', $this->attrs['accept'] ) : array() );

        // Check accept parameter
        if( count( $accept ) ) {
            // Check the file extension
            $wp_filetype = wp_check_filetype( $this->file['name'] );

            if( ! $wp_filetype['ext'] ) {
                $this->file_error = 'file_type_error';
                return false;
            }

            // File extension (without .)
            $file_ext = $wp_filetype['ext'];

            $allowed_ext = array();

            foreach ( $accept as $a ) {
                $a = trim( $a );

                if ( preg_match( '/^\.[a-z0-9]+$/i', $a ) ) {
                    // Value is already a file extension
                    $allowed_ext[] = strtolower( trim( $a, ' .' ) );
                } else {
                    // Value is a MIME type, so convert it to extension
                    foreach ( bbforms_convert_mime_to_ext( $a ) as $ext ) {
                        $allowed_ext[] = strtolower( trim( $ext, ' .' ) );
                    }
                }
            }

            $allowed_ext = array_unique( $allowed_ext );

            if ( ! in_array( $file_ext, $allowed_ext, true ) ) {
                $this->file_error = 'file_type_error';
                return false;
            }

        }

        // All checks done, so upload the file!
        $file_uploaded = $this->upload_file();

        if( ! $file_uploaded ) {
            $this->file_error = 'file_error';
            return false;
        }

        return true;
    }

    public function upload_file() {

        $wp_filesystem = bbforms_get_filesystem();

        // Not file provided
        if( $this->value === '' ) {
            return false;
        }

        // Bail if temporal file not provided
        if ( empty( $this->file['tmp_name'] ) ) {
            return false;
        }

        // Bail if temporal file not uploaded
        if ( ! is_uploaded_file( $this->file['tmp_name'] ) ) {
            return false;
        }

        $uploads_dir = BBFORMS_UPLOAD_DIR;
        $uploads_dir = bbforms_create_random_dir( $uploads_dir );

        $this->custom_uploads_dir = $uploads_dir;

        // Sanitize file name
        $filename = sanitize_file_name( $this->file['name'] );

        /**
         * Filter to override uploaded filename
         *
         * @since 1.0.0
         *
         * @param string                $filename           Sanitized filename
         * @param string                $original_filename  Original filename
         * @param BBForms_Field_File    $field              File field object
         *
         * @return string
         */
        $filename = apply_filters( 'bbforms_upload_file_name', $filename, $this->file['name'], $this );

        $filename = wp_unique_filename( $uploads_dir, $filename );
        
        // Temporal filter to register path override.
        add_filter( 'upload_dir', array( $this, 'bbforms_filter_upload_dir' ) );

        $uploaded_file = array(
            'name'     => $filename,
            'type'     => $this->file['type'],
            'tmp_name' => $this->file['tmp_name'],
            'error'    => $this->file['error'],
            'size'     => $this->file['size']
        );

        $upload_overrides = array( 'test_form' => false );

        $new_file = wp_handle_upload( $uploaded_file, $upload_overrides );

        // Remove filter to avoid affecting other uploads
        remove_filter( 'upload_dir', array( $this, 'bbforms_filter_upload_dir' ) );

        // Could not move file
        if ( ! $new_file || isset( $new_file['error'] ) ) {
            return false;
        }

        // Set the file permissions
        $wp_filesystem->chmod( $new_file['file'], FS_CHMOD_FILE );

        $this->value = $new_file['file'];
        $this->sanitize();

        if( isset( $this->attrs['media'] ) && bbforms_is_option_enabled( $this->attrs['media'] ) ) {
            bbforms_import_attachment( $new_file['file'] );
        }
        
        return true;

    }

    public function bbforms_filter_upload_dir( $uploads_dir ) {
        return array(
            'path'    => $this->custom_uploads_dir,
            'url'     => $uploads_dir['baseurl'] . '/bbforms/' . basename( $this->custom_uploads_dir ),
            'subdir'  => '',
            'basedir' => $this->custom_uploads_dir,
            'baseurl' => $uploads_dir['baseurl'] . '/bbforms',
            'error'   => false,
        );
    }

    public function get_error_message() {
        if( $this->file_error !== false ) {
            return bbforms_get_error_message( $this->file_error );

        }
        return '';
    }

}
new BBForms_Field_File();