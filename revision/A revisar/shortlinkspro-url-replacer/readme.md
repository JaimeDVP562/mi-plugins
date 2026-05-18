# ShortLinks Pro - URL Replacer

Add-on para ShortLinks Pro que permite reemplazar URLs dentro del contenido de posts por un shortcode asociado a un link concreto.

## Funcionalidad

Desde la pantalla de edición de un link de ShortLinks Pro se añade una caja de configuración con:

- URL(s) a reemplazar usando Select2 multiple como tags.
- Soporte para pegar varias URLs separadas por comas.
- Selección múltiple de post types donde aplicar el reemplazo.
- Botón para ejecutar el reemplazo.
- Botón de limpieza para eliminar los shortcodes generados.

## Shortcode usado

```text
[slp_url_link id="ID_LINK"]URL_ORIGINAL[/slp_url_link]