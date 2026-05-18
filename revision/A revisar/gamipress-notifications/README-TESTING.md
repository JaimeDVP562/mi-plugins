# GamiPress Notifications - Testing Guide

Este archivo describe los pasos para comprobar la instalación y el comportamiento del plugin GamiPress Notifications tras los cambios del desarrollo.

## 1. Requisitos previos

- WordPress instalado (el mismo donde se prueba).
- GamiPress base instalado y activo.
- GamiPress Notifications activado.
- Usuario con permisos para generar logros/puntos/rangos.

## 2. Revisar la tabla de notificaciones (CT)

1. Conéctate a la base de datos (phpMyAdmin, WP-CLI, etc.).
2. Observa que se han creado dos tablas nuevas (wp-gamipress-notifications; wp-gamipress-pnotifications-meta).
3. Confirma que la tabla existe y contiene registros con campos:
   - `notification_id`, `user_id`, `user_earning_id`, `timestamp`, `read`

## 3. Configuración de plugin para pruebas

1. Crea un achievment.
2. Crea un rank.
3. crean un Points Type.
4. Guardar.

## 4. Generar una notificación

1. Crea o consigue que el usuario realice una acción de GamiPress:
   - desbloquear logro
   - subir rango
   - recibir puntos (award/deduct)
2. También puedes otorgarle tú mismo otorgarle el achievment o rank, los puntos solo puedes otorgarselos desde gamipress -> tools -> Bulk awards, gamipress -> tools -> bulk revokes.
3. Esta acción debe disparar un `user_earning` y el hook `gamipress_insert_user_earning`.

## 5. Verificar notificación en pantalla y registros en la tabla

1. Al otorgarle un logro, rango o punto, podras observar que en la parte superior, habra un boton que ponga notifications con un número al lado que indica las notificaciones que tienes y no has leido.
2. Clickale y te deberia llevar a la pagina de notificaciones que te crea por defecto el add-on.
3. Te deberan salir los pop-up de notificaciones, además de ver un apartado de no leido y un boton para pasar dichas notificaciones a leidos.
4. Al clickar al boton de "Marcar todas como leídas" se eliminaran de no leidas y se añadiran al apartado de leidas, además de eliminar el boton.
5. Fijate también que en las dos tablas creadas habran nuevos registros, a lo primero las notificaciones te saldran en la tabla wp-gamipress-notifications en la columna "read" como 0 (significa no leido), al clickar al boton este cambiara a 1 (significa leido).