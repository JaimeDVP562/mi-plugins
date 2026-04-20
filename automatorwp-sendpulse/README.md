# AutomatorWP - SendPulse Add-on

Breve README del plugin SendPulse que hemos estado depurando.

Resumen de cambios aplicados
- Se cambió el flujo de autorización a server-side (client_credentials).
- Credenciales (`application_id`, `application_secret`) guardadas en opciones individuales con `autoload = 'no'`.
- Añadida migración para convertir opciones existentes a `autoload='no'`.
- Correcciones en JS/admin para enviar la petición AJAX y guardar el token.

Comprobaciones realizadas
- El token `automatorwp_sendpulse_access_token` se creó y aparece en la base de datos con `autoload = no`.
- Llamada a `https://api.sendpulse.com/addressbooks` con el token devolvió datos (200).

Pendientes (para revisar mañana)
- Añadir UI de pruebas (botón "Test API") en la página de ajustes (opcional).
- Revisar y limpiar cualquier referencia antigua de OAuth redirect si decidimos mantener solo client_credentials.
- Empaquetar y preparar commit final.

Plantilla de prompt para retomar mañana
--------------------------------------
Por favor pega aquí la información solicitada para continuar con los cambios pendientes:

1) Resumen corto del estado actual (una línea).
2) Resultado de la consulta en la BD (phpMyAdmin) — pega las filas relevantes ya ejecutadas:
   SELECT option_name, LENGTH(option_value) AS val_len, autoload FROM wp_options WHERE option_name LIKE 'automatorwp_sendpulse_%';

3) Si hiciste un `Test API`, pega el JSON de respuesta o captura de DevTools Console.

4) Lista de cambios que quieres priorizar (marca con `- [ ]` los que quieres que haga primero):
   - [ ] Añadir botón "Test API" en ajustes
   - [ ] Añadir notice para migración manual
   - [ ] Limpiar código relacionado con redirect OAuth
   - [ ] Preparar commit y release notes

5) Notas/observaciones adicionales (errores, capturas, permisos, etc.).

Instrucciones rápidas para usar la plantilla
- Copia este archivo y pega la sección "Plantilla de prompt" en tu mensaje al agente mañana.
- Rellena los campos 1–5 lo más concreto posible; incluye screenshots o el contenido de `wp-content/debug.log` si hay errores.

Gracias — con esto retomo al arrancar y continúo donde lo dejaste.
