<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Placeholder tags definition file. Use AutomatorWP tags API to register tags if needed.
function automatorwp_mailmint_register_tags() {
    if ( ! function_exists( 'automatorwp_register_tag' ) ) return;

    // Example: automatorwp_register_tag( 'mailmint', 'mailmint_response', 'Mail Mint response', 'Gets value from API response' );
}
add_action( 'automatorwp_register_tags', 'automatorwp_mailmint_register_tags' );
