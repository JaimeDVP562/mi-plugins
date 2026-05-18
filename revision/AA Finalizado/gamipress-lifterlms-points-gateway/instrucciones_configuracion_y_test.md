# Guía de Instalación, Configuración y Pruebas: GamiPress - LifterLMS Points Gateway

Este documento detalla los pasos necesarios para instalar el plugin, configurar el entorno base de LifterLMS y realizar las pruebas de calidad (QA) para validar los flujos de compra y reembolso.

## PARTE 1: Instalación de los Plugins

Para que la pasarela funcione, el entorno debe contar con los plugins base instalados. Sigue este orden:

1. Descarga el plugin oficial de **LifterLMS** desde el repositorio de WordPress: `https://wordpress.org/plugins/lifterlms/`
2. Descarga el plugin oficial de **GamiPress** desde: `https://wordpress.org/plugins/gamipress/`
3. Descarga el archivo comprimido de nuestro plugin personalizado **"GamiPress - LifterLMS Points Gateway"**.
4. Descomprime este último archivo en tu ordenador. Obtendrás una carpeta llamada `gamipress-llms-points-gateway`.
5. Accede a los archivos de tu servidor (vía FTP o mediante el administrador de archivos de tu panel de hosting).
6. Navega hasta la ruta: `wp-content/plugins/`
7. Sube la carpeta de nuestro plugin (y las de LifterLMS y GamiPress si no estaban instaladas) dentro del directorio `plugins`.
8. Accede al panel de administración de WordPress (wp-admin).
9. Ve a la sección "Plugins" > "Plugins instalados".
10. Activa primero "LifterLMS" y "GamiPress".
11. Finalmente, localiza en la lista el plugin "GamiPress - LifterLMS Points Gateway" y haz clic en "Activar".

## PARTE 2: Configuración del Entorno (Páginas de LifterLMS)

Si te has saltado el asistente de instalación inicial de LifterLMS, el sistema no sabrá a dónde redirigir a los usuarios. Es obligatorio crear estas páginas manualmente para evitar enlaces rotos durante el proceso de compra.

### Paso A: Creación de las páginas

1. Ve a "Páginas" > "Añadir nueva".
2. Crea una página llamada "Panel del Estudiante" y escribe en el contenido el shortcode: `[lifterlms_my_account]` y publícala.
3. Crea una página llamada "Catálogo de Cursos" y escribe el shortcode: `[lifterlms_courses]` y publícala.
4. Crea una página llamada "Membresías" y escribe el shortcode: `[lifterlms_memberships]` y publícala.

### Paso B: Asignación de las páginas

1. Ve a "LifterLMS" > "Ajustes" > Pestaña "Cuentas".
2. En la opción "Student Dashboard Page", selecciona la página "Panel del Estudiante" y guarda los cambios.
3. Ve a la pestaña "Cursos".
4. En la opción "Course Catalog", selecciona la página "Catálogo de Cursos" y guarda los cambios.
5. Ve a la pestaña "Membresías".
6. En la opción "Membership Catalog", selecciona la página "Membresías" y guarda los cambios.

## PARTE 3: Configuración de la Pasarela de Pago

1. Ve a "LifterLMS" > "Ajustes" > Pestaña "Finalizar compra" (Checkout).
2. En el menú secundario, verás una lista de pasarelas de pago. Aparecerá una pasarela nueva por cada tipo de puntos que tengas configurado en GamiPress (por ejemplo, "GamiPress - Monedas" o "GamiPress - Soles").
3. Haz clic en el nombre de la pasarela que deseas configurar.
4. Marca la casilla "Habilitar / Deshabilitar" para activar la pasarela.
5. En el campo "Conversion Rate" (Tasa de conversión), introduce cuántos puntos equivalen a 1 unidad de la moneda base de la web.
6. Haz clic en "Guardar cambios".

## PARTE 4: Pruebas de Funcionamiento (QA)

Para garantizar que el sistema funciona correctamente, se deben ejecutar los siguientes tres escenarios de prueba utilizando un usuario con rol de suscriptor/estudiante.

### Prueba 1: Muro de Seguridad (Saldo Insuficiente)

1. Accede a la web con un usuario de prueba que NO tenga puntos suficientes en GamiPress.
2. Intenta comprar un curso seleccionando la pasarela de GamiPress.
3. Resultado esperado: La página debe recargarse mostrando un aviso en rojo indicando "Insufficient points. You need X points to buy this course.". El carrito de compra NO debe vaciarse.

### Prueba 2: Compra Exitosa

1. Añade saldo suficiente a tu usuario de prueba (desde el panel de administración "GamiPress" > "User Points").
2. Accede con ese usuario e intenta comprar un curso seleccionando la pasarela de GamiPress.
3. Resultado esperado: El sistema procesará el pago, descontará los puntos del saldo del usuario y lo redirigirá automáticamente a la pantalla de "Recibo de compra". El usuario tendrá acceso inmediato a las lecciones del curso.

### Prueba 3: Sistema de Reembolso (Exclusivo para Administradores)

1. Accede al panel de administración de WordPress.
2. Ve a "LifterLMS" > "Pedidos" y selecciona el pedido que acabas de generar en la Prueba 2.
3. Desplázate hacia abajo hasta la caja llamada "Transactions".
4. Localiza la transacción exitosa y haz clic en la acción "Refund" (Reembolsar).
5. En la casilla de cantidad, introduce exactamente el monto total del pedido.
6. IMPORTANTE: Haz clic únicamente en el botón que dice "Refund via GamiPress - [Nombre de tus puntos]".
7. Espera unos segundos y recarga la página del navegador.
8. Resultado esperado: El estado del estudiante en el curso pasará a "Cancelled", la tabla de transacciones mostrará el importe negativo en la columna "Refunded", y el usuario habrá recuperado sus puntos en su cuenta de GamiPress de forma automática.

---

## Recursos Adicionales (Documentación Visual)

Para comprender mejor los flujos nativos de redirección y reembolso dentro de la arquitectura de LifterLMS (los cuales nuestro plugin respeta y utiliza de forma nativa), puedes consultar la documentación oficial en vídeo:

- **Gestión de redirecciones Post-Checkout:** [How to Use the LifterLMS Checkout Redirect Feature](https://www.youtube.com/watch?v=kGgzBSCWCWo)
- **Gestión de devoluciones y pérdida de acceso:** [How to Refund an Order in LifterLMS](https://www.youtube.com/watch?v=F0k8A7fRkE4)
