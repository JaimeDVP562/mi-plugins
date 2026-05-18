=== GP Recommended Achievements ===
Contributors:      gamipress
Tags:              gamipress, achievements, recommendations
Requires at least: 5.6
Tested up to:      6.5
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPLv2 or later

Muestra logros recomendados en la página o shortcode de un logro individual.

== Description ==

Este add-on para GamiPress muestra automáticamente un listado de **logros recomendados** debajo de cualquier logro individual, ya sea en su página dedicada o mediante el shortcode `[gamipress_achievement]`.

**Características principales:**

* Muestra entre 1 y 6 logros recomendados en formato de columnas.
* Excluye los logros ya desbloqueados por el usuario.
* Opcional: muestra solo logros del mismo tipo o de todos los tipos.
* Caché automática de 24 horas por usuario mediante transients de WordPress → mínimo impacto en la base de datos.
* Invalidación inteligente de caché cuando el usuario desbloquea un logro o cuando se edita un logro.
* Compatible con sobreescritura de plantilla desde el tema activo.

== Ajustes ==

Los ajustes se encuentran en **GamiPress → Recommended Achievements**:

| Opción | Descripción | Valores |
|--------|-------------|---------|
| Max achievements to show | Número de logros recomendados a mostrar | 1 – 6 (defecto: 3) |
| Show same type only | Recomendar solo del mismo tipo de logro | Activado / Desactivado |

== Instalación ==

1. Sube la carpeta `gp-recommended-achievements` a `/wp-content/plugins/`.
2. Activa el plugin desde **Plugins → Plugins instalados**.
3. Configura las opciones en **GamiPress → Recommended Achievements**.

== Personalización de plantilla ==

Para sobreescribir el HTML de la tarjeta de recomendación, copia el archivo
`templates/recommended.php` del plugin a tu tema en:

    {tu-tema}/gp-recommended-achievements/recommended.php

Las variables disponibles dentro de la plantilla son:

* `$achievements` — array de `WP_Post` con los logros recomendados.
* `$columns` — número de columnas configurado.
* `$achievement_id` — ID del logro que se está visualizando.

== Changelog ==

= 1.0.0 =
* Primera versión estable.

== Frequently Asked Questions ==

= ¿Funciona sin GamiPress? =
No. Este plugin es un add-on de GamiPress y requiere que esté instalado y activo.

= ¿Con qué frecuencia se actualiza la caché? =
Las recomendaciones se cachean durante 24 horas por combinación usuario + logro.
La caché se invalida automáticamente cuando el usuario desbloquea un logro o
cuando se edita cualquier logro.
