<?php
/**
 * Admin
 *
 * @package DeckGenerator\Admin
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Add admin menu and conditionally enqueue scripts for the plugin page
 *
 * @since 1.0.0
 */
function deck_generator_admin_menu() {
  $hook = add_menu_page(
    'Deck Generator',
    'Deck Generator',
    'read',
    'deck-generator',
    'deck_generator_admin_page',
    'dashicons-editor-table',
    80
  );

  add_action( 'admin_enqueue_scripts', function( $current_hook ) use ( $hook ) {
    deck_generator_admin_enqueue_scripts( $current_hook, $hook );
  } );
}
add_action( 'admin_menu', 'deck_generator_admin_menu' );

/**
 * Admin page content
 *
 * @since 1.0.0
 */
function deck_generator_admin_page() {
  ?>
  <div class="wrap">
    <h1>Deck Generator</h1>
    <div id="deck-generator-app">
      <!-- Left Column - Form -->
      <div id="deck-generator-form">

        <div class="dg-field">
          <label for="dg-god">Dios</label>
          <select id="dg-god" name="dg_god">
          </select>
        </div>

        <div id="dg-shared-state" class="dg-field dg-shared-state" style="display:none">
          <label>Mazo cargado desde URL</label>
          <div id="dg-shared-state-content" class="dg-shared-state__content"></div>
          <p class="dg-hint">Modo compartido activo. Modifica filtros o pulsa regenerar para volver al modo normal.</p>
        </div>

        <div class="dg-field">
          <label for="dg-monsters">Monstruos</label>
          <select id="dg-monsters" name="dg_monsters">
            <option value="random">Aleatorio (3-6)</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
          </select>
        </div>

        <div class="dg-field">
          <label>Familias de monstruo</label>
          <div id="dg-families" class="dg-checkboxes"></div>
          <p class="dg-hint">Sin selección: cualquier familia</p>
        </div>

        <div class="dg-field">
          <label>Niveles de monstruo</label>
          <div id="dg-levels" class="dg-checkboxes"></div>
          <p class="dg-hint">Sin selección: cualquier nivel</p>
        </div>

        <div class="dg-field">
          <label>Elementos</label>
          <div id="dg-elements" class="dg-checkboxes"></div>
          <p class="dg-hint">Sin selección: cualquier elemento</p>
        </div>

        <div class="dg-field">
          <label for="dg-charms">Amuletos</label>
          <select id="dg-charms" name="dg_charms">
            <option value="random">Aleatorio (0-3)</option>
            <option value="0">0</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
          </select>
        </div>

        <div class="dg-field">
          <label for="dg-tokens">Fichas</label>
          <select id="dg-tokens" name="dg_tokens">
            <option value="random">Aleatorio (0-3)</option>
            <option value="0">0</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
          </select>
        </div>

        <div class="dg-field">
          <label>Relación con el dios</label>
          <div class="dg-checkboxes">
            <label>
              <input type="checkbox" id="dg-rel-charms">
              Amuletos afines al dios
            </label>
            <label>
              <input type="checkbox" id="dg-rel-tokens">
              Fichas afines al dios
            </label>
            <label>
              <input type="checkbox" id="dg-rel-spells">
              Hechizos afines al dios
            </label>
          </div>
        </div>

        <div class="dg-field">
          <button type="button" id="dg-regenerate">Regenerar</button>
        </div>

      </div>

      <!-- Right Column - Result -->
      <div id="deck-generator-right">

        <div id="dg-warnings" style="display:none"></div>

        <div id="deck-generator-result">

        </div>

        <!-- Summary textarea -->
        <div id="dg-summary-wrap">
          <label for="dg-summary">Resumen</label>
          <textarea id="dg-summary" readonly></textarea>
        </div>

        <!-- Shareable URL -->
        <div id="dg-share-wrap">
          <label for="dg-share-url">Enlace compartible</label>
          <div class="dg-share-row">
            <input type="text" id="dg-share-url" readonly>
            <button type="button" id="dg-copy-url">Copiar</button>
          </div>
        </div>
        
      </div>

    </div>
  </div>
  <?php
}
