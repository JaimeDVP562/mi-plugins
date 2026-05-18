document.addEventListener('DOMContentLoaded', () => {

  // Helpers

  /**
   * Return a random integer between two values.
   *
   * @param {number} min Minimum value.
   * @param {number} max Maximum value.
   * @returns {number} Random integer.
   */
  const randomInt = ( min, max ) => {
    return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
  };

  /**
   * Pick one random item from an array.
   *
   * @param {Array} arr Source array.
   * @returns {*} Random item.
   */
  const randomFrom = ( arr ) => {
    return arr[ Math.floor( Math.random() * arr.length ) ];
  };

  /**
   * Convert an object to an array and remove null values.
   *
   * @param {Object} obj Source object.
   * @returns {Array} Clean array.
   */
  const toArray = ( obj ) => {
    return Object.values( obj || {} ).filter( item => item !== null );
  };

  /**
   * Escape text for safe HTML rendering.
   *
   * @param {*} value Source value.
   * @returns {string} Escaped text.
   */
  const escapeHtml = ( value ) => {
    return String( value ?? '' )
      .replace( /&/g, '&amp;' )
      .replace( /</g, '&lt;' )
      .replace( />/g, '&gt;' )
      .replace( /"/g, '&quot;' )
      .replace( /'/g, '&#039;' );
  };

  /**
   * Convert a value to integer or null.
   *
   * @param {*} value Source value.
   * @returns {number|null} Parsed integer.
   */
  const toIntOrNull = ( value ) => {
    const parsed = parseInt( value, 10 );
    return Number.isInteger( parsed ) ? parsed : null;
  };

  /**
   * Draw items from a pool allowing a limited number of copies.
   *
   * @param {Array} pool Source items.
   * @param {number} n Number of items to draw.
   * @param {number} maxCopies Maximum copies per item.
   * @returns {Array} Selected items.
   */
  const drawFromPool = ( pool, n, maxCopies ) => {
    const expanded = [];

    pool.forEach( item => {
      for ( let i = 0; i < maxCopies; i++ ) {
        expanded.push( item );
      }
    } );

    for ( let i = expanded.length - 1; i > 0; i-- ) {
      const j = Math.floor( Math.random() * ( i + 1 ) );
      [ expanded[ i ], expanded[ j ] ] = [ expanded[ j ], expanded[ i ] ];
    }

    const selected = [];
    const copies = {};

    for ( const candidate of expanded ) {
      if ( selected.length >= n ) {
        break;
      }

      const count = copies[ candidate.id ] || 0;

      if ( count < maxCopies ) {
        selected.push( candidate );
        copies[ candidate.id ] = count + 1;
      }
    }

    return selected;
  };

  /**
   * Draw unique items from a pool.
   *
   * @param {Array} pool Source items.
   * @param {number} n Number of items to draw.
   * @returns {Array} Selected unique items.
   */
  const drawUniqueFromPool = ( pool, n ) => {
    const shuffled = [ ...pool ];

    for ( let i = shuffled.length - 1; i > 0; i-- ) {
      const j = Math.floor( Math.random() * ( i + 1 ) );
      [ shuffled[ i ], shuffled[ j ] ] = [ shuffled[ j ], shuffled[ i ] ];
    }

    return shuffled.slice( 0, n );
  };

  /**
   * Escape a value so it can be used in regular expressions.
   *
   * @param {string} value Source value.
   * @returns {string} Escaped value.
   */
  const escapeRegExp = ( value ) => {
    return String( value ).replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
  };

  /**
   * Normalize a query key by removing array suffixes.
   *
   * @param {string} key Query key.
   * @returns {string} Normalized key.
   */
  const normalizeParamKey = ( key ) => {
    return String( key ).replace( /\[(?:\d*)\]$/, '' );
  };

  /**
   * Read indexed values from URL params.
   *
   * @param {URLSearchParams} params URL params.
   * @param {string} baseKey Base key.
   * @returns {Array} Indexed values sorted by index.
   */
  const getIndexedParamValues = ( params, baseKey ) => {
    const pattern = new RegExp( `^${ escapeRegExp( baseKey ) }\\[(\\d+)\\]$` );
    const indexedValues = [];

    params.forEach( ( value, key ) => {
      const match = key.match( pattern );

      if ( ! match ) {
        return;
      }

      indexedValues.push( {
        index: parseInt( match[ 1 ], 10 ),
        value,
      } );
    } );

    return indexedValues
      .sort( ( left, right ) => left.index - right.index )
      .map( entry => entry.value );
  };

  /**
   * Return true when at least one key exists.
   *
   * @param {URLSearchParams} params URL params.
   * @param {Array} keys Key aliases.
   * @returns {boolean} Whether at least one key exists.
   */
  const hasParamKey = ( params, keys ) => {
    const baseKeys = [ ...new Set( keys.map( normalizeParamKey ) ) ];

    return baseKeys.some( baseKey => {
      if ( params.has( baseKey ) || params.has( `${ baseKey }[]` ) ) {
        return true;
      }

      return getIndexedParamValues( params, baseKey ).length > 0;
    } );
  };

  /**
   * Read parameter values from one or more key aliases.
   *
   * @param {URLSearchParams} params URL params.
   * @param {Array} keys Key aliases.
   * @returns {Array} Normalized values.
   */
  const getParamValues = ( params, keys ) => {
    const normalizeValues = ( values ) => values
      .filter( value => value !== null && value !== '' )
      .flatMap( value => String( value ).split( ',' ) )
      .map( value => value.trim() )
      .filter( Boolean );

    const baseKeys = [ ...new Set( keys.map( normalizeParamKey ) ) ];
    const indexedValues = baseKeys.flatMap( baseKey => getIndexedParamValues( params, baseKey ) );

    if ( indexedValues.length > 0 ) {
      return normalizeValues( indexedValues );
    }

    const repeatedBracketValues = baseKeys.flatMap( baseKey => params.getAll( `${ baseKey }[]` ) );

    if ( repeatedBracketValues.length > 0 ) {
      return normalizeValues( repeatedBracketValues );
    }

    const repeatedPlainValues = baseKeys.flatMap( baseKey => params.getAll( baseKey ) );

    if ( repeatedPlainValues.length > 0 ) {
      return normalizeValues( repeatedPlainValues );
    }

    return [];
  };

  /**
   * Append one query key/value pair.
   *
   * @param {Array} pairs Pair list.
   * @param {string} key Query key.
   * @param {*} value Query value.
   */
  const appendRawParam = ( pairs, key, value ) => {
    if ( value === null || value === undefined ) {
      return;
    }

    const safeKey = String( key );
    const safeValue = encodeURIComponent( String( value ) );

    pairs.push( `${ safeKey }=${ safeValue }` );
  };

  /**
   * Append list items using key[] format.
   *
   * @param {Array} pairs Pair list.
   * @param {string} key Base key.
   * @param {Array} values Values to append.
   */
  const appendRawList = ( pairs, key, values ) => {
    values.forEach( value => appendRawParam( pairs, `${ key }[]`, value ) );
  };

  const deckLimit = Number( DeckData.deckLimit );
  const shareBaseUrl = String( DeckData.shareBaseUrl || window.location.href.split( '?' )[ 0 ] );
  const pageSlug = String( DeckData.pageSlug || 'deck-generator' );

  let sharedDeck = null;
  let sharedModeActive = false;

  // Warnings

  /**
   * Show warning message in the admin screen.
   *
   * @param {string} message Warning text.
   */
  const showWarning = ( message ) => {
    const el = document.getElementById( 'dg-warnings' );
    if ( ! el ) {
      return;
    }

    const warning = document.createElement( 'div' );
    warning.className = 'dg-warning';
    warning.textContent = message;

    el.appendChild( warning );
    el.style.display = '';
  };

  /**
   * Clear all warning messages.
   */
  const clearWarnings = () => {
    const el = document.getElementById( 'dg-warnings' );
    if ( ! el ) {
      return;
    }

    el.innerHTML = '';
    el.style.display = 'none';
  };

  // Shared mode

  /**
   * Activate shared mode with a deck snapshot.
   *
   * @param {Object|null} deck Deck snapshot.
   */
  const setSharedDeckState = ( deck ) => {
    sharedDeck = deck;
    sharedModeActive = Boolean( deck );
  };

  /**
   * Clear shared mode state.
   */
  const clearSharedDeckState = () => {
    sharedDeck = null;
    sharedModeActive = false;
  };

  /**
   * Return active shared deck only in shared mode.
   *
   * @returns {Object|null} Active snapshot.
   */
  const getActiveSharedDeck = () => {
    return sharedModeActive ? sharedDeck : null;
  };

  /**
   * Render shared state block.
   *
   * @param {Object|null} deck Deck snapshot.
   */
  const renderSharedState = ( deck ) => {
    const container = document.getElementById( 'dg-shared-state' );
    const content = document.getElementById( 'dg-shared-state-content' );

    if ( ! container || ! content ) {
      return;
    }

    if ( ! deck || ! deck.god ) {
      container.style.display = 'none';
      content.innerHTML = '';
      return;
    }

    const ids = ( items ) => items.length
      ? items.map( item => escapeHtml( item.id ) ).join( ', ' )
      : '-';

    content.innerHTML = [
      `<p class="dg-shared-state__line"><strong>Dios:</strong> ${ escapeHtml( deck.god.id ) }</p>`,
      `<p class="dg-shared-state__line"><strong>Amuletos:</strong> ${ ids( deck.charms ) }</p>`,
      `<p class="dg-shared-state__line"><strong>Fichas:</strong> ${ ids( deck.tokens ) }</p>`,
      `<p class="dg-shared-state__line"><strong>Monstruos:</strong> ${ ids( deck.monsters ) }</p>`,
      `<p class="dg-shared-state__line"><strong>Hechizos:</strong> ${ ids( deck.spells ) }</p>`,
    ].join( '' );

    container.style.display = '';
  };

  /**
   * Hide shared state block.
   */
  const hideSharedState = () => {
    const container = document.getElementById( 'dg-shared-state' );
    const content = document.getElementById( 'dg-shared-state-content' );

    if ( ! container || ! content ) {
      return;
    }

    container.style.display = 'none';
    content.innerHTML = '';
  };

  /**
   * Leave shared mode.
   */
  const leaveSharedMode = () => {
    if ( ! sharedModeActive ) {
      return;
    }

    clearSharedDeckState();
    hideSharedState();
  };

  // Form

  /**
   * Fill god select with available options.
   */
  const loadGodOptions = () => {
    const select = document.getElementById( 'dg-god' );
    if ( ! select ) {
      return;
    }

    let options = '<option value="">Aleatorio</option>';

    Object.values( DeckData.gods ).forEach( god => {
      if ( god === null ) {
        return;
      }

      options += `<option value="${ escapeHtml( god.id ) }">${ escapeHtml( god.name ) }</option>`;
    } );

    select.innerHTML = options;
  };

  /**
   * Create checkboxes from item list.
   *
   * @param {string} containerId Container id.
   * @param {Object} items Item map.
   */
  const loadCheckboxes = ( containerId, items ) => {
    const container = document.getElementById( containerId );
    if ( ! container ) {
      return;
    }

    const fragment = document.createDocumentFragment();

    Object.values( items ).forEach( item => {
      if ( item === null ) {
        return;
      }

      const label = document.createElement( 'label' );
      const input = document.createElement( 'input' );

      input.type = 'checkbox';
      input.value = String( item.id );

      label.appendChild( input );
      label.append( ` ${ item.name }` );

      fragment.appendChild( label );
    } );

    container.innerHTML = '';
    container.appendChild( fragment );
  };

  /**
   * Read current form values.
   *
   * @returns {Object} Form values.
   */
  const getFormValues = () => {
    const godValue = document.getElementById( 'dg-god' ).value;

    return {
      god:       toIntOrNull( godValue ),
      godState:  godValue,
      monsters:  document.getElementById( 'dg-monsters' ).value,
      charms:    document.getElementById( 'dg-charms' ).value,
      tokens:    document.getElementById( 'dg-tokens' ).value,
      families:  Array.from( document.querySelectorAll( '#dg-families input:checked' ) )
                      .map( el => parseInt( el.value, 10 ) )
                      .filter( n => Number.isInteger( n ) ),
      levels:    Array.from( document.querySelectorAll( '#dg-levels input:checked' ) )
                      .map( el => parseInt( el.value, 10 ) )
                      .filter( n => Number.isInteger( n ) ),
      elements:  Array.from( document.querySelectorAll( '#dg-elements input:checked' ) )
                      .map( el => parseInt( el.value, 10 ) )
                      .filter( n => Number.isInteger( n ) ),
      relCharms: document.getElementById( 'dg-rel-charms' ).checked,
      relTokens: document.getElementById( 'dg-rel-tokens' ).checked,
      relSpells: document.getElementById( 'dg-rel-spells' ).checked,
    };
  };

  loadGodOptions();
  loadCheckboxes( 'dg-families', DeckData.families );
  loadCheckboxes( 'dg-levels', DeckData.levels );
  loadCheckboxes( 'dg-elements', DeckData.elements );

  // Selectors

  /**
   * Select one god by form value or random.
   *
   * @param {number|null} godId Selected god id.
   * @returns {Object|null} Selected god.
   */
  const selectGod = ( godId ) => {
    if ( godId ) {
      return DeckData.gods[ godId ] || null;
    }

    return randomFrom( toArray( DeckData.gods ) );
  };

  /**
   * Build fixed element cards (2 copies per element).
   *
   * @returns {Array} Fixed cards.
   */
  const selectElements = () => {
    const selected = [];

    toArray( DeckData.elements ).forEach( element => {
      selected.push( {
        id: element.id,
        name: element.name,
        image: element.image || null,
        isElement: true,
      } );

      selected.push( {
        id: element.id,
        name: element.name,
        image: element.image || null,
        isElement: true,
      } );
    } );

    return selected;
  };

  /**
   * Return true when one selected element matches monster elements.
   *
   * @param {Object} monster Monster entry.
   * @param {Array} selectedElements Selected element ids.
   * @returns {boolean} Whether the monster matches.
   */
  const monsterMatchesElements = ( monster, selectedElements ) => {
    if ( selectedElements.length === 0 ) {
      return true;
    }

    const monsterElements = Array.isArray( monster.elements ) ? monster.elements : [];
    return selectedElements.some( elementId => monsterElements.includes( elementId ) );
  };

  /**
   * Select monsters from available pool.
   *
   * @param {Object} values Form values.
   * @param {number} freeSlots Free card slots.
   * @returns {Array} Selected monsters.
   */
  const selectMonsters = ( values, freeSlots ) => {
    let n = values.monsters === 'random'
      ? randomInt( 3, 6 )
      : parseInt( values.monsters, 10 );

    n = Math.min( n, freeSlots );

    let pool = toArray( DeckData.monsters );

    if ( values.families.length > 0 ) {
      pool = pool.filter( monster => values.families.includes( monster.family ) );
    }

    if ( values.levels.length > 0 ) {
      pool = pool.filter( monster => values.levels.includes( monster.level ) );
    }

    if ( values.elements.length > 0 ) {
      pool = pool.filter( monster => monsterMatchesElements( monster, values.elements ) );
    }

    if ( pool.length === 0 ) {
      showWarning( 'No hay monstruos con los filtros actuales. Se usa el pool completo.' );
      pool = toArray( DeckData.monsters );
    }

    const selected = drawFromPool( pool, n, 3 );

    if ( selected.length < Math.min( 3, freeSlots ) ) {
      showWarning( 'El mazo no pudo alcanzar el minimo de 3 monstruos.' );
    }

    return selected;
  };

  /**
   * Select spells for the remaining slots.
   *
   * @param {number} slots Free slots.
   * @param {Object} values Form values.
   * @param {Object|null} god Selected god.
   * @returns {Array} Selected spells.
   */
  const selectSpells = ( slots, values, god ) => {
    if ( slots <= 0 ) {
      return [];
    }

    let pool = toArray( DeckData.spells );

    if ( values.relSpells && god ) {
      pool = pool.filter( spell => Array.isArray( spell.gods ) && spell.gods.includes( god.id ) );
    }

    if ( pool.length === 0 ) {
      showWarning( 'No hay hechizos afines al dios. Se usa el pool completo.' );
      pool = toArray( DeckData.spells );
    }

    const selected = drawFromPool( pool, slots, 3 );

    if ( selected.length < slots ) {
      showWarning( `Faltan ${ slots - selected.length } hechizos para completar el mazo.` );
    }

    return selected;
  };

  /**
   * Select charms for sidebar.
   *
   * @param {Object} values Form values.
   * @param {Object|null} god Selected god.
   * @returns {Array} Selected charms.
   */
  const selectCharms = ( values, god ) => {
    const n = values.charms === 'random'
      ? randomInt( 0, 3 )
      : parseInt( values.charms, 10 );

    if ( n === 0 ) {
      return [];
    }

    let pool = toArray( DeckData.charms );

    if ( values.relCharms && god ) {
      pool = pool.filter( charm => Array.isArray( charm.gods ) && charm.gods.includes( god.id ) );
    }

    if ( pool.length === 0 ) {
      showWarning( 'No hay amuletos disponibles para este dios.' );
      return [];
    }

    const selected = drawUniqueFromPool( pool, n );

    if ( selected.length < n ) {
      showWarning( `Solo se pudieron asignar ${ selected.length } de ${ n } amuletos solicitados.` );
    }

    return selected;
  };

  /**
   * Select tokens for sidebar.
   *
   * @param {Object} values Form values.
   * @param {Object|null} god Selected god.
   * @returns {Array} Selected tokens.
   */
  const selectTokens = ( values, god ) => {
    const n = values.tokens === 'random'
      ? randomInt( 0, 3 )
      : parseInt( values.tokens, 10 );

    if ( n === 0 ) {
      return [];
    }

    let pool = toArray( DeckData.tokens );

    if ( values.relTokens && god ) {
      pool = pool.filter( token => Array.isArray( token.gods ) && token.gods.includes( god.id ) );
    }

    if ( pool.length === 0 ) {
      showWarning( 'No hay fichas disponibles para este dios.' );
      return [];
    }

    const selected = drawUniqueFromPool( pool, n );

    if ( selected.length < n ) {
      showWarning( `Solo se pudieron asignar ${ selected.length } de ${ n } fichas solicitadas.` );
    }

    return selected;
  };

  // Generator

  /**
   * Build deck object from values.
   *
   * @param {Object} values Form values.
   * @param {Object|null} preloaded Preloaded snapshot.
   * @returns {Object} Deck data.
   */
  const buildDeck = ( values, preloaded = null ) => {
    const god = preloaded ? preloaded.god : selectGod( values.god );
    const fixedElements = selectElements();
    const variableSlots = deckLimit - fixedElements.length;
    const monsters = preloaded ? preloaded.monsters : selectMonsters( values, variableSlots );
    const spellSlots = variableSlots - monsters.length;
    const spells = preloaded ? preloaded.spells : selectSpells( spellSlots, values, god );
    const charms = preloaded ? preloaded.charms : selectCharms( values, god );
    const tokens = preloaded ? preloaded.tokens : selectTokens( values, god );
    const totalCards = fixedElements.length + monsters.length + spells.length;

    if ( totalCards !== deckLimit ) {
      showWarning( `El mazo tiene ${ totalCards } cartas en lugar de ${ deckLimit }.` );
    }

    return { god, fixedElements, monsters, spells, charms, tokens, totalCards };
  };

  /**
   * Generate deck and update outputs.
   *
   * @param {Object|null} preloaded Preloaded snapshot.
   */
  const generate = ( preloaded = null ) => {
    clearWarnings();

    const values = getFormValues();
    const deckSnapshot = preloaded || getActiveSharedDeck();
    const deck = buildDeck( values, deckSnapshot );

    renderResult( deck );
    renderSummary( deck );
    renderShareUrl( deck );
  };

  /**
   * Return list with card kind attached.
   *
   * @param {Array} items Card list.
   * @param {string} kind Card kind.
   * @returns {Array} Card list with kind.
   */
  const withCardKind = ( items, kind ) => {
    return items.map( item => ( { ...item, kind } ) );
  };

  /**
   * Resolve card kind.
   *
   * @param {Object} card Card entry.
   * @returns {string} Card kind.
   */
  const getCardKind = ( card ) => {
    if ( card.kind ) {
      return card.kind;
    }

    if ( card.isElement ) {
      return 'element';
    }

    return 'card';
  };

  /**
   * Return descending ID list as string.
   *
   * @param {Array} items Item list.
   * @returns {string} ID list.
   */
  const sortIdsDesc = ( items ) => {
    return items.length
      ? items
          .map( item => item.id )
          .sort( ( left, right ) => right - left )
          .join( ', ' )
      : '-';
  };

  // Render

  /**
   * Return media markup for cards.
   *
   * @param {Object} item Card item.
   * @returns {string} Media HTML.
   */
  const renderCardMedia = ( item ) => {
    if ( ! item || ! item.image ) {
      return '<span class="dg-card__media dg-card__media--fallback" aria-hidden="true"></span>';
    }

    const src = escapeHtml( item.image );
    const alt = escapeHtml( item.name || `Carta ${ item.id }` );

    return `<span class="dg-card__media"><img src="${ src }" alt="${ alt }" loading="lazy"></span>`;
  };

  /**
   * Remove image wrappers when media fails to load.
   *
   * @param {Element} root Root element.
   */
  const bindImageFallback = ( root ) => {
    if ( ! root ) {
      return;
    }

    root.querySelectorAll( '.dg-card__media img' ).forEach( img => {
      img.addEventListener( 'error', () => {
        const media = img.closest( '.dg-card__media' );

        if ( media ) {
          media.classList.add( 'dg-card__media--fallback' );
          media.innerHTML = '';
        }
      }, { once: true } );
    } );
  };

  /**
   * Render main result area.
   *
   * @param {Object|null} deck Deck data.
   */
  const renderResult = ( deck ) => {
    const container = document.getElementById( 'deck-generator-result' );
    if ( ! container ) {
      return;
    }

    if ( ! deck ) {
      container.innerHTML = '<div class="dg-placeholder">Mazo</div>';
      return;
    }

    container.innerHTML = `
      <div class="dg-result-layout">
        ${ renderSidebar( deck ) }
        ${ renderDeckGrid( deck ) }
      </div>
    `;

    bindImageFallback( container );
  };

  /**
   * Render sidebar (god, charms, tokens).
   *
   * @param {Object} deck Deck data.
   * @returns {string} Sidebar HTML.
   */
  const renderSidebar = ( deck ) => {
    return `
      <div class="dg-sidebar">
        ${ renderGodCard( deck.god ) }
        ${ renderSidebarSlots( deck.charms, 'dg-charms-slots', 'Amuletos' ) }
        ${ renderSidebarSlots( deck.tokens, 'dg-tokens-slots', 'Fichas' ) }
      </div>
    `;
  };

  /**
   * Render god card.
   *
   * @param {Object|null} god Selected god.
   * @returns {string} God card HTML.
   */
  const renderGodCard = ( god ) => {
    if ( ! god ) {
      return '<div class="dg-card dg-card--empty">Sin dios</div>';
    }

    return `
      <div class="dg-card dg-card--god dg-card--image-only" data-id="${ god.id }">
        ${ renderCardMedia( god ) }
      </div>
    `;
  };

  /**
   * Render three fixed slots for sidebar section.
   *
   * @param {Array} items Card list.
   * @param {string} containerId Container id.
   * @param {string} label Section label.
   * @returns {string} Section HTML.
   */
  const renderSidebarSlots = ( items, containerId, label ) => {
    let slots = `<span class="dg-sidebar__section-label">${ escapeHtml( label ) }</span>`;

    for ( let i = 0; i < 3; i++ ) {
      const item = items[ i ] || null;

      slots += item
        ? `<div class="dg-card dg-card--side" data-id="${ item.id }">
            ${ renderCardMedia( item ) }
            <span class="dg-card__name">${ escapeHtml( item.name ) }</span>
            <span class="dg-card__id">${ item.id }</span>
          </div>`
        : '<div class="dg-card dg-card--empty"></div>';
    }

    return `<div class="dg-sidebar__section" id="${ containerId }">${ slots }</div>`;
  };

  /**
   * Render grouped deck cards.
   *
   * @param {Object} deck Deck data.
   * @returns {string} Deck HTML.
   */
  const renderDeckGrid = ( deck ) => {
    const grouped = groupCards( [
      ...withCardKind( deck.fixedElements, 'element' ),
      ...withCardKind( deck.monsters, 'monster' ),
      ...withCardKind( deck.spells, 'spell' ),
    ] );

    const cards = grouped.map( renderCard ).join( '' );

    return `
      <div class="dg-deck">
        <div class="dg-deck__grid">${ cards }</div>
        <div class="dg-deck__total">Total: ${ deck.totalCards } / ${ deckLimit } cartas</div>
      </div>
    `;
  };

  /**
   * Group equal cards and count copies.
   *
   * @param {Array} cards Card list.
   * @returns {Array} Grouped entries.
   */
  const groupCards = ( cards ) => {
    const map = {};

    cards.forEach( card => {
      const kind = getCardKind( card );
      const key = `${ kind }-${ card.id }`;

      if ( map[ key ] ) {
        map[ key ].copies++;
      } else {
        map[ key ] = { ...card, kind, copies: 1 };
      }
    } );

    return Object.values( map );
  };

  /**
   * Render one grouped deck card.
   *
   * @param {Object} entry Card entry.
   * @returns {string} Card HTML.
   */
  const renderCard = ( entry ) => {
    const kind = getCardKind( entry );
    const badge = entry.copies > 1 ? `<span class="dg-card__badge">x${ entry.copies }</span>` : '';
    const typeClass = kind === 'element'
      ? 'dg-card--element'
      : `dg-card--normal dg-card--${ kind }`;

    return `
      <div class="dg-card dg-card--image-only ${ typeClass }" data-id="${ entry.id }" data-kind="${ kind }">
        ${ badge }
        ${ renderCardMedia( entry ) }
      </div>
    `;
  };

  /**
   * Render summary textarea.
   *
   * @param {Object} deck Deck data.
   */
  const renderSummary = ( deck ) => {
    const summary = document.getElementById( 'dg-summary' );
    if ( ! summary ) {
      return;
    }

    const elementIds = [ ...new Set( deck.fixedElements.map( element => element.id ) ) ]
      .sort( ( left, right ) => right - left );

    summary.value = [
      `Dios: ${ deck.god ? deck.god.id : '-' }`,
      `Amuletos: ${ sortIdsDesc( deck.charms ) }`,
      `Fichas: ${ sortIdsDesc( deck.tokens ) }`,
      `Elementos: ${ elementIds.length ? elementIds.join( ', ' ) : '-' }`,
      `Monstruos: ${ sortIdsDesc( deck.monsters ) }`,
      `Hechizos: ${ sortIdsDesc( deck.spells ) }`,
    ].join( '\n' );
  };

  // Share URL

  /**
   * Build and render shareable URL.
   *
   * @param {Object|null} deck Deck data.
   */
  const renderShareUrl = ( deck ) => {
    const output = document.getElementById( 'dg-share-url' );
    if ( ! output ) {
      return;
    }

    if ( ! deck || ! deck.god ) {
      output.value = '';
      return;
    }

    const formValues = getFormValues();
    const pairs = [];

    appendRawParam( pairs, 'page', pageSlug );
    appendRawParam( pairs, 'g', deck.god.id );
    appendRawParam( pairs, 'ng', formValues.godState );

    appendRawList( pairs, 'c', deck.charms.map( item => item.id ) );
    appendRawList( pairs, 'tk', deck.tokens.map( item => item.id ) );
    appendRawList( pairs, 'm', deck.monsters.map( item => item.id ) );
    appendRawList( pairs, 's', deck.spells.map( item => item.id ) );

    appendRawParam( pairs, 'nm', formValues.monsters );
    appendRawParam( pairs, 'nc', formValues.charms );
    appendRawParam( pairs, 'nt', formValues.tokens );

    appendRawList( pairs, 'fam', formValues.families );
    appendRawList( pairs, 'lv', formValues.levels );
    appendRawList( pairs, 'elem', formValues.elements );

    if ( formValues.relCharms ) {
      appendRawParam( pairs, 'rc', '1' );
    }

    if ( formValues.relTokens ) {
      appendRawParam( pairs, 'rt', '1' );
    }

    if ( formValues.relSpells ) {
      appendRawParam( pairs, 'rs', '1' );
    }

    const rawQuery = pairs.join( '&' );

    try {
      const url = new URL( shareBaseUrl );
      url.search = rawQuery;
      output.value = url.toString();
    } catch ( error ) {
      output.value = `${ shareBaseUrl }?${ rawQuery }`;
    }
  };

  /**
   * Load deck and form state from URL.
   *
   * @returns {Object|null} URL state.
   */
  const loadFromUrl = () => {
    const params = new URLSearchParams( window.location.search );
    const urlWarnings = [];

    const hasSharedParams = [
      [ 'g' ],
      [ 'ng' ],
      [ 'nm' ],
      [ 'nc', 'na' ],
      [ 'nt' ],
      [ 'fam[]', 'fam', 'ty[]', 'ty' ],
      [ 'lv[]', 'lv' ],
      [ 'elem[]', 'elem' ],
      [ 'rc', 'ra' ],
      [ 'rt' ],
      [ 'rs' ],
      [ 'm[]', 'm' ],
      [ 's[]', 's' ],
      [ 'c[]', 'c' ],
      [ 'tk[]', 'tk' ],
    ].some( keys => hasParamKey( params, keys ) );

    if ( ! hasSharedParams ) {
      return null;
    }

    const godId = toIntOrNull( params.get( 'g' ) );
    const god = godId ? DeckData.gods[ godId ] || null : null;

    const resolveNumberList = ( keys ) => {
      return getParamValues( params, keys )
        .map( value => parseInt( value, 10 ) )
        .filter( n => Number.isInteger( n ) );
    };

    const resolveIds = ( keys, source, label ) => {
      const ids = resolveNumberList( keys );
      const items = ids
        .map( id => source[ id ] || null )
        .filter( item => item !== null );

      if ( items.length < ids.length ) {
        const invalidCount = ids.length - items.length;
        urlWarnings.push( `Se ignoraron ${ invalidCount } ${ label } invalidos del enlace compartido.` );
      }

      return items;
    };

    const monsters = resolveIds( [ 'm[]', 'm' ], DeckData.monsters, 'ids de monstruo' );
    const spells = resolveIds( [ 's[]', 's' ], DeckData.spells, 'ids de hechizo' );
    const charms = resolveIds( [ 'c[]', 'c' ], DeckData.charms, 'ids de amuleto' );
    const tokens = resolveIds( [ 'tk[]', 'tk' ], DeckData.tokens, 'ids de ficha' );

    const hasDeckSnapshotParams = [
      [ 'm[]', 'm' ],
      [ 's[]', 's' ],
      [ 'c[]', 'c' ],
      [ 'tk[]', 'tk' ],
    ].some( keys => hasParamKey( params, keys ) );

    if ( hasDeckSnapshotParams && ! god ) {
      urlWarnings.push( 'No se pudo cargar el dios del enlace compartido.' );
    }

    if ( hasDeckSnapshotParams && god && monsters.length === 0 && spells.length === 0 && charms.length === 0 && tokens.length === 0 ) {
      urlWarnings.push( 'No se pudieron reconstruir las cartas del enlace compartido.' );
    }

    const deck = god && hasDeckSnapshotParams
      ? {
          god,
          charms,
          tokens,
          monsters,
          spells,
        }
      : null;

    const selectedGodValue = params.has( 'ng' ) ? params.get( 'ng' ) : null;
    const selectedGodId = selectedGodValue === null ? null : toIntOrNull( selectedGodValue );

    const formState = {
      godId: selectedGodValue !== null ? selectedGodId : ( god ? god.id : null ),
      monsters: params.get( 'nm' ) || 'random',
      charms: params.get( 'nc' ) || params.get( 'na' ) || 'random',
      tokens: params.get( 'nt' ) || 'random',
      families: resolveNumberList( [ 'fam[]', 'fam', 'ty[]', 'ty' ] ),
      levels: resolveNumberList( [ 'lv[]', 'lv' ] ),
      elements: resolveNumberList( [ 'elem[]', 'elem' ] ),
      relCharms: params.get( 'rc' ) === '1' || params.get( 'ra' ) === '1',
      relTokens: params.get( 'rt' ) === '1',
      relSpells: params.get( 'rs' ) === '1',
    };

    return {
      deck,
      formState,
      warnings: urlWarnings,
    };
  };

  /**
   * Apply URL form state to controls.
   *
   * @param {Object} formState Form state.
   */
  const syncFormFromUrl = ( formState ) => {
    if ( ! formState ) {
      return;
    }

    const selectedFamilies = new Set( formState.families.map( value => String( value ) ) );
    const selectedLevels = new Set( formState.levels.map( value => String( value ) ) );
    const selectedElements = new Set( formState.elements.map( value => String( value ) ) );

    const setSelectValue = ( id, value, fallback ) => {
      const select = document.getElementById( id );
      if ( ! select ) {
        return;
      }

      const requestedValue = String( value ?? fallback );
      const hasOption = Array.from( select.options )
        .some( option => option.value === requestedValue );

      select.value = hasOption ? requestedValue : String( fallback );
    };

    setSelectValue( 'dg-god', formState.godId || '', '' );
    setSelectValue( 'dg-monsters', formState.monsters, 'random' );
    setSelectValue( 'dg-charms', formState.charms, 'random' );
    setSelectValue( 'dg-tokens', formState.tokens, 'random' );

    document.getElementById( 'dg-rel-charms' ).checked = formState.relCharms;
    document.getElementById( 'dg-rel-tokens' ).checked = formState.relTokens;
    document.getElementById( 'dg-rel-spells' ).checked = formState.relSpells;

    document.querySelectorAll( '#dg-families input' ).forEach( input => {
      input.checked = selectedFamilies.has( input.value );
    } );

    document.querySelectorAll( '#dg-levels input' ).forEach( input => {
      input.checked = selectedLevels.has( input.value );
    } );

    document.querySelectorAll( '#dg-elements input' ).forEach( input => {
      input.checked = selectedElements.has( input.value );
    } );
  };

  /**
   * Check whether all controls are ready.
   *
   * @returns {boolean} Ready state.
   */
  const areSharedInputsReady = () => {
    const godsReady = document.getElementById( 'dg-god' ).options.length === toArray( DeckData.gods ).length + 1;
    const familiesReady = document.querySelectorAll( '#dg-families input' ).length === toArray( DeckData.families ).length;
    const levelsReady = document.querySelectorAll( '#dg-levels input' ).length === toArray( DeckData.levels ).length;
    const elementsReady = document.querySelectorAll( '#dg-elements input' ).length === toArray( DeckData.elements ).length;

    return godsReady && familiesReady && levelsReady && elementsReady;
  };

  /**
   * Bind form listeners.
   */
  const bindFormListeners = () => {
    document.getElementById( 'deck-generator-form' )
      .addEventListener( 'change', () => {
        leaveSharedMode();
        generate();
      } );

    document.getElementById( 'dg-regenerate' )
      .addEventListener( 'click', () => {
        leaveSharedMode();
        generate();
      } );
  };

  /**
   * Copy current share URL to clipboard.
   */
  const bindCopyButton = () => {
    const button = document.getElementById( 'dg-copy-url' );
    const input = document.getElementById( 'dg-share-url' );

    if ( ! button || ! input ) {
      return;
    }

    button.addEventListener( 'click', async () => {
      const value = input.value;

      if ( ! value ) {
        return;
      }

      let copied = false;

      if ( navigator.clipboard && window.isSecureContext ) {
        try {
          await navigator.clipboard.writeText( value );
          copied = true;
        } catch ( error ) {
          copied = false;
        }
      }

      if ( ! copied ) {
        input.focus();
        input.select();

        try {
          copied = document.execCommand( 'copy' );
        } catch ( error ) {
          copied = false;
        }
      }

      if ( copied ) {
        const original = button.textContent;
        button.textContent = 'Copiado';
        setTimeout( () => {
          button.textContent = original;
        }, 1800 );
      }
    } );
  };

  /**
   * Initialize app.
   *
   * @param {Object|null} urlData URL state.
   * @param {number} retries Remaining retries.
   */
  const initializeApp = ( urlData, retries = 10 ) => {
    if ( urlData && ! areSharedInputsReady() && retries > 0 ) {
      setTimeout( () => initializeApp( urlData, retries - 1 ), 0 );
      return;
    }

    if ( urlData ) {
      syncFormFromUrl( urlData.formState );
      setSharedDeckState( urlData.deck || null );
      renderSharedState( urlData.deck || null );
      generate( urlData.deck || null );

      ( urlData.warnings || [] ).forEach( showWarning );
    } else {
      clearSharedDeckState();
      hideSharedState();
      generate();
    }

    bindFormListeners();
    bindCopyButton();
  };

  // Init

  const urlData = loadFromUrl();
  initializeApp( urlData );

} );
