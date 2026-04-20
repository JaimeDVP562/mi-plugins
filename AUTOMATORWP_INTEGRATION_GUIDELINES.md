AutomatorWP Integration Guidelines (plantilla y guía extendida)

Resumen y objetivo
------------------
Esta guía combina la plantilla de integración estándar de AutomatorWP con las instrucciones iniciales proporcionadas en el PDF "Información sobre AutomatorWP". Su objetivo es servir como referencia única para crear, revisar y auditar integraciones (plugins/add-ons) siguiendo las convenciones del equipo.

Estructura y archivos principales
--------------------------------
Cada integración debe seguir la siguiente estructura mínima:

- `automatorwp-<slug>.php` — archivo bootstrap del plugin (define constantes VER/FILE/DIR/URL, incluye `includes/*.php` y registra la integración con `automatorwp_register_integration()`).
- `assets/` — CSS, JS e imágenes (icono opcional durante el desarrollo).
- `includes/` — código principal de la integración:
  - `admin.php` — ajustes (API keys, token, client_id/secret). No todas las integraciones lo requieren.
  - `ajax-functions.php` — AJAX handlers (`wp_ajax_*`) para verificar/autorizar.
  - `functions.php` — helpers, wrappers de API (`get_settings()`, `update_settings()`), peticiones HTTP y manejo de errores.
  - `scripts.php` — registros y colas de scripts/estilos (solo en páginas necesarias).
  - `tags.php` — definición de tags para usar en plantillas/logs.
  - `actions/` — cada action en su propio archivo que extiende `AutomatorWP_Integration_Action`.
  - `triggers/` — cada trigger en su propio archivo que extiende `AutomatorWP_Integration_Trigger`.

Conceptos importantes
--------------------
- API Secret y Token: la mayoría de integraciones con plataformas externas requieren 2 credenciales (o 1). Nunca hardcodearlas; guardarlas en `options` con `auto_load => false` y sólo mostrar/usar cuando sea necesario. Evitar registrar secrets en logs.
- Hooks: para integraciones con otros plugins, busca los `do_action()`/`apply_filters()` que indican eventos (p. ej. `complete_course`, `order_completed`). El trigger debe engancharse a ese hook.

Ejemplo: Trigger (resumen)
-------------------------
- Clase única que extiende `AutomatorWP_Integration_Trigger`.
- Propiedades: `public $integration = '<slug>'` y `public $trigger = '<unique_name>'`.
- `register()` — llama a `automatorwp_register_trigger()` con: `integration`, `label`, `select_option`, `edit_label`, `log_label`, `action` (hook a escuchar), `function` (listener), `priority`, `accepted_args`, `options`, `tags`.
- `listener($args)` — procesa los parámetros del hook y llama a `automatorwp_trigger_event(array(...))` con `trigger`, `user_id`, `post_id`, etc.
- `user_deserves_trigger($deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation)` — filtro para validar condiciones adicionales antes de disparar.

Ejemplo: Action (resumen)
------------------------
- Clase que extiende `AutomatorWP_Integration_Action`.
- Propiedades: `public $integration` y `public $action`.
- `register()` — llama a `automatorwp_register_action()` con `integration`, `label`, `select_option`, `edit_label`, `log_label` y `options` (campos configurables por el usuario).
- `execute($action, $user_id, $action_options, $automation)` — contiene la lógica que usa funciones del plugin integrado o llamadas a su API para realizar la tarea.

Buenas prácticas de implementación
--------------------------------
- Seguridad:
  - `check_ajax_referer()` en handlers AJAX.
  - `current_user_can('manage_options')` antes de guardar credenciales.
  - Sanitizar entradas con `sanitize_text_field()`, `esc_url_raw()`, `wp_unslash()` cuando sea necesario.
  - No volcar secrets en `error_log()` ni en respuestas públicas.
  - Guardar credenciales en `options` con `auto_load => false`.

- API / HTTP:
  - Usar `wp_remote_get()` / `wp_remote_post()` y comprobar `is_wp_error()` y códigos HTTP antes de usar la respuesta.
  - Manejar y propagar errores con `WP_Error`.

- Código:
  - Evitar `include`/`require` dinámicos basados en entrada del usuario.
  - Mantener nombres de constantes y prefijos consistentes: `AUTOMATORWP_<SLUG>_DIR`, `AUTOMATORWP_<SLUG>_URL`, opciones `automatorwp_<slug>_...`.

