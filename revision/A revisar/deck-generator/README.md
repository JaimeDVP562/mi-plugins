# Deck Generator

Deck Generator is a WordPress admin plugin that build random decks.
You choose filters on the left panel, and the deck updates live on the right without reloading the page.

This README explains the plugin in a practical way so it is easy to maintain and extend.

## What this plugin does

- Adds a `Deck Generator` page in `wp-admin`.
- Generates a 20-card deck in real time using JavaScript.
- Keeps one god outside deck count.
- Always includes fixed element cards (2 copies per element).
- Fills remaining slots with monsters and spells.
- Shows charms and tokens in a side panel (not part of the 20-card deck count).
- Creates a shareable URL that can restore both deck snapshot and form filters.

## Current structure

- `deck-generator.php`
  - Plugin bootstrap and constants.
- `includes/admin.php`
  - Admin menu and page markup.
- `includes/scripts.php`
  - Enqueues CSS/JS and localizes `DeckData` for JavaScript.
- `includes/data.php`
  - Card pools and static data.
- `assets/js/deckgenerator-admin.js`
  - Deck generation logic, URL import/export, and rendering.
- `assets/css/deckgenerator-admin.css`
  - Admin UI styles.

## Important update notes

The plugin now uses the updated naming and model:

- `type` is now `family`.
- `amulets` is now `charms`.
- Monster `element` is now `elements` (array of 1 to 3 element IDs).
- Monsters include `level` (1, 2, 3).
- `extra` data was removed.
- Not all assets match the cards. Only the god and elements assets match, the other do not because there are still assets remaining for more cards, such as monster levels, charms, and news for both.
- Assets for charms and tokens are not ready yet.

## Deck rules implemented

- Deck limit: `20` cards.
- God: exactly `1` (outside the 20-card count).
- Elements: always `2` copies per element, fixed block.
- Monsters:
  - count options: `random (3-6)`, `3`, `4`, `5`, `6`
  - minimum expected target: 3
  - maximum copies per same monster: 3
  - filters: family, level, elements
- Spells:
  - fill all remaining deck slots
  - maximum copies per same spell: 3
  - optional filter by god affinity
- Charms and tokens:
  - not part of the 20-card deck count
  - count options: `random (0-3)`, `0`, `1`, `2`, `3`
  - optional filter by god affinity

## Admin form controls

- God
- Monsters count
- Monster families (checkboxes)
- Monster levels (checkboxes)
- Elements (checkboxes)
- Charms count
- Tokens count
- God affinity toggles for:
  - charms
  - tokens
  - spells

When no checkbox is selected in family/level/elements, that filter is treated as “any”.

## Share URL behavior

The generated URL always points to:

- `wp-admin/admin.php?page=deck-generator`

The plugin now writes list params using `[]` keys (for example `m[]=1&m[]=2`) and keeps key brackets unescaped in the final URL string.

### Snapshot params

- `g` = resolved god ID
- `c[]` = charm IDs
- `tk[]` = token IDs
- `m[]` = monster IDs
- `s[]` = spell IDs

### Form-state params

- `ng` = original god selector value
- `nm` = monsters selector value
- `nc` = charms selector value
- `nt` = tokens selector value
- `fam[]` = selected family IDs
- `lv[]` = selected level IDs
- `elem[]` = selected element IDs
- `rc=1` = god affinity for charms
- `rt=1` = god affinity for tokens
- `rs=1` = god affinity for spells

### Backward compatibility

The URL loader still accepts legacy keys from previous versions:

- `na` (old charms selector)
- `ty[]` / `ty` (old family key)
- `ra=1` (old charm affinity key)
- indexed forms like `m[0]=...` and plain repeated keys.

## Data and images

The plugin includes optional `image` fields for gods, elements, monsters, and spells.
If an image file does not exist, the UI gracefully falls back to text-only cards.
