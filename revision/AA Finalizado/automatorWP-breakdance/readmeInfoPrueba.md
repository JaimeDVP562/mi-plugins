# AutomatorWP - Breakdance Integration

Este complemento conecta AutomatorWP con el constructor Breakdance. A continuación se explican los pasos para instalarlo y probarlo desde WordPress.

## Requisitos

1. AutomatorWP instalado y activado.
2. Breakdance instalado y activado.

## Instalación

1. Copia la carpeta `automatorWP-breakdance` al directorio de plugins de WordPress (`wp-content/plugins/`).
2. En el panel de administración de WordPress, ve a **Plugins › Plugins instalados**.
3. Busca "AutomatorWP - Breakdance integration" y haz clic en **Activar**.

> Si no ves el plugin en la lista, revisa que la estructura de carpetas sea correcta (no debe haber una carpeta adicional dentro de `automatorWP-breakdance`).

## Prueba básica

1. Crea una automatización nueva en **AutomatorWP › Add new automation**. En el tipo de automatización seleccionamos conectado o anónimo
2. Si seleccionamos conectado, en el primer paso (trigger), selecciona alguno de los disparadores que añade el add-on de Breakdance, por ejemplo:
   - `Save page` (guardar página)
   - `Publish page` (publicar página)
   - `Submit form` (enviar formulario)
   
   Si seleccionamos anónimo nos saldra esta opción:
   - `Anonymous submit form` (enviar formulario anónimo)

3. Configura la acción que quieras ejecutar cuando se active el trigger (por ejemplo, enviar un correo, asignar un rol, etc.).
4. Guarda la automatización.

### Ejemplo rápido con formulario

1. Crea un formulario en Breakdance y publícalo en una página.
2. Crea una automatización que use el trigger **Submit form** o **Anonymous submit form** (si desea probar el anonimo deberas entrar en una nueva pestaña de incognito e introducir el link de tu pagina. Ej: "http://localhost/(nombre de tu Wordpress)/").
3. Envía el formulario desde la interfaz pública.
4. Comprueba que la acción configurada se ejecuta (p. ej. recibes un correo, se crea un usuario, etc.) y se crean nuevos registros en logs.

### Ejemplo rápido con guardar y publicar página.

1. Dirígete al apartado páginas y crea una nueva en **Añadir Página**.
2. Una vez realizado esto, deberemos darle al boton **Edit in Breakdance**.
3. Ahora con darle a save ya estaría cumplido disparador **Save page**.
4. A continuación haz click a la **X** que aparece en la parte superior derecha y después al boton **Exit to Wordpress**.
5. Cuando volvemos a la pantalla de edición de wordpress clicka a publicar y con esto ya estaria cumplido el disparador **Publish page**.
6. Comprueba que la acción configurada se ejecuta (p. ej. recibes un correo, se crea un usuario, etc.) y se crean nuevos registros en logs.

## Estructura del plugin

El archivo principal es `automatorWP-breakdance.php`. Los triggers se encuentran en `includes/triggers/` y las funciones auxiliares en `includes/functions.php`.
