=== AutomatorWP - Manual Triggers ===
Contributors: automatorwp, rubengc
Tags: automatorwp, manual triggers, automation, shortcode
Requires at least: 4.4
Tested up to: 6.4
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Launch AutomatorWP automations manually via the admin panel, PHP code or shortcodes.

== Description ==

AutomatorWP - Manual Triggers allows you to launch automations manually in three different ways: from the admin panel using the Run Now button, from PHP code using a generated snippet, or from any page using a shortcode button.

= Triggers =

* Manual launch (logged-in user).
* Manual launch (anonymous).

= 3 Ways to Launch =

**Run Now (admin panel)**
A "Run Now" button is injected into each manual trigger in the automation editor. For logged-in triggers, a dialog allows you to specify a user ID (leave empty to use the current admin user).

**Code Example**
A collapsible code snippet is shown in the trigger panel with the exact PHP function to call from your theme or plugin.

**Shortcode**
A collapsible shortcode is shown in the trigger panel, ready to paste into any page or widget.

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

= How do I use the shortcode? =

For the current logged-in user:
`[automatorwp_manual_trigger trigger="ID" user="" label="Run"]`

For a specific user:
`[automatorwp_manual_trigger trigger="ID" user="123" label="Run"]`

For anonymous triggers:
`[automatorwp_manual_trigger trigger="ID" label="Run"]`

= How do I launch a trigger from PHP code? =

Basic usage (uses the current logged-in user):
`automatorwp_run_trigger( ID );`

With a specific user ID:
`automatorwp_run_trigger( ID, 123 );`

== Screenshots ==

== Changelog ==

= 1.0.0 =

* Initial release.
* Added trigger: Manual launch (logged-in user).
* Added trigger: Manual launch (anonymous).
* Added Run Now button in the automation editor.
* Added PHP code example in the automation editor.
* Added shortcode in the automation editor.