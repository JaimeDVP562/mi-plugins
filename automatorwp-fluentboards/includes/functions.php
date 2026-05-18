<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get FluentBoards boards
 *
 * Helper used in AutomatorWP select fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_fluentboards_get_boards() {

    // Bail if FluentBoards is not available
    if ( ! class_exists( '\FluentBoards\App\Models\Board' ) ) {
        return array();
    }

    $boards = \FluentBoards\App\Models\Board::orderBy( 'title', 'ASC' )->get();

    $options = array();

    if ( ! empty( $boards ) ) {
        foreach ( $boards as $board ) {
            $options[ (int) $board->id ] = array(
                'id'   => (int) $board->id,
                'text' => esc_html( $board->title ),
            );
        }
    }

    return $options;
}

/**
 * Get WordPress users formatted for AutomatorWP select fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_fluentboards_get_users() {

    $users = get_users( array(
        'number'  => 50,
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ) );

    $options = array();

    if ( ! empty( $users ) ) {
        foreach ( $users as $user ) {
            $options[ (int) $user->ID ] = array(
                'id'   => (int) $user->ID,
                'text' => esc_html( $user->display_name . ' (' . $user->user_email . ')' ),
            );
        }
    }

    return $options;
}

/**
 * Get FluentBoards tasks
 */
function automatorwp_fluentboards_get_tasks() {
    // Demo: devolver tareas de prueba
    return array(
        1 => array('id' => 1, 'text' => 'Demo Task 1'),
        2 => array('id' => 2, 'text' => 'Demo Task 2'),
    );
}

/**
 * Get FluentBoards statuses
 */
function automatorwp_fluentboards_get_statuses() {
    // Demo: devolver estados de prueba
    return array(
        'open' => array('id' => 'open', 'text' => 'Open'),
        'in_progress' => array('id' => 'in_progress', 'text' => 'In Progress'),
        'done' => array('id' => 'done', 'text' => 'Done'),
    );
}
