<?php
/**
 * Data
 *
 * @package DeckGenerator\Data
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Get all data for the deck generator
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_data() {
  return array(
    'deckLimit' => deck_generator_get_deck_limit(),
    'gods'      => deck_generator_get_gods(),
    'families'  => deck_generator_get_families(),
    'levels'    => deck_generator_get_levels(),
    'elements'  => deck_generator_get_elements(),
    'monsters'  => deck_generator_get_monsters(),
    'spells'    => deck_generator_get_spells(),
    'charms'    => deck_generator_get_charms(),
    'tokens'    => deck_generator_get_tokens(),
  );
}

/**
 * Get deck limit
 *
 * @since 1.0.0
 * @return int
 */
function deck_generator_get_deck_limit() {
  return 20;
}

/**
 * Get monster families
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_families() {
  return array(
    0 => null,
    1 => array( 'id' => 1, 'name' => 'Familia 1' ),
    2 => array( 'id' => 2, 'name' => 'Familia 2' ),
    3 => array( 'id' => 3, 'name' => 'Familia 3' ),
    4 => array( 'id' => 4, 'name' => 'Familia 4' ),
    5 => array( 'id' => 5, 'name' => 'Familia 5' ),
  );
}

/**
 * Get monster levels
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_levels() {
  return array(
    0 => null,
    1 => array( 'id' => 1, 'name' => 'Nivel 1' ),
    2 => array( 'id' => 2, 'name' => 'Nivel 2' ),
    3 => array( 'id' => 3, 'name' => 'Nivel 3' ),
  );
}

/**
 * Get elements
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_elements() {
  return array(
    0 => null,
    1 => array(
      'id'    => 1,
      'name'  => 'Fuego',
      'image' => DECK_GENERATOR_URL . 'assets/img/elements/Element_Fire.png',
    ),
    2 => array(
      'id'    => 2,
      'name'  => 'Agua',
      'image' => DECK_GENERATOR_URL . 'assets/img/elements/Element_Water.png',
    ),
    3 => array(
      'id'    => 3,
      'name'  => 'Tierra',
      'image' => DECK_GENERATOR_URL . 'assets/img/elements/Element_Earth.png',
    ),
    4 => array(
      'id'    => 4,
      'name'  => 'Viento',
      'image' => DECK_GENERATOR_URL . 'assets/img/elements/Element_Wind.png',
    ),
    5 => array(
      'id'    => 5,
      'name'  => 'Vacío',
      'image' => DECK_GENERATOR_URL . 'assets/img/elements/Element_Void.png',
    ),
  );
}

/**
 * Get monsters
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_monsters() {
  return array(
    0  => null,
    1  => array( 'id' => 1,  'name' => 'Gokai 1 Nv 1', 'family' => 1, 'level' => 1, 'elements' => array( 1 ),       'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai01.png' ),
    2  => array( 'id' => 2,  'name' => 'Gokai 1 Nv 2', 'family' => 1, 'level' => 2, 'elements' => array( 1, 2 ),    'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai02.png' ),
    3  => array( 'id' => 3,  'name' => 'Gokai 1 Nv 3', 'family' => 1, 'level' => 3, 'elements' => array( 1, 2, 3 ), 'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai03.png' ),
    4  => array( 'id' => 4,  'name' => 'Gokai 2 Nv 1', 'family' => 2, 'level' => 1, 'elements' => array( 2 ),       'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai01.png' ),
    5  => array( 'id' => 5,  'name' => 'Gokai 2 Nv 2', 'family' => 2, 'level' => 2, 'elements' => array( 2, 3 ),    'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai02.png' ),
    6  => array( 'id' => 6,  'name' => 'Gokai 2 Nv 3', 'family' => 2, 'level' => 3, 'elements' => array( 2, 3, 4 ), 'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai03.png' ),
    7  => array( 'id' => 7,  'name' => 'Gokai 3 Nv 1', 'family' => 3, 'level' => 1, 'elements' => array( 3 ),       'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai01.png' ),
    8  => array( 'id' => 8,  'name' => 'Gokai 3 Nv 2', 'family' => 3, 'level' => 2, 'elements' => array( 3, 4 ),    'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai02.png' ),
    9  => array( 'id' => 9,  'name' => 'Gokai 3 Nv 3', 'family' => 3, 'level' => 3, 'elements' => array( 3, 4, 5 ), 'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai03.png' ),
    10 => array( 'id' => 10, 'name' => 'Gokai 4 Nv 1', 'family' => 4, 'level' => 1, 'elements' => array( 4 ),       'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai01.png' ),
    11 => array( 'id' => 11, 'name' => 'Gokai 4 Nv 2', 'family' => 4, 'level' => 2, 'elements' => array( 4, 5 ),    'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai02.png' ),
    12 => array( 'id' => 12, 'name' => 'Gokai 4 Nv 3', 'family' => 4, 'level' => 3, 'elements' => array( 1, 4, 5 ), 'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai03.png' ),
    13 => array( 'id' => 13, 'name' => 'Gokai 5 Nv 1', 'family' => 5, 'level' => 1, 'elements' => array( 5 ),       'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai01.png' ),
    14 => array( 'id' => 14, 'name' => 'Gokai 5 Nv 2', 'family' => 5, 'level' => 2, 'elements' => array( 1, 5 ),    'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai02.png' ),
    15 => array( 'id' => 15, 'name' => 'Gokai 5 Nv 3', 'family' => 5, 'level' => 3, 'elements' => array( 1, 2, 5 ), 'image' => DECK_GENERATOR_URL . 'assets/img/gokais/Gokai03.png' ),
  );
}

/**
 * Get spells
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_spells() {
  return array(
    0  => null,
    1  => array( 'id' => 1,  'name' => 'Hechizo 1',  'category' => 1, 'gods' => array( 1, 5 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard01.png' ),
    2  => array( 'id' => 2,  'name' => 'Hechizo 2',  'category' => 2, 'gods' => array( 2, 4 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard02.png' ),
    3  => array( 'id' => 3,  'name' => 'Hechizo 3',  'category' => 3, 'gods' => array( 3, 4 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard03.png' ),
    4  => array( 'id' => 4,  'name' => 'Hechizo 4',  'category' => 1, 'gods' => array( 1, 3 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard01.png' ),
    5  => array( 'id' => 5,  'name' => 'Hechizo 5',  'category' => 2, 'gods' => array( 2, 5 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard02.png' ),
    6  => array( 'id' => 6,  'name' => 'Hechizo 6',  'category' => 3, 'gods' => array( 1, 2 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard03.png' ),
    7  => array( 'id' => 7,  'name' => 'Hechizo 7',  'category' => 1, 'gods' => array( 3, 4 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard01.png' ),
    8  => array( 'id' => 8,  'name' => 'Hechizo 8',  'category' => 2, 'gods' => array( 4, 5 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard02.png' ),
    9  => array( 'id' => 9,  'name' => 'Hechizo 9',  'category' => 3, 'gods' => array( 1, 5 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard03.png' ),
    10 => array( 'id' => 10, 'name' => 'Hechizo 10', 'category' => 1, 'gods' => array( 2, 3 ), 'image' => DECK_GENERATOR_URL . 'assets/img/spells/SpellCard01.png' ),
  );
}

/**
 * Get gods
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_gods() {
  return array(
    0 => null,
    1 => array( 'id' => 1, 'name' => 'Aerasu',   'image' => DECK_GENERATOR_URL . 'assets/img/gods/Aerasu.png' ),
    2 => array( 'id' => 2, 'name' => 'Akuoi',    'image' => DECK_GENERATOR_URL . 'assets/img/gods/Akuoi.png' ),
    3 => array( 'id' => 3, 'name' => 'Igtsune',  'image' => DECK_GENERATOR_URL . 'assets/img/gods/Igtsune.png' ),
    4 => array( 'id' => 4, 'name' => 'Inami',    'image' => DECK_GENERATOR_URL . 'assets/img/gods/Inami.png' ),
    5 => array( 'id' => 5, 'name' => 'Terabuto', 'image' => DECK_GENERATOR_URL . 'assets/img/gods/Terabuto.png' ),
  );
}

/**
 * Get charms
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_charms() {
  return array(
    0  => null,
    1  => array( 'id' => 1,  'name' => 'Amuleto 1',  'gods' => array( 1 ) ),
    2  => array( 'id' => 2,  'name' => 'Amuleto 2',  'gods' => array( 2 ) ),
    3  => array( 'id' => 3,  'name' => 'Amuleto 3',  'gods' => array( 3 ) ),
    4  => array( 'id' => 4,  'name' => 'Amuleto 4',  'gods' => array( 4 ) ),
    5  => array( 'id' => 5,  'name' => 'Amuleto 5',  'gods' => array( 5 ) ),
    6  => array( 'id' => 6,  'name' => 'Amuleto 6',  'gods' => array( 1, 2 ) ),
    7  => array( 'id' => 7,  'name' => 'Amuleto 7',  'gods' => array( 2, 3 ) ),
    8  => array( 'id' => 8,  'name' => 'Amuleto 8',  'gods' => array( 3, 4 ) ),
    9  => array( 'id' => 9,  'name' => 'Amuleto 9',  'gods' => array( 4, 5 ) ),
    10 => array( 'id' => 10, 'name' => 'Amuleto 10', 'gods' => array( 1, 5 ) ),
  );
}

/**
 * Get tokens
 *
 * @since 1.0.0
 * @return array
 */
function deck_generator_get_tokens() {
  return array(
    0  => null,
    1  => array( 'id' => 1,  'name' => 'Ficha 1',  'gods' => array( 1 ) ),
    2  => array( 'id' => 2,  'name' => 'Ficha 2',  'gods' => array( 2 ) ),
    3  => array( 'id' => 3,  'name' => 'Ficha 3',  'gods' => array( 3 ) ),
    4  => array( 'id' => 4,  'name' => 'Ficha 4',  'gods' => array( 4 ) ),
    5  => array( 'id' => 5,  'name' => 'Ficha 5',  'gods' => array( 5 ) ),
    6  => array( 'id' => 6,  'name' => 'Ficha 6',  'gods' => array( 1, 2 ) ),
    7  => array( 'id' => 7,  'name' => 'Ficha 7',  'gods' => array( 2, 3 ) ),
    8  => array( 'id' => 8,  'name' => 'Ficha 8',  'gods' => array( 3, 4 ) ),
    9  => array( 'id' => 9,  'name' => 'Ficha 9',  'gods' => array( 4, 5 ) ),
    10 => array( 'id' => 10, 'name' => 'Ficha 10', 'gods' => array( 1, 5 ) ),
  );
}
