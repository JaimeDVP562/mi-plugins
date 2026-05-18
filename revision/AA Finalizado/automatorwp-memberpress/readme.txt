=== AutomatorWP - MemberPress ===
Contributors: automatorwp, rubengc, eneribs
Tags: memberpress, automatorwp, membership, recurring, product
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 1.1.1
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with MemberPress

== Description ==

[MemberPress](https://memberpress.com/gamipress/home "MemberPress") is a membership plugin for WordPress that makes it easy to restrict access to content and well both membership subscriptions and digital downloads.

= Triggers =

* User registers with a specific field value.
* User purchases a membership.
* User purchases a one-time membership.
* User purchases a recurring membership.
* User cancels a membership.
* User suspends a membership.
* User views a membership.
* User completes a lesson of a course.
* User starts a course.
* User completes a course.
* User views a course.
* A sub-account is added to a parent account.

= Actions =

* Add membership to user.
* Remove all/specific membership to user.
* Cancel all/specific membership to user.

= Filters =

* User is active in membership.
* User is not active in membership.

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

= 1.1.1 =

* **New Features**
* New filter: User is active in membership.
* New filter: User is not active in membership.

= 1.1.0 =

* **Bug fixes**
* Fixed tag selection in user fields.

= 1.0.9 =

* **New Features**
* Tags for trigger "A sub-account is added to a parent account".

= 1.0.8 =

* **New Features**
* New trigger: A sub-account is added to a parent account.

= 1.0.7 =

* **New Features**
* New trigger: User views a membership.
* New trigger: User views a course.

= 1.0.6 =

* **New Features**
* Added new options on all actions to select a different user to apply the actions.

= 1.0.5 =

* **New Features**
* New trigger: User cancels a membership.
* New trigger: User suspends a membership.
* Added support to MemberPress Courses.
* New trigger: User completes a lesson of a course.
* New trigger: User starts a course.
* New trigger: User completes a course.

= 1.0.4 =

* **New Features**
* New action: Cancel all/specific membership to user.

= 1.0.3 =

* **Improvements**
Added support to the AutomatorWP custom values feature.

= 1.0.2 =

* **New Feature**
* New trigger: User registers with a specific field value.

= 1.0.1 =

* **Improvements**
* Added the subscription object to the event.

= 1.0.0 =

* Initial release.
