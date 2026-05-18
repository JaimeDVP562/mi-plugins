<?php
/**
 * Add Row
 *
 * @package     AutomatorWP\Integrations\Google_Sheets\Actions\Add_Row
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined('ABSPATH') ) exit;


class AutomatorWP_Google_Sheets_Add_Row extends AutomatorWP_Integration_Action {
    
    public $integration = 'google_sheets';
    public $action = 'google_sheets_add_row';

    /**
     * Store the action result
     *
     * @since 1.0.0
     *
     * @var string $result
     */
    public $result = '';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration' => $this->integration,
            'label' => __( 'Add a row to a spreadsheet', 'automatorwp-google-sheets' ),
            'select_option' => __( 'Add <strong>a row</strong> to a spreadsheet', 'automatorwp-google-sheets' ),
            /* translators: %1$s: Row. */
            'edit_label' => sprintf( __( 'Add %1$s to a spreadsheet', 'automatorwp-google-sheets' ), '{rows}' ),
            /* translators: %1$s: Row. */
            'log_label' => sprintf( __( 'Add %1$s to a spreadsheet', 'automatorwp-google-sheets' ), '{rows}' ),
            'options' => array(
                'rows' => array(
                    'from' => '',
                    'default' => __( 'a row', 'automatorwp-google-sheets' ),
                    'fields' => array(
                        'spreadsheet' => automatorwp_utilities_ajax_selector_field( array(
                            'field'             => 'spreadsheet',
                            'option_default'    => __( 'spreadsheet', 'automatorwp-google-sheets' ),
                            'placeholder'       => __( 'Select a spreadsheet', 'automatorwp-google-sheets' ),
                            'name'              => __( 'Spreadsheet:', 'automatorwp-google-sheets' ),
                            'action_cb'         => 'automatorwp_google_sheets_get_spreadsheets',
                            'options_cb'        => 'automatorwp_google_sheets_options_cb_spreadsheets',
                            'default'           => ''
                        ) ),
                        'worksheet' => automatorwp_utilities_ajax_selector_field( array(
                            'field'             => 'worksheet',
                            'option_default'    => __( 'worksheet', 'automatorwp-google-sheets' ),
                            'placeholder'       => __( 'Select a worksheet', 'automatorwp-google-sheets' ),
                            'name'              => __( 'Worksheet:', 'automatorwp-google-sheets' ),
                            'action_cb'         => 'automatorwp_google_sheets_get_worksheets',
                            'options_cb'        => 'automatorwp_google_sheets_options_cb_worksheets',
                            'default'           => ''
                        ) ),
                        'rows' => array(
                            'name' => __( 'Rows:', 'automatorwp-google-sheets' ),
                            'desc' => __( 'Rows to insert in the spreadsheet.', 'automatorwp-google-sheets' ),
                            'type' => 'group',
                            'classes' => 'automatorwp-fields-table automatorwp-fields-table-col-1',
                            'options'     => array(
                                'add_button'        => __( 'Add row', 'automatorwp-google-sheets' ),
                                'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                                ),
                            'fields' => array(
                                'value' => array(
                                    'name' => __( 'Value:', 'automatorwp-google-sheets' ),
                                    'type' => 'text',
                                    'default' => ''
                                ),
                            ),
                        ),
                    )
                )
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation){

        // Bail if not selected spreadsheet
        if( empty( $action_options['spreadsheet'] ) ) {
            $this->result = __( 'No spreadsheet selected on the action\'s setup.', 'automatorwp-google-sheets' );
            return;
        }

        // Bail if not data to insert
        if( empty( $action_options['rows'] ) ) {
            $this->result = __( 'No data found to insert.', 'automatorwp-google-sheets' );
            return;
        }

        $spreadsheet_id = $action_options['spreadsheet'];
        $worksheet_id = absint( $action_options['worksheet'] );
        $rows = $action_options['rows'];
        $values = array();
        
        foreach ( $rows as $row ){
            $values[] = $row['value'];
        }

        // Get the available worksheets for this spreadsheet
        $worksheets = automatorwp_google_sheets_get_worksheets( $spreadsheet_id );

        // Look for the worksheet name
        foreach( $worksheets as $worksheet ) {
            if( $worksheet['id'] == $worksheet_id ){
                $worksheet_name = $worksheet['title'];
            }
        }

        $params = automatorwp_google_sheets_get_request_parameters();

        // Bail if the authorization has not been setup from settings
        if( $params === false ) {
            return;
        }

        // Setup the request parameters
        $body_params = array(
            'values' => array( $values ),
        );

        $params['body'] = json_encode( $body_params );

        // Query to add record
        $url = 'https://sheets.googleapis.com/v4/spreadsheets/'.$spreadsheet_id.'/values/'.$worksheet_name.'!A1/:append?valueInputOption=USER_ENTERED&includeValuesInResponse=true&insertDataOption=INSERT_ROWS';
      
        $request = wp_remote_post( $url, $params );

        $response = json_decode( wp_remote_retrieve_body( $request ), true );

        $this->result = __( 'Data added to the spreadsheet successfully!', 'automatorwp-google-sheets' );
        
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-google-sheets' ),
            'type' => 'text',
        );

        return $log_fields;

    }
    
}

new AutomatorWP_Google_Sheets_Add_Row();