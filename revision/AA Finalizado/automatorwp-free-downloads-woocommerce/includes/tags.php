<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function automatorwp_free_downloads_woocommerce_get_tags() {

    return array(
        'product_id' => array(
            'label'   => __( 'Product ID', 'automatorwp-free-downloads-woocommerce' ),
            'type'    => 'integer',
            'preview' => '123',
        ),
        'file_path' => array(
            'label'   => __( 'File Path', 'automatorwp-free-downloads-woocommerce' ),
            'type'    => 'text',
            'preview' => '/path/to/file.zip',
        ),
    );

}
