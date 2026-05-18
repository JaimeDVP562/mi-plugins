# AutomatorWP Mail Mint — Guía técnica de integración

Guía de referencia para desarrolladores sobre el plugin `automatorwp-mailmint`: qué hooks de Mail Mint
existen realmente, cómo se disparan, y qué limitaciones tiene cada trigger implementado.

---

## Índice

- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Hooks reales de Mail Mint (verificados en código fuente)](#hooks-reales-de-mail-mint)
- [Triggers implementados](#triggers-implementados)
- [Acciones implementadas](#acciones-implementadas)
- [Limitaciones conocidas](#limitaciones-conocidas)
- [Cómo probar la integración](#cómo-probar-la-integración)

---

## Requisitos

- WordPress con AutomatorWP activo
- Mail Mint (probado con v1.21.3)
- El contacto de Mail Mint debe tener el mismo email que un usuario WordPress registrado para
  que AutomatorWP pueda asociar la acción a un usuario. Si no existe cuenta WP, se usa el
  primer administrador como fallback.

---

## Instalación

1. Sube la carpeta `automatorwp-mailmint` a `wp-content/plugins/`
2. Activa el plugin desde el panel de WordPress
3. Comprueba en AutomatorWP → Ajustes → Integraciones que Mail Mint aparece como activo

No requiere credenciales adicionales: la integración detecta Mail Mint automáticamente si está activo.

---

## Hooks reales de Mail Mint

Información obtenida directamente del código fuente de Mail Mint v1.21.3.
**No existe documentación oficial de hooks para desarrolladores.**

### Hooks que SÍ existen y cuándo se lanzan

| Hook | Parámetros | Se lanza desde |
|---|---|---|
| `mint_after_contact_creation` | `$contact_id` | `ContactModel::insert()` — formularios, código programático |
| `mailmint_contacts_saved` | `$contact_id, $params` | Admin UI crear/actualizar contacto (desde v1.19.5) |
| `mailmint_after_form_submit` | `$form_id, $contact_id, $contact` | Envío de formulario Mail Mint |
| `mailmint_tag_applied` | `$tags, $contact_id` | `ContactGroupModel::set_tags_to_contact()` — UI y código |
| `mint_tag_removed` | `$groups, $contact_ids` | `ContactGroupPivotModel` — UI y código |
| `mailmint_list_applied` | `$lists, $contact_id` | `ContactGroupModel::set_lists_to_contact()` — UI y código |
| `mint_list_removed` | `$groups, $contact_ids` | `ContactGroupPivotModel` — UI y código |
| `mint_subscriber_status_to_{status}` | `$contact_id, $old_status` | SOLO desde `EmailBounce.php` → `record_unsubscribe()` (webhooks de proveedores) |
| `mailmint_after_confirm_double_optin` | `$contact` | Confirmación de doble opt-in |
| `mailmint_after_email_open` | `$email_id` | Apertura de email de campaña |
| `mailmint_after_email_click` | `$email_id, $url` | Clic en enlace de email de campaña |

### Hooks que NO existen

- **Cambio de estado vía UI** — `ContactModel::update()` y `ContactController::update_status()` son
  llamadas directas a BD sin ningún hook. Cambiar el estado de un contacto desde la interfaz de
  Mail Mint no lanza ningún evento.
- **Desuscripción vía enlace de email** — `UnsubscribeConfirmation.php` llama a
  `ContactModel::update_subscription_status()`, también sin hook.

---

## Triggers implementados

### ✅ Contacto creado (`mailmint_contact_created`)

**Cuándo se dispara:** al crear un contacto nuevo, sea cual sea su estado.

**Hooks escuchados:**
- `mint_after_contact_creation` — formularios y código programático
- `mailmint_contacts_saved` (solo creates, no updates) — UI del admin desde v1.19.5

**Cómo probarlo desde la UI:** Mail Mint → Contacts → Add New → cualquier estado → guardar.

---

### ⚠️ Contacto suscrito (`mailmint_contact_subscribed`)

**Cuándo se dispara:** al crear un contacto nuevo con `status = 'subscribed'`.

**Importante — lo que NO hace:**
- No se dispara al cambiar el estado de un contacto existente a "Subscribed"
- No se dispara si Mail Mint convierte el estado a "Pending" por tener el double opt-in activado

**Hooks escuchados:**
- `mint_after_contact_creation` — formularios sin double opt-in
- `mailmint_contacts_saved` (solo creates, status=subscribed) — UI del admin

**Cómo probarlo:** crear un contacto nuevo con estado "Subscribed" explícito. Si el double opt-in
global está activo, Mail Mint puede forzar el estado a "Pending" y el trigger no se disparará.

---

### ⚠️ Contacto desuscrito (`mailmint_contact_unsubscribed`)

**Cuándo se dispara:** cuando un proveedor de email externo (Mailgun, SendGrid, etc.) notifica
un bounce o unsubscribe a través de webhook.

**Importante — lo que NO hace:**
- No se dispara al cambiar el estado a "Unsubscribed" desde la UI de Mail Mint
- No se dispara cuando el contacto hace clic en el enlace de baja del email

**Hook escuchado:** `mint_subscriber_status_to_unsubscribed` (solo desde `EmailBounce.php`)

---

### ✅ Tag aplicado a contacto (`mailmint_tag_applied`)

**Cuándo se dispara:** al añadir un tag a un contacto.

**Cómo probarlo:** Mail Mint → Contacts → editar contacto → añadir tag.

**Parámetro `$tags`:** array de arrays con clave `id` (y opcionalmente `title`).

---

### ✅ Tag eliminado de contacto (`mailmint_tag_removed`)

**Cuándo se dispara:** al quitar un tag de un contacto.

**Hook:** `mint_tag_removed($groups, $contact_ids)` — `$groups` son IDs de grupos, `$contact_ids` es array.

---

### ✅ Contacto añadido a lista (`mailmint_list_applied`)

**Cuándo se dispara:** al añadir un contacto a una lista.

---

### ✅ Contacto eliminado de lista (`mailmint_list_removed`)

**Cuándo se dispara:** al quitar un contacto de una lista.

---

### ✅ Formulario enviado (`mailmint_form_submitted`)

**Cuándo se dispara:** al enviar un formulario de Mail Mint desde el frontend.

**No se dispara** desde el admin. Requiere un formulario Mail Mint embebido en una página.

---

### ✅ Double opt-in confirmado (`mailmint_double_optin_confirmed`)

**Cuándo se dispara:** cuando el contacto hace clic en el enlace de confirmación del email de double opt-in.

---

### ✅ Email abierto (`mailmint_email_opened`)

**Cuándo se dispara:** cuando se registra la apertura de un email de campaña.

---

### ✅ Enlace de email clicado (`mailmint_email_clicked`)

**Cuándo se dispara:** cuando se registra un clic en un enlace de email de campaña.

---

## Acciones implementadas

| Acción | Descripción | Función Mail Mint usada |
|---|---|---|
| `mailmint_create_contact` | Crear o actualizar contacto | `mailmint_create_single_contact()` |
| `mailmint_change_status` | Cambiar estado del contacto | `$wpdb->update()` directo |
| `mailmint_send_double_optin` | Enviar email de double opt-in | `do_action('mint_send_double_optin_email', ...)` |
| `mailmint_add_tag` | Añadir tag al contacto | `mailmint_add_contact_to_groups('tags', ...)` |
| `mailmint_remove_tag` | Eliminar tag del contacto | Consulta directa a `mint_contact_group_relationship` |
| `mailmint_add_to_list` | Añadir contacto a lista | `mailmint_add_contact_to_groups('lists', ...)` |
| `mailmint_remove_from_list` | Eliminar contacto de lista | Consulta directa a `mint_contact_group_relationship` |

---

## Limitaciones conocidas

### Contactos sin cuenta WordPress

AutomatorWP requiere un `user_id` de WordPress para procesar automations de tipo "user". Si el
contacto de Mail Mint no tiene cuenta WP con el mismo email, el plugin usa el primer administrador
como fallback. Esto significa que:

- El trigger se dispara correctamente
- Las acciones que usan el campo "Email" vacío aplicarán al email del admin, no al del contacto

### Cambios de estado vía UI no disparan triggers

Mail Mint no lanza ningún hook cuando se cambia el estado de un contacto desde su interfaz. Esto
afecta a los triggers `contact-subscribed` y `contact-unsubscribed`. Solo se pueden disparar
creando contactos nuevos o mediante webhooks de proveedores de email respectivamente.

### Double opt-in activo

Si el double opt-in global está activado en Mail Mint, los contactos creados desde la UI pueden
ser forzados a estado "Pending" independientemente de lo que se seleccione. El trigger
`contact-subscribed` no se disparará en ese caso.

---

## Cómo probar la integración

El plugin incluye un script de prueba en `test-mailmint.php` (en la raíz del plugin).

**Acceder:** `http://[tu-dominio]/wp-content/plugins/automatorwp-mailmint/test-mailmint.php`

El script:
1. Crea un usuario WP de prueba (`automatorwp-mailmint-test@example.com`)
2. Crea un contacto Mail Mint vinculado
3. Crea una automatización completa en la BD de AutomatorWP
4. Dispara el hook directamente y verifica los logs
5. Ofrece botón de limpieza para eliminar todos los datos de prueba

**Eliminar el script** una vez finalizadas las pruebas.
