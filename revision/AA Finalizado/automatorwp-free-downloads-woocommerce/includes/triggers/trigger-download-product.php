<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Free_Downloads_WooCommerce_Download_Product extends AutomatorWP_Integration_Trigger {

    public $integration = 'free_downloads_woocommerce';
    public $trigger = 'free_downloads_woocommerce_download_product';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User downloads a product', 'automatorwp-free-downloads-woocommerce' ),
            'select_option' => __( 'User downloads <strong>a product</strong>', 'automatorwp-free-downloads-woocommerce' ),
            'edit_label'    => sprintf( __( 'User downloads %1$s %2$s time(s)', 'automatorwp-free-downloads-woocommerce' ), '{product}', '{times}' ),
            'log_label'     => __( 'User downloads a product', 'automatorwp-free-downloads-woocommerce' ),
            'action'        => 'somdn_count_download',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10, 
            'accepted_args' => 1,
            'options'       => array(
                'product' => automatorwp_utilities_post_option( array(
                    'name'            => __( 'Product:', 'automatorwp-free-downloads-woocommerce' ),
                    'option_code'     => 'product',
                    'post_type'       => 'product',
                    'show_any_option' => true,
                    'default'         => 'any',
                ) ),
                'times'   => automatorwp_utilities_times_option(),
            ),
            'tags' => automatorwp_free_downloads_woocommerce_get_tags()
        ) );
    }

    public function listener( $product_id ) {

        $user_id = get_current_user_id();

        if( $user_id === 0 ) {
            return;
        }

        $file_path_string = '';
        $product = wc_get_product( $product_id );
        
        if ( $product && $product->is_downloadable() ) {
            $downloads = $product->get_downloads();
            $file_urls = array();
            
            foreach ( $downloads as $download ) {
                $file_urls[] = $download->get_file();
            }
            
            $file_path_string = implode( ', ', $file_urls );
        }

        automatorwp_trigger_event( array(
            'trigger'    => $this->trigger,
            'user_id'    => $user_id,
            'product_id' => $product_id,
            'file_path'  => $file_path_string,
        ) );

    }

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        if( ! isset( $event['product_id'] ) ) {
            return false;
        }

        if( isset( $trigger_options['product'] ) && $trigger_options['product'] !== 'any' ) {
            if( absint( $event['product_id'] ) !== absint( $trigger_options['product'] ) ) {
                return false;
            }
        }

        return $deserves_trigger;

    }

public function hooks() {
        add_filter( 'automatorwp_user_deserves_trigger_' . $this->trigger, array( $this, 'user_deserves_trigger' ), 10, 6 );
        
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['product_id'] = isset( $event['product_id'] ) ? absint( $event['product_id'] ) : '';
        $log_meta['file_path']  = isset( $event['file_path'] ) ? sanitize_text_field( $event['file_path'] ) : '';

        return $log_meta;

    }

} 

new AutomatorWP_Free_Downloads_WooCommerce_Download_Product();