=== GamiPress - Notifications ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, point, achievement, rank, badge, award, reward, credit, engagement, ajax
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.5.6
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Instantly notify of achievements, steps and/or points awards completion to your users.

== Description ==

Notifications gives you the ability to live notify to your users about new achievements, steps, points awards, points deductions, ranks and/or rank requirements completion.

While your users are interacting with your site, they will get notified without refresh the page when an action gives them something related to GamiPress.

Also, you can configure text patterns to show as example the user name to make notifications more personalized.

= Features =

* Ability to live notify to your users about new achievements, steps, points awards, points deductions, ranks and/or rank requirements completion.
* Ability to selectively disable which notify to your users.
* Ability to positioning the notification in 8 different positions.
* Ability to set the lifetime of new notifications.
* Ability to enable the click to hide on notifications.
* Customizable notification sound effects.
* Easy controls to customize the background and text colors of notifications.
* Ability to enable the notification auto hide and the delay to perform it.
* Ability to disable live notifications checks making notifications work just on page load.
* Ability to configure each notification title pattern.
* Ability to configure the achievements look (thumbnail, earners, steps, etc).
* Ability to configure the ranks look (thumbnail, earners, requirements, etc).
* Integrated with the official add-ons that add new content to achievements and ranks.
* Support to FluentCommunity.

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

== Changelog ==

= 1.5.6 =

* **Bug Fixes**
* Fixed bug related to CSS border styles.

= 1.5.5 =

* **Bug Fixes**
* Fixed styles when displaying the notification in FluentCommunity.

= 1.5.4 =

* **Improvements**
* Support to FluentCommunity plugin.

= 1.5.3 =

* **New Features**
* Added new style options: title & text font size, border width, color & radius.

= 1.5.2 =

* **Improvements**
* Improved check for ajax exclusions.

= 1.5.1 =

* **Bug Fixes**
* Fixed error for live checks.

= 1.5.0 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.

= 1.4.9 =

* **Improvements**
* Added checks to avoid unnecessary ajax requests.

= 1.4.8 =

* **Bug Fixes**
* Fixed JS error.

= 1.4.7 =

* **Improvements**
* Added a small delay to check new notifications when another ajax request ends.

= 1.4.6 =

* **Bug Fixes**
* Added new checks to prevent hide notification.

= 1.4.5 =

* **Improvements**
* Improved the way to display notifications in case the page get refreshed multiples times (like in some themes while commenting or in certain plugin actions after submit a form).
* **Bug Fixes**
* Fixed parameters check when excluding URLs.
* **Developer Notes**
* Added a new filter to allow customize the last check delay through code.

= 1.4.4 =

* **Improvements**
* Added new checks on Javascript to prevent missing variables.

= 1.4.3 =

* **Improvements**
* Added new checks to prevent play duplicated sound effects.

= 1.4.2 =

* **Improvements**
* Added support to Congratulations Popups ajax requests to prevent check notices on popup loading actions.

= 1.4.1 =

* **Improvements**
* Added checks in Javascript to detect if URL is correctly passed on notifications check.

= 1.4.0 =

* **New Features**
* Optimization and full rework of the entire notification check process.
* **Improvements**
* Exclude Notifications add-on checks from AutomatorWP redirect checks for performance.
* Reduced settings to make the plugin more easy to configure.
* Removed the "Notifications check delay" setting.
* Removed the "Attempts to cancel live checks" setting.
