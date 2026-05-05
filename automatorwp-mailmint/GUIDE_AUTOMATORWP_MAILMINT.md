# AutomatorWP Mail Mint – Guía de configuración y pruebas

Esta guía explica cómo configurar y probar el plugin AutomatorWP Mail Mint, incluyendo la creación de cuentas 
necesarias, la instalación y conexión de Mail Mint, y un checklist de las acciones y disparadores verificados.

## Índice
- [Introducción](#introducción)
- [Requisitos previos](#requisitos-previos)
- [Crear cuenta de Google Cloud con Gmail](#crear-cuenta-de-google-cloud-con-gmail)
- [Descargar e instalar Mail Mint](#descargar-e-instalar-mail-mint)
- [Conectar Mail Mint desde AutomatorWP](#conectar-mail-mint-desde-automatorwp)
- [Checklist de pruebas actuales](#checklist-de-pruebas-actuales)

---

## Introducción

Esta guía recopila los pasos de prueba realizados sobre el plugin AutomatorWP Mail Mint y explica cómo aprovechar al 
máximo las acciones y disparadores disponibles. Incluye la configuración de cuentas necesarias y la conexión entre 
AutomatorWP y Mail Mint.

---

## Requisitos previos

- Tener una cuenta de Gmail.
- Acceso de administrador a tu instalación de WordPress.
- Tener instalado el plugin AutomatorWP.

---

## Crear cuenta de Google Cloud con Gmail

1. Accede a [Google Cloud Console](https://console.cloud.google.com/) y pulsa en "Empezar gratis".
2. Inicia sesión con tu cuenta de Gmail.
3. Completa el proceso de registro y acepta los términos.
4. Crea un nuevo proyecto desde el panel de Google Cloud.
5. Configura los servicios necesarios según la documentación de Mail Mint (por ejemplo, para envío de emails o 
integraciones específicas).

---


## Descargar e instalar Mail Mint

1. Descarga el plugin Mail Mint desde [WordPress.org](https://wordpress.org/plugins/mail-mint/) o desde la web oficial.
2. En tu WordPress, ve a "Plugins > Añadir nuevo" y sube el archivo ZIP de Mail Mint, o búscalo directamente en el repositorio.
3. Instala y activa el plugin.

---

## Instalar y configurar un plugin SMTP (recomendado: WP Mail SMTP)

Para garantizar el envío correcto de correos electrónicos desde WordPress y Mail Mint, es imprescindible instalar y configurar un plugin SMTP. Se recomienda [WP Mail SMTP](https://es.wordpress.org/plugins/wp-mail-smtp/):

1. Ve a "Plugins > Añadir nuevo" y busca "WP Mail SMTP".
2. Instala y activa el plugin.
3. Accede a "WP Mail SMTP > Ajustes" en el panel de WordPress.
4. En el apartado "Mailer", selecciona "Gmail".
5. Sigue el asistente para conectar tu cuenta de Gmail (usando el proyecto de Google Cloud creado anteriormente):
   - Introduce el Client ID y Client Secret generados en Google Cloud Console.
   - Autoriza la conexión siguiendo los pasos indicados.
6. Guarda los cambios y realiza una prueba de envío desde la pestaña "Email Test" para comprobar que todo funciona correctamente.

> Nota: Si usas otro proveedor SMTP, selecciona la opción correspondiente y configura los datos de acceso proporcionados por tu proveedor.

---

## Conectar Mail Mint desde los ajustes de AutomatorWP

1. Ve a "AutomatorWP > Ajustes > Integraciones" en el panel de WordPress.
2. Busca la sección de Mail Mint.
3. Comprueba que Mail Mint aparece como "instalado y activo". Si no, revisa la instalación.
4. No es necesario introducir credenciales manualmente: la integración es automática si ambos plugins están activos.

---

## Checklist de pruebas actuales

### Triggers (Disparadores)
- [✅] Se crea un nuevo contacto en Mail Mint
- [✖] Un contacto de Mail Mint se suscribe
- [✖] Un contacto de Mail Mint se desuscribe
- [✖] Un contacto confirma el doble opt-in
- [✅] Se aplica una etiqueta a un contacto
- [✅] Se elimina una etiqueta de un contacto
- [✅] Se añade un contacto a una lista
- [✅] Se elimina un contacto de una lista
- [✖] Un formulario de Mail Mint es enviado
- [✖] Un contacto abre un email
- [✖] Un contacto hace clic en un email

### Actions (Acciones)
- [✅] Crear o actualizar un contacto
- [✅] Cambiar el estado de un contacto
- [✖] Enviar email de doble opt-in
- [✅] Añadir etiqueta a un contacto
- [✅] Eliminar etiqueta de un contacto
- [✅] Añadir contacto a una lista
- [✅] Eliminar contacto de una lista


