<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function automatorwp_surecontact_get_tags() {

    return array(
        'contact_uuid' => array(
            'label'   => __( 'Contact UUID', 'automatorwp-surecontact' ),
            'type'    => 'text',
            'preview' => 'abc123-def456',
        ),
        'email' => array(
            'label'   => __( 'Contact Email', 'automatorwp-surecontact' ),
            'type'    => 'text',
            'preview' => 'contact@example.com',
        ),
    );

}