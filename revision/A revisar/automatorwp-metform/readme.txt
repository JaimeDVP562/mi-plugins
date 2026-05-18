=== AutomatorWP - WP Fluent Forms ===
Contributors: automatorwp, rubengc, eneribs
Tags: fluent, form, forms, automatorwp, submission
Requires at least: 4.4
Tested up to: 6.4
Stable tag: 1.1.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with WP Fluent Forms

== Description ==

[WP Fluent Forms](https://wordpress.org/plugins/fluentform/ "WP Fluent Forms") is a free form-builder plugin that allows you to easily build advanced forms for your WordPress-powered website. Create standard forms, order forms and more with WP Fluent Forms.

= Triggers =

* User submits any/specific form.
* User submits a specific field value on any/specific form.

= Anonymous Triggers =

* Guest submits any/specific form.
* Guest submits a specific field value on any/specific form.

= Tags =

* Field value tag to use any form field submitted value on actions.

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

= How to handle values of fields with multiples inputs? =

Here is a quick explanation about how to meet the fields handled in a submission:

1) Create a test automation with the trigger "User submits any/specific form".
2) Save and activate the automation.
3) Submit the form to force AutomatorWP handle it.
4) Go to AutomatorWP > Logs > Check the trigger entry for the "User submits any/specific form".
5) On this entry, you will find the section "Fields Submitted" on the "Log Data" box.
6) Here you are able to see all the fields and sub-fields handled.
7) Copy the field or sub-field identifier to use it on the AutomatorWP "form_field" tag to use the field value on the AutomatorWP actions.

== Screenshots ==

== Changelog ==

= 1.1.0 =

* **Improvements**
* Ensure compatibility with new trigger tags.

= 1.0.9 =

* **Bug Fixes**
* Fixed anonymous triggers.

= 1.0.8 =

* **Bug Fixes**
* Fixed deprecated hooks to adapt to Fluent Forms latest version.

= 1.0.7 =

* **Bug Fixes**
* Fixed typo on plugin main file name.

= 1.0.6 =

* **Bug Fixes**
* Fixed anonymous triggers visibility.

= 1.0.5 =

* **New Features**
* Added support for nested fields (requires AutomatorWP 1.4.4).

= 1.0.4 =

* **New Features**
* Added support to anonymous automations.
* New anonymous trigger: Guest submits any/specific form.
* New anonymous trigger: Guest submits a specific field value on any/specific form.

= 1.0.3 =

* **Bug Fixes**
* Fixed fields submitted detection.

= 1.0.2 =

* **Improvements**
* Added information of all fields values sent on the form submission.
* Prevent to override other integration tags replacements.

= 1.0.1 =

* **Bug Fixes**
* Fixed form ID detection.
* Fixed field values not detected correctly in some submissions.

= 1.0.0 =

* Initial release.
