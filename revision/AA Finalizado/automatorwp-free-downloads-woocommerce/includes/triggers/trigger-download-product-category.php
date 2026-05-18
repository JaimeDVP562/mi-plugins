<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Free_Downloads_WooCommerce_Download_Product_Category extends AutomatorWP_Integration_Trigger {

    public $integration = 'free_downloads_woocommerce';
    public $trigger = 'free_downloads_woocommerce_download_product_category';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User downloads a product of a specific category', 'automatorwp-free-downloads-woocommerce' ),
            'select_option' => __( 'User downloads a product of <strong>a specific category</strong>', 'automatorwp-free-downloads-woocommerce' ),
            'edit_label'    => sprintf( __( 'User downloads a product of %1$s %2$s time(s)', 'automatorwp-free-downloads-woocommerce' ), '{category}', '{times}' ),
            'log_label'     => __( 'User downloads a product of a specific category', 'automatorwp-free-downloads-woocommerce' ),
            'action'        => 'somdn_count_download',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10, 
            'accepted_args' => 1,
            'options'       => array(
                'category' => automatorwp_utilities_term_option( array(
                    'name'            => __( 'Category:', 'automatorwp-free-downloads-woocommerce' ),
                    'option_code'     => 'category',
                    'taxonomy'        => 'product_cat',
                    'show_any_option' => true,
                    'default'         => 'any',
                ) ),
                'times'   => automatorwp_utilities_times_option(),
            ),
            'tags' => automatorwp_free_downloads_woocommerce_get_tags()
        ) );
    }

    public function hooks() {
        add_filter( 'automatorwp_user_deserves_trigger_' . $this->trigger, array( $this, 'user_deserves_trigger' ), 10, 6 );
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        parent::hooks();
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

        if ( ! $deserves_trigger || ! isset( $event['product_id'] ) ) {
            return false;
        }

        $selected_category = 'any';
        foreach ( $trigger_options as $key => $value ) {
            if ( strpos( $key, 'category' ) !== false ) {
                $selected_category = $value;
                break;
            }
        }

        if ( $selected_category !== 'any' && $selected_category !== '' ) {
            $term_id = absint( $selected_category );
            $product_id = absint( $event['product_id'] );
            
            if ( ! has_term( $term_id, 'product_cat', $product_id ) ) {
                return false; 
            }
        }

        return $deserves_trigger;
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

new AutomatorWP_Free_Downloads_WooCommerce_Download_Product_Category();