<?php
/**
 * Plugin Name: Deck Generator
 * Description: Generates random decks from card pools in wp-admin.
 * Version: 1.0.0
 *
 * @package DeckGenerator
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

final class Deck_Generator {

  /**
   * @var       Deck_Generator $instance The one true Deck_Generator
   * @since     1.0.0
   */
  private static $instance;

  /**
   * Get active instance
   *
   * @access    public
   * @since     1.0.0
   * @return    \Deck_Generator self::$instance The one true Deck_Generator
   */
  public static function instance() {
    if ( !self::$instance ) {
      self::$instance = new Deck_Generator();
      self::$instance->constants();
      self::$instance->includes();
    }
    return self::$instance;
  }

  /**
   * Setup plugin constants
   * 
   * @access    private
   * @since     1.0.0
   * @return    void
   */
  private function constants() {
    // Plugin version
    define( 'DECK_GENERATOR_VER', '1.0.0' );

    // Main plugin file
    define( 'DECK_GENERATOR_FILE', __FILE__ );

    // Plugin directory path
    define( 'DECK_GENERATOR_DIR', plugin_dir_path( __FILE__ ) );

    // Plugin base URL
    define( 'DECK_GENERATOR_URL', plugin_dir_url( __FILE__ ) );
  }

  /**
   * Include plugin files
   *
   * @access    private
   * @since     1.0.0
   * @return    void
   */
  private function includes() {

    require_once DECK_GENERATOR_DIR . 'includes/data.php';
    require_once DECK_GENERATOR_DIR . 'includes/scripts.php';
    require_once DECK_GENERATOR_DIR . 'includes/admin.php';

  }
}

/**
 * The main function responsible for returning the one true Deck_Generator
 * 
 * @since     1.0.0
 * @return    \Deck_Generator The one true Deck_Generator
 */
function Deck_Generator() {
  return Deck_Generator::instance();
}
add_action( 'plugins_loaded', 'Deck_Generator' );
