=== AutomatorWP - Google Groups ===
Contributors: automatorwp, rubengc, alexcmuresan
Tags: google-groups, automatorwp, groups, automation
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with Google Groups using the Google Admin SDK Directory API.

== Description ==

This add-on allows AutomatorWP to manage Google Groups members and groups via
Google Workspace. It requires a service account with domain-wide delegation and
appropriate Admin SDK scopes.

= Actions =

* Add a member to a Google Group
* Remove a member from a Google Group
* Create a Google Group
* Delete a Google Group
* Update group settings (name/description)
* Change a member's role in a group
* Remove all members from a group
* Export group members as CSV

= Triggers =

* A member is added to a Google Group
* A member is removed from a Google Group
* A Google Group is created
* A Google Group is deleted
* A member's role is changed in a Google Group

== Configuration ==

1. Create a Google Cloud service account with domain-wide delegation.
2. Grant it the following scopes:
   * https://www.googleapis.com/auth/admin.directory.group
   * https://www.googleapis.com/auth/admin.directory.group.member
3. In the plugin settings, paste the service account JSON key.
4. Optionally set a service account impersonated email and domain.
5. Check the "Configured" option to enable API calls.

== Installation ==

1. Upload the plugin zip through Plugins -> Add New -> Upload Plugin.
2. Activate the plugin.
3. Configure the service account settings under AutomatorWP settings.

== Changelog ==

= 1.0.0 =
* Initial release.

== Developer notes ==

All API requests are performed via the `Google_Groups_Service` class using the
Google Admin SDK Directory API. The plugin will only attempt API calls when the
"Configured" option is enabled and a valid service account JSON is provided.

