<?php
/**
 * Database
 *
 * @package     ShortLinksPro\Classes\Database
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class ShortLinksPro_Database {

    /**
     * Posts table name
     *
     * @since 1.0.0
     *
     * @var string $posts
     */
    public $posts = '';

    /**
     * Post meta table name
     *
     * @since 1.0.0
     *
     * @var string $postmeta
     */
    public $postmeta = '';

    /**
     * Users table name
     *
     * @since 1.0.0
     *
     * @var string $users
     */
    public $users = '';

    /**
     * User meta table name
     *
     * @since 1.0.0
     *
     * @var string $user
     */
    public $usermeta = '';

    /**
     * Links table name
     *
     * @since 1.0.0
     *
     * @var string $links
     */
    public $links = '';

    /**
     * Links meta table name
     *
     * @since 1.0.0
     *
     * @var string $links_meta
     */
    public $links_meta = '';

    /**
     * Clicks table name
     *
     * @since 1.0.0
     *
     * @var string $clicks
     */
    public $clicks = '';

    /**
     * Clicks meta table name
     *
     * @since 1.0.0
     *
     * @var string $clicks_meta
     */
    public $clicks_meta = '';

}