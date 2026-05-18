Guía de pruebas: Cómo verificar triggers y actions de AutomatorWP - Mail Mint

Objetivo
- Probar que las *triggers* (disparadores) y *actions* (acciones) del add-on `automatorwp-funnels-mail-mint` funcionan correctamente sin depender de que aparezcan en el panel de AutomatorWP. Esta guía está pensada para que cualquier compañero la siga sin problema.

Resumen (rápido)
- Requisitos: tener WordPress local con el plugin AutomatorWP activo y el add-on copiado en `wp-content/plugins/automatorwp-funnels-mail-mint` y activado en Plugins.
- Instalad el plugin **Code Snippets** (o utilizad WP‑CLI). Luego añadid el snippet de prueba que viene en `dev-snippets/snippet-test-triggers.php` o ejecutadlo con `wp eval-file`.
- Revisad el `debug.log` para buscar líneas con el prefijo `[AWPM TEST]` — ahí están las evidencias de ejecución.

Paso a paso (no técnico)
1) Verificar requisitos mínimos
  - En el sitio local, id a Plugins y comprobad que **AutomatorWP** está activado.
  - Comprobad que el add‑on `AutomatorWP - WP Funnels ↔ Mail Mint` aparece en la lista de plugins (no hace falta que muestre triggers en Automations para probar).

2) (Opcional) Forzar carga de triggers en desarrollo
  - Si queréis intentar que los triggers se registren en el admin, podéis definir la constante de desarrollo. Sin embargo NO es necesario para las pruebas con snippets.
  - Para forzar sin tocar el plugin, crear el fichero `wp-content/mu-plugins/force-mailmint.php` con este contenido:

    <?php
    if ( ! defined( 'AUTOMATORWP_MAILMINT_FORCE_LOAD_TRIGGERS' ) ) {
        define( 'AUTOMATORWP_MAILMINT_FORCE_LOAD_TRIGGERS', true );
    }

  - Alternativa: añadir la línea en `wp-config.php` justo antes de "That's all, stop editing":

    define('AUTOMATORWP_MAILMINT_FORCE_LOAD_TRIGGERS', true);

3) Instalar Code Snippets (recomendado) o usar WP‑CLI
  - Plugin: Plugins → Añadir nuevo → buscar "Code Snippets" → instalar y activar.
  - WP‑CLI: si lo usáis, podéis ejecutar los snippets con `wp eval-file`.

4) Añadir y ejecutar el snippet de prueba
  - Archivo con el snippet: `dev-snippets/snippet-test-triggers.php` (ya incluido en el repo).
  - Opción A (Code Snippets): crear un nuevo snippet, pegar el contenido de `snippet-test-triggers.php`, Guardar y Ejecutar. Elegid "Ejecutar en todas partes" para que funcione en admin y frontend.
  - Opción B (WP‑CLI): desde la carpeta del sitio WordPress ejecutar:

    wp eval-file wp-content/plugins/automatorwp-funnels-mail-mint/dev-snippets/snippet-test-triggers.php

5) ¿Qué buscar en los logs?
  - El snippet escribe en el log de PHP con prefijo `[AWPM TEST]`. En un entorno XAMPP/Local, buscad `wp-content/debug.log` o el log del servidor.
  - Líneas esperadas (ejemplos):
    - [AWPM TEST] Starting trigger/action tests
    - [AWPM TEST] AutomatorWP class found
    - [AWPM TEST] AutomatorWP_Mailmint_Trigger_Step_Submitted exists? yes
    - [AWPM TEST] AutomatorWP_Mailmint_Trigger_Funnel_Completed exists? yes
    - [AWPM TEST] AutomatorWP_Mailmint_Action_Add_Subscriber exists? yes
    - [AWPM TEST] do_action for wpfunnels_step_submitted fired
    - [AWPM TEST] automatorwp_trigger_event returned: <algo>
    - [AWPM TEST] Action execute result: true

  - Copiad las líneas relevantes del `debug.log` y pegadlas en la entrega como comprobante.

