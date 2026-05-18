<?php
/**
 * Scripts
 *
 * @package DeckGenerator\Scripts
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Enqueue admin scripts
 *
 * @since 1.0.0
 */
function deck_generator_admin_enqueue_scripts( $current_hook, $page_hook ) {
  if ( $current_hook !== $page_hook ) {
    return;
  }

  $localized = deck_generator_get_data();
  $localized['shareBaseUrl'] = admin_url( 'admin.php' );
  $localized['pageSlug'] = 'deck-generator';

  wp_register_style(
    'deck-generator-admin',
    DECK_GENERATOR_URL . 'assets/css/deckgenerator-admin.css',
    array(),
    DECK_GENERATOR_VER
  );

  wp_register_script(
    'deck-generator-admin',
    DECK_GENERATOR_URL . 'assets/js/deckgenerator-admin.js',
    array(),
    DECK_GENERATOR_VER,
    true
  );

  wp_localize_script(
    'deck-generator-admin',
    'DeckData',
    $localized
  );

  wp_enqueue_style( 'deck-generator-admin' );
  wp_enqueue_script( 'deck-generator-admin' );
}
