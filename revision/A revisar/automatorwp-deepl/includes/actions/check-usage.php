<?php
/**
 * Check Usage
 *
 * @package     AutomatorWP\Integrations\DeepL\Actions\Check_Usage
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DeepL_Check_Usage extends AutomatorWP_Integration_Action {

    public $integration     = 'deepl';
    public $action          = 'deepl_check_usage';
    public $result          = '';
    public $response        = '';
    public $character_count = 0;
    public $character_limit = 0;

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Check DeepL character usage', 'automatorwp-deepl' ),
            'select_option' => __( 'Check DeepL <strong>character usage</strong>', 'automatorwp-deepl' ),
            'edit_label'    => __( 'Check DeepL character usage this month', 'automatorwp-deepl' ),
            'log_label'     => __( 'Check DeepL character usage this month', 'automatorwp-deepl' ),
            'options'       => array(),
            // Extra tags: {character_count} and {character_limit} available in addition to {response}
            'tags'          => array(
                'response' => array(
                    'label'   => __( 'DeepL usage summary', 'automatorwp-deepl' ),
                    'type'    => 'text',
                    'preview' => '150000 / 500000 characters used this month',
                ),
                'character_count' => array(
                    'label'   => __( 'Characters used', 'automatorwp-deepl' ),
                    'type'    => 'text',
                    'preview' => '150000',
                ),
                'character_limit' => array(
                    'label'   => __( 'Character limit', 'automatorwp-deepl' ),
                    'type'    => 'text',
                    'preview' => '500000',
                ),
            ),
        ) );

    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        $this->result          = '';
        $this->response        = '';
        $this->character_count = 0;
        $this->character_limit = 0;

        if ( ! automatorwp_deepl_get_api() ) {
            $this->result = __( 'DeepL API Key is not configured.', 'automatorwp-deepl' );
            return;
        }

        $data = automatorwp_deepl_get_usage();

        if ( ! $data || isset( $data['error'] ) ) {
            $this->result = isset( $data['error'] ) ? $data['error'] : __( 'Could not retrieve usage data.', 'automatorwp-deepl' );
            return;
        }

        $count = isset( $data['character_count'] ) ? absint( $data['character_count'] ) : 0;
        $limit = isset( $data['character_limit'] ) ? absint( $data['character_limit'] ) : 0;

        $this->character_count = $count;
        $this->character_limit = $limit;
        $this->response        = sprintf( __( '%s / %s characters used this month', 'automatorwp-deepl' ), number_format( $count ), number_format( $limit ) );
        $this->result          = $this->response;

    }

    public function hooks() {

        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        // Register extra tag replacements for character_count and character_limit
        add_filter( 'automatorwp_get_action_tag_replacement', array( $this, 'tag_replacements' ), 10, 6 );

        parent::hooks();

    }

    public function tag_replacements( $replacement, $tag_name, $action, $user_id, $content, $log ) {

        if ( $action->type !== $this->action ) return $replacement;

        switch ( $tag_name ) {
            case 'character_count':
                $replacement = automatorwp_get_log_meta( $log->id, 'character_count', true );
                break;
            case 'character_limit':
                $replacement = automatorwp_get_log_meta( $log->id, 'character_limit', true );
                break;
        }

        return $replacement;

    }

    public function configuration_notice( $object, $item_type ) {

        if ( $item_type !== 'action' ) return;
        if ( $object->type !== $this->action ) return;

        if ( ! automatorwp_deepl_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">DeepL settings</a> to get this action to work.', 'automatorwp-deepl' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-deepl'
                ); ?>
            </div>
        <?php endif;

    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( $action->type !== $this->action ) return $log_meta;

        $log_meta['result']          = $this->result;
        $log_meta['response']        = isset( $this->response ) ? $this->response : '';
        $log_meta['character_count'] = isset( $this->character_count ) ? $this->character_count : 0;
        $log_meta['character_limit'] = isset( $this->character_limit ) ? $this->character_limit : 0;

        return $log_meta;

    }

    public function log_fields( $log_fields, $log, $object ) {

        if ( $log->type !== 'action' ) return $log_fields;
        if ( $object->type !== $this->action ) return $log_fields;

        $log_fields['result'] = array(
            'name' => __( 'Usage:', 'automatorwp-deepl' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DeepL_Check_Usage();