6) Si algo falla — pasos rápidos de diagnóstico
  - Si no se ve "AutomatorWP class found": activad el plugin AutomatorWP y volved a ejecutar el snippet.
  - Si las clases de trigger/action no existen: comprobad que el add‑on está activado (Plugins) y que los archivos `includes/triggers/` y `includes/actions/` están presentes en la carpeta del add‑on.
  - Si `automatorwp_trigger_event not available`: AutomatorWP no está cargado; asegurar activación y recargar la página.
  - Mirad `debug.log` por errores PHP (líneas con `Fatal` o `Warning`) y pegadlas si las encontráis.



7) Revertir cambios de desarrollo (opcional)
  - Si creasteis `wp-content/mu-plugins/force-mailmint.php`, podéis eliminarlo cuando terminéis.
  - Si añadisteis la constante en `wp-config.php`, quitadla para volver al comportamiento normal.

Contenido del snippet (copiado para facilidad)
------------------------------------------------
Pegad exactamente el siguiente código en Code Snippets o guardadlo en un archivo y ejecutadlo con WP‑CLI:

<?php
/**
 * Snippet: Test AutomatorWP triggers and Mail Mint action
 * Usage: paste into Code Snippets (run once) or execute via WP-CLI:
 * wp eval-file dev-snippets/snippet-test-triggers.php
 */

if ( ! defined( 'WPINC' ) ) {
    // Protect against direct access when pasted into a public file
    return;
}

function awpm_test_log( $msg ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( '[AWPM TEST] ' . $msg );
    }
}

awpm_test_log( 'Starting trigger/action tests' );

// Check AutomatorWP availability
if ( ! class_exists( 'AutomatorWP' ) ) {
    awpm_test_log( 'AutomatorWP class NOT found - ensure AutomatorWP plugin is active' );
} else {
    awpm_test_log( 'AutomatorWP class found' );
}

// Check our trigger classes
$triggers = array(
    'AutomatorWP_Mailmint_Trigger_Step_Submitted',
    'AutomatorWP_Mailmint_Trigger_Funnel_Completed',
);
foreach ( $triggers as $t ) {
    awpm_test_log( $t . ' exists? ' . ( class_exists( $t ) ? 'yes' : 'no' ) );
}

// Check action class
$action_class = 'AutomatorWP_Mailmint_Action_Add_Subscriber';
awpm_test_log( $action_class . ' exists? ' . ( class_exists( $action_class ) ? 'yes' : 'no' ) );

// Test triggering via WP hooks (simulate WP Funnels)
awpm_test_log( 'Firing do_action("wpfunnels_step_submitted", $step_id, $user_id)' );
try {
    do_action( 'wpfunnels_step_submitted', 123, 1 );
    awpm_test_log( 'do_action for wpfunnels_step_submitted fired' );
} catch ( Exception $e ) {
    awpm_test_log( 'Exception while firing hook: ' . $e->getMessage() );
}

// Directly call automatorwp_trigger_event (if available)
if ( function_exists( 'automatorwp_trigger_event' ) ) {
    awpm_test_log( 'Calling automatorwp_trigger_event directly' );
    $res = automatorwp_trigger_event( 'mailmint', 'wpfunnels_step_submitted', 1, array( 'step_id' => 123 ) );
    awpm_test_log( 'automatorwp_trigger_event returned: ' . ( is_scalar( $res ) ? print_r( $res, true ) : gettype( $res ) ) );
} else {
    awpm_test_log( 'automatorwp_trigger_event not available' );
}

// Test action execution logic by instantiating the action class and calling execute()
if ( class_exists( $action_class ) ) {
    try {
        $a = new $action_class();
        $sample_action = array();
        $user_id = 0; // unauthenticated / fallback
        $options = array( 'email' => 'tester@example.test', 'first_name' => 'Tester', 'list_id' => 'dev' );
        $automation = array( 'id' => 'dev-test' );
        awpm_test_log( 'Calling ' . $action_class . '->execute() with sample data' );
        $ok = $a->execute( $sample_action, $user_id, $options, $automation );
        awpm_test_log( 'Action execute result: ' . ( $ok ? 'true' : 'false' ) );
    } catch ( Exception $e ) {
        awpm_test_log( 'Exception when executing action: ' . $e->getMessage() );
    }
} else {
    awpm_test_log( 'Action class not available, skipping execute() test' );
}

awpm_test_log( 'Trigger/action tests finished' );