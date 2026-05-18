<?php
/**
 * Tags
 *
 * @package     AutomatorWP\TI_WOOCOMMERCE_WISHLIST\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;



/**
 * Custom tags
 *
 * @since 1.0.0
 *
 * @param array $tags The global tags
 *
 * @return array
 */
function automatorwp_user_wishlist_get_tags( $tags ) {

    foreach( automatorwp_user_wishlist_get_registration_fields() as $field ) {

        $tags['tiwoocommercewishlist']['tags']['tiwoocommercewishlist' . $field] = array(
            'label'     => automatorwp_user_wishlist_get_registration_field_label( $field ),
            'type'      => 'text',
            'preview'   => automatorwp_user_wishlist_get_registration_field_preview( $field ),
        );

    }

    return $tags;

}
add_filter( 'automatorwp_get_tags', 'automatorwp_user_wishlist_get_tags' );



/**
 * Tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_user_wishlist_get_webhook_tags() {

    return array(
        'product' => array(
            'label'     => __( 'product', 'automatorwp-ti-woocommerce-wishlist' ),
            'type'      => 'text',
            'preview'   => 'Name of the user',
        ),
        'loop' => array(
            'label'     => __( 'loop', 'automatorwp-ti-woocommerce-wishlist' ),
            'type'      => 'text',
            'preview'   => 'Email of the user',
        )
 
    );

}



/**
 * Filter tags displayed on the tag selector
 *
 * @since 1.0.0
 *
 * @param array     $tags       The tags
 * @param stdClass  $automation The automation object
 * @param stdClass  $object     The trigger/action object
 * @param string    $item_type  The item type (trigger|action)
 *
 * @return array
 */
function automatorwp_user_wishlist_tags_selector_html_tags( $tags, $automation, $object, $item_type ) {

    // Remove tags on anonymous user action
    if( $automation->type === 'anonymous' && $object->type === 'automatorwp_anonymous_user' ) {
        if( isset( $tags['tiwoocommercewishlist'] ) ) {
            unset( $tags['tiwoocommercewishlist'] );
        }
    }

    return $tags;

}
add_filter( 'automatorwp_tags_selector_html_tags', 'automatorwp_user_wishlist_tags_selector_html_tags', 10, 4 );

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_user_wishlist_get_trigger_registration_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {


    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'tiwoocommercewishlist' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'product':
            $replacement = automatorwp_get_log_meta( $log->id, 'product', true );
            break;
        case 'loop':
            $replacement = automatorwp_get_log_meta( $log->id, 'loop', true );
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_user_wishlist_get_trigger_registration_tag_replacement', 10, 6 );