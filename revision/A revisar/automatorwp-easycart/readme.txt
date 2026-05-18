# AutomatorWP - EasyCart Integration Plugin Structure

Este documento describe la estructura y las buenas prácticas del plugin **automatorwp-easycart**, así como comandos útiles para depurar hooks en WordPress.

Estructura de carpetas

automatorwp-easycart/
├── automatorwp-easycart.php       # Archivo principal que define el plugin
├── includes/
│   ├── triggers/                  # Triggers (un archivo .php por trigger)
│   │   └── cart-abandoned.php     # Ejemplo: trigger para carritos abandonados
│   ├── actions/                   # Actions (un archivo .php por action)
│   │   └── send-abandoned-email.php
│   └── functions.php              # Funciones auxiliares para triggers/actions
└── languages/                     # Archivos de traducción (.pot, .mo)


Triggers

Cada trigger vive en `includes/triggers/` y extiende de `AutomatorWP_Integration_Trigger`. Debe implementar al menos:

1. **public function register()**

   * Usa `automatorwp_register_trigger()` (heredada de `/automatorwp/includes/triggers.php`).
   * Define `integration`, `trigger`, `label`, `action (hook)`, `function`, `accepted_args`, `tags` y `types`.

2. **public function listener( ... )**

   * Recibe los argumentos del hook disparado por EasyCart.
   * Termina llamando a:

     ```php
     automatorwp_trigger_event( array(
         'trigger'    => $this->trigger,    // nombre del trigger
         'user_id'    => $user_id,          // ID de usuario de WP
         'session_id' => $session_id,       // p.ej. session de carrito
         // otras variables necesarias...
     ) );
     ```


---

Actions

Cada action vive en `includes/actions/` y extiende de `AutomatorWP_Integration_Action`. Debe incluir:

1. **public function register()**

   * Usa `automatorwp_register_action()` (heredada de `/automatorwp/includes/actions.php`).

2. **public function listener( \$event\_data )**

   * Recibe datos del trigger y ejecuta la lógica de la acción (enviar email, crear pedido, etc.).


Funciones auxiliares

En `includes/functions.php` define helpers comunes. Ejemplo:


<?php
if ( ! defined( 'ABSPATH' ) ) exit;


Obtiene el user_id desde datos de EasyCart o current_user

function automatorwp_easycart_get_user_id( $order_data ) {
    $user_id = get_current_user_id();
    if ( 0 === $user_id && ! empty( $order_data->user_id ) ) {
        $user_id = absint( $order_data->user_id );
    }
    return $user_id;
}


Recursos Web

 Listado de triggers y actions de AutomatorWP:
 [https://automatorwp.com/all-triggers-and-actions/](https://automatorwp.com/all-triggers-and-actions/)


## Depuración de Hooks en debug.log

### Limpiar y leer log en PowerShell

```powershell
# Vaciar contenido
Clear-Content debug.log

# Ver las últimas 50 líneas
Get-Content debug.log -Tail 50
```

### Registrar solo hooks de EasyCart

```php
function registrar_hooks_easycart( $hook ) {
    $args = func_get_args();
    if ( stripos( $hook, 'wpeasycart_' ) !== false ) {
        error_log( 'Hook disparado: ' . $hook . ' - Args: ' . print_r( $args, true ) );
    }
}
add_action( 'all', 'registrar_hooks_easycart', 10, 99 );
```

**Ejemplo en debug.log:**

```
[21-May-2025 13:57:45 UTC] Hook disparado: wpeasycart_validate_submit_order_data - Args: Array(...)
[21-May-2025 13:57:56 UTC] Hook disparado: wpeasycart_order_success - Args: Array(...)
```

### Buscar líneas específicas en PowerShell

```powershell
Select-String -Path "debug.log" -Pattern "wpeasycart_order_success_pre" | ForEach-Object {
    $lineNumber = $_.LineNumber
    $lines = Get-Content "debug.log"
    $lines[$lineNumber - 5]   # Contexto previo
    $lines[$lineNumber]       # Línea exacta
}
```
