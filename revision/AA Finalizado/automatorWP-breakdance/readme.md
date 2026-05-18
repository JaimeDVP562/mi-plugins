=== AutomatorWP - Breakdance ===

Contributors: 

Tags: breakdance, automatorwp, page builder, forms

Requires at least: 4.4

Tested up to: 6.9.4

Stable tag: 1.0.0

License: GNU AGPLv3

License URI: http://www.gnu.org/licenses/agpl-3.0.html

Conecta AutomatorWP con el constructor Breakdance para reaccionar a eventos de páginas y formularios.

== Description ==

[Breakdance](https://breakdance.com) es un constructor visual de WordPress que permite crear páginas y formularios mediante arrastrar y soltar. Esta integración añade triggers a AutomatorWP para que puedas automatizar acciones basadas en interacciones dentro de las páginas Breakdance.

= Triggers =

* User saves a page.
* User publishes a page.
* User submits a form.
* User submits a form (anonymous).

= Anonymous Triggers =

* User submits a form (anonymous).

= Actions =

Las mismas acciones que ofrece AutomatorWP core (envío de correo, asignar rol, etc.) se pueden ejecutar cuando se disparan los triggers de este plugin.

= Tags =

* Page tags para utilizar información de la página (título, ID, etc.).
* Form field tags para acceder a valores enviados en un formulario (reemplaza FIELD_ID por el identificador del campo).

= Filters =

Este add-on no añade filtros propios; puedes usar los del núcleo de AutomatorWP con los eventos de Breakdance.

== Installation ==

= From WordPress backend =

1. Navigate to Plugins -> Add new.
2. Click the button "Upload Plugin" next to "Add plugins" title.
3. Upload the downloaded zip file and activate it.

= Direct upload =

1. Upload the downloaded zip file into your `wp-content/plugins/` folder.
2. Unzip the uploaded zip file.
3. Navigate to Plugins menu on your WordPress admin area.
4. Activate this plugin.

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.0.1 =
* Added direct access protection to the main plugin file.
* Improved code sustainability with subtle implementation cleanup.

= 1.0.0 =
* Initial release.