Checklist para creación / revisión (manual)
-----------------------------------------
- [ ] Existe `automatorwp-<slug>.php` con includes correctos.
- [ ] `includes/admin.php` exige capacidades y valida entradas.
- [ ] `includes/ajax-functions.php` usa `check_ajax_referer` y `current_user_can`.
- [ ] `includes/functions.php` maneja `wp_remote_*` con `WP_Error` y no expone secrets.
- [ ] `includes/scripts.php` registra/encola solo en pantalla `automatorwp_settings` y usa `wp_localize_script` para `ajax_url` y `nonce`.
- [ ] Triggers implementan `listener()` y `user_deserves_trigger()` correctamente.
- [ ] Actions implementan `execute()` y sanitizan/escapan la salida cuando corresponda.
- [ ] No hay secrets hardcodeados en el repo.
- [ ] Assets referenciados con las constantes de la integración.

Auditoría automática sugerida
----------------------------
Puedes ejecutar comprobaciones básicas en el repo para acelerar revisiones. Ejemplos de búsquedas útiles (desde la raíz del proyecto en Windows PowerShell o WSL):

```bash
# buscar bootstraps
grep -R "automatorwp-" wp-content/plugins/automatorwp/integrations | grep ".php$"

# comprobar handlers AJAX que no usan check_ajax_referer
grep -R "add_action.*wp_ajax_" -n wp-content/plugins/automatorwp/integrations | while read l; do f=$(echo $l | cut -d: -f1); grep -n "check_ajax_referer" "$f" || echo "MISSING_NONCE: $f"; done

# buscar uso de wp_remote_ sin manejo evidente de is_wp_error
grep -R "wp_remote_\(get\|post\)" -n wp-content/plugins/automatorwp/integrations | while read l; do f=$(echo $l | cut -d: -f1); grep -n "is_wp_error" "$f" || echo "NO_ERROR_CHECK: $f"; done

# buscar posibles secrets (heurístico: cadenas largas o claves)
grep -R "\(api_key\|api_secret\|client_secret\|token\|SECRET\)" -n wp-content/plugins/automatorwp/integrations || true
```

Nota: los comandos anteriores son heurísticos. Para un escaneo profundo recomendamos usar herramientas como `grep` combinado con `phpcs`, `phpstan` o `Psalm`, y un escaneo de secretos como `git-secrets` o `truffleHog` en repositorio histórico.

Tests, CI y calidad
-------------------
- Añadir plantillas de pruebas unitarias (PHPUnit) para triggers y actions críticos.
- Añadir un workflow de CI (GitHub Actions) que ejecute `phpcs`, `phpunit` y un análisis de secretos en cada PR.

Plantillas y snippets útiles
--------------------------
- Plantilla mínima segura para un handler AJAX (en `includes/ajax-functions.php`):

```php
function my_integration_verify_ajax() {
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'no-permission', 403 );
    }

    $payload = wp_unslash( $_POST );
    $key = isset( $payload['key'] ) ? sanitize_text_field( $payload['key'] ) : '';

    // ... comprobar con API y almacenar con update_option( 'automatorwp_<slug>_settings', $data )

    wp_send_json_success( array( 'status' => 'ok' ) );
}
add_action( 'wp_ajax_my_integration_verify', 'my_integration_verify_ajax' );
```

- Plantilla mínima para `execute()` de un action:

```php
public function execute( $action, $user_id, $action_options, $automation ) {
    $id = absint( $action_options['post'] );
    if ( $id === 0 ) {
        return; // validación básica
    }

    $result = my_integration_api_call( 'do/something', array( 'id' => $id, 'user' => $user_id ) );
    if ( is_wp_error( $result ) ) {
        // log seguro opcional (no incluir secrets)
        automatorwp_add_log( $automation->id, 'error', $result->get_error_message() );
    }
}
```

Tareas y flujo recomendado para revisión y desarrollo
----------------------------------------------------
1. Clonar/copiar la integración base más cercana y renombrar (archivo principal y prefijos). Usar búsquedas y reemplazos controlados.
2. Implementar `admin.php` si la integración necesita credenciales externas.
3. Implementar triggers apuntando a los hooks del plugin objetivo o endpoints de webhooks para APIs externas.
4. Implementar actions usando funciones públicas del plugin integrado o llamadas a su API.
5. Probar manualmente triggers y actions en un entorno local con los plugins dependientes activos.
6. Añadir pruebas unitarias básicas y un CI que corra linters/tests.

Checklist rápido de auditoría (para entregar con la revisión de integración)
----------------------------------------------------------------------------
- Nombre de la integración (slug) y archivo bootstrap presentes.
- Credenciales protegidas y validadas en `admin.php`.
- AJAX handlers protegiendo con `check_ajax_referer` y capability checks.
- `wp_remote_*` con manejo de `WP_Error`.
- `listener()` y `execute()` implementados y validados.
- No secrets en código.
- Resultados de pruebas básicas (lista de triggers/actions probados y su estado).

-----
Guía actualizada: utiliza este fichero como referencia primaria para crear o auditar integraciones y acompaña las revisiones con el reporte automático que prefieras (CSV/markdown) para facilitar el seguimiento.
