<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\MailMint\Functions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -------------------------------------------------------
// Plugin status
// -------------------------------------------------------

/**
 * Check whether Mail Mint is installed and active
 *
 * @since 1.0.0
 *
 * @return bool
 */
function automatorwp_mailmint_is_active()
{
    return defined( 'MRM_VERSION' );
}

// -------------------------------------------------------
// Contact helpers
// -------------------------------------------------------

/**
 * Get a Mail Mint contact row by its internal ID
 *
 * @since 1.0.0
 *
 * @param int $contact_id
 *
 * @return object|null
 */
function automatorwp_mailmint_get_contact( $contact_id )
{
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mint_contacts WHERE id = %d LIMIT 1",
        (int) $contact_id
    ) );
}

/**
 * Get a Mail Mint contact row by email address
 *
 * @since 1.0.0
 *
 * @param string $email
 *
 * @return object|null
 */
function automatorwp_mailmint_get_contact_by_email( $email )
{
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mint_contacts WHERE email = %s LIMIT 1",
        sanitize_email( $email )
    ) );
}

/**
 * Resolve a WordPress user ID from a Mail Mint contact ID.
 * Falls back to the first administrator if no matching WP user is found.
 *
 * @since 1.0.0
 *
 * @param int $contact_id
 *
 * @return int WP user ID, or 0 on failure
 */
function automatorwp_mailmint_get_user_id_from_contact( $contact_id )
{
    $contact = automatorwp_mailmint_get_contact( $contact_id );

    if ( ! $contact || empty( $contact->email ) ) {
        return 0;
    }

    $user = get_user_by( 'email', $contact->email );

    if ( $user ) {
        return (int) $user->ID;
    }

    // Fall back to the first admin when contact email has no WP account
    $admins = get_users( array(
        'role'    => 'administrator',
        'number'  => 1,
        'orderby' => 'ID',
        'order'   => 'ASC',
    ) );

    return ! empty( $admins ) ? (int) $admins[0]->ID : 0;
}

// -------------------------------------------------------
// Groups (tags / lists) helpers
// -------------------------------------------------------

/**
 * Get a Mail Mint contact group (tag or list) by its ID
 *
 * @since 1.0.0
 *
 * @param int $group_id
 *
 * @return object|null
 */
function automatorwp_mailmint_get_group( $group_id )
{
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mint_contact_groups WHERE id = %d LIMIT 1",
        (int) $group_id
    ) );
}

/**
 * Get a Mail Mint contact group by title and type
 *
 * @since 1.0.0
 *
 * @param string $title
 * @param string $type  'tags' or 'lists'
 *
 * @return object|null
 */
function automatorwp_mailmint_get_group_by_title( $title, $type )
{
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mint_contact_groups WHERE title = %s AND type = %s LIMIT 1",
        sanitize_text_field( $title ),
        sanitize_text_field( $type )
    ) );
}

/**
 * Get all Mail Mint tags formatted as an id => title array for select fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_mailmint_get_tags()
{
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT id, title FROM {$wpdb->prefix}mint_contact_groups WHERE type = 'tags' ORDER BY title ASC"
    );

    $options = array( '' => __( '-- Any tag --', 'automatorwp-mailmint' ) );

    foreach ( $rows as $row ) {
        $options[ (int) $row->id ] = esc_html( $row->title );
    }

    return $options;
}

/**
 * Get all Mail Mint lists formatted as an id => title array for select fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_mailmint_get_lists()
{
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT id, title FROM {$wpdb->prefix}mint_contact_groups WHERE type = 'lists' ORDER BY title ASC"
    );

    $options = array( '' => __( '-- Any list --', 'automatorwp-mailmint' ) );

    foreach ( $rows as $row ) {
        $options[ (int) $row->id ] = esc_html( $row->title );
    }

    return $options;
}

/**
 * Extract the title from a tag/list entry as passed to Mail Mint hooks.
 * The entry can be an associative array with 'id'/'title' keys, or a bare integer ID.
 *
 * @since 1.0.0
 *
 * @param mixed $group
 *
 * @return string
 */
function automatorwp_mailmint_resolve_group_title( $group )
{
    if ( is_array( $group ) && ! empty( $group['title'] ) ) {
        return (string) $group['title'];
    }

    $id = is_array( $group ) ? ( isset( $group['id'] ) ? (int) $group['id'] : 0 ) : (int) $group;

    if ( ! $id ) {
        return '';
    }

    $row = automatorwp_mailmint_get_group( $id );

    return $row ? (string) $row->title : '';
}

// -------------------------------------------------------
// Contact status
// -------------------------------------------------------

/**
 * Update the subscription status of a Mail Mint contact.
 * Valid statuses: subscribed, unsubscribed, pending, complained, bounced, inactive.
 *
 * @since 1.0.0
 *
 * @param int    $contact_id
 * @param string $status
 *
 * @return bool
 */
function automatorwp_mailmint_update_contact_status( $contact_id, $status )
{
    global $wpdb;

    $allowed = array( 'subscribed', 'unsubscribed', 'pending', 'complained', 'bounced', 'inactive' );

    if ( ! in_array( $status, $allowed, true ) ) {
        return false;
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'mint_contacts',
        array( 'status' => $status ),
        array( 'id'     => (int) $contact_id ),
        array( '%s' ),
        array( '%d' )
    );

    return $updated !== false;
}

// -------------------------------------------------------
// Remove tag / list from contact
// -------------------------------------------------------

/**
 * Remove one or more tags/lists from a contact in the pivot table
 *
 * @since 1.0.0
 *
 * @param int   $contact_id
 * @param array $group_ids  Array of group IDs to remove
 *
 * @return bool
 */
function automatorwp_mailmint_remove_groups_from_contact( $contact_id, $group_ids )
{
    global $wpdb;

    if ( empty( $group_ids ) ) {
        return false;
    }

    $table       = $wpdb->prefix . 'mint_contact_group_relationship';
    $ids_escaped = implode( ',', array_map( 'intval', $group_ids ) );

    $deleted = $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$table} WHERE contact_id = %d AND group_id IN ({$ids_escaped})",
        (int) $contact_id
    ) );

    return $deleted !== false;
}

// -------------------------------------------------------
// Email broadcast log helpers
// -------------------------------------------------------

/**
 * Get a Mail Mint contact row from a broadcast email log entry.
 * Mail Mint stores individual email sends in {prefix}mint_broadcast_emails.
 *
 * @since 1.0.0
 *
 * @param int $log_id  The broadcast email log entry ID
 *
 * @return object|null
 */
function automatorwp_mailmint_get_contact_from_email_log( $log_id )
{
    global $wpdb;

    $contact_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT contact_id FROM {$wpdb->prefix}mint_broadcast_emails WHERE id = %d LIMIT 1",
        (int) $log_id
    ) );

    return $contact_id ? automatorwp_mailmint_get_contact( (int) $contact_id ) : null;
}
