=== AutomatorWP - Google Groups ===
Contributors: automatorwp, rubengc, alexcmuresan
Tags: google-groups, automatorwp, groups, automation
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with Google Groups

== Description ==

Google Groups is a tool to create and manage groups of users and conversations; this add-on lets you add/remove members and react to member changes from AutomatorWP.

= Actions =

* Add a member to a Google Group.
* Remove a member from a Google Group.

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

= 1.0.0 =

* Initial release.

== Developer notes ==

This version introduces a clean service abstraction. All API operations are
performed through a `Groups_Service_Interface` implementation. The plugin
provides a `Fake_Groups_Service` (mock) and a `Google_Groups_Service` (real,
when credentials are available). The helper `automatorwp_googlegroups_get_service()`
decides which one to instantiate based on configuration and the "test mode"
flag. Existing helper functions act as thin wrappers around the service.

Mock data is stored in the `fake_googlegroups_state` option and can be
preloaded with JSON fixtures for testing. When valid Google credentials are
provided via the settings screen, the real service will attempt to contact the
Admin SDK Directory API.

This architecture makes it easy to develop and test the plugin without a real
Google account; simply keep the "Force test/mock mode" option checked or leave
"Configured" unchecked.

