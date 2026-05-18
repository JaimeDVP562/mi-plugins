=== AutomatorWP - Schedule Actions ===
Contributors: automatorwp, rubengc, eneribs
Tags: webhooks, automatorwp, automation, automate, trigger, action
Requires at least: 4.4
Tested up to: 6.8
Stable tag: 1.1.8
License: GNU AGPLv3
License URI:  http://www.gnu.org/licenses/agpl-3.0.html

Schedule any action to run after a time delay of your choice or in a specific date.

== Description ==

Schedule Actions lets you schedule any action to force it’s execution after a time delay or a specific date of your choice!

You can set up an automation like "When user purchases a product, Send an email 7 days after" or "When user completes a course, Send an email on Dec 31th at 08:00 AM".

Schedule Actions includes easy controls that lets you configure different schedules on each action and is designed to work with any action of any integration, without exception!

= Features =

* Schedule any action to the specific date of your choice.
* Delay any action for seconds, minutes, hours, days, weeks, months and years of your choice.
* Configure different schedules for each action of the same automation.
* Works with all actions supported by AutomatorWP.

== Installation ==

= From WordPress backend =

1. Navigate to Plugins -> Add new.
2. Click the link "Upload Plugin" next to "Add plugins" title.
3. Upload the downloaded zip file and activate it.

= Direct upload =

1. Upload the downloaded zip file into your `wp-content/plugins/` folder.
2. Unzip the uploaded zip file.
3. Navigate to Plugins menu on your WordPress admin area.
4. Activate this plugin.

== Frequently Asked Questions ==

= Can this add-on schedule any action? =

Yes, Schedule Actions add-on can schedule all actions supported by AutomatorWP.

= Can I set different schedules for each action of the same automation? =

Absolutely yes, you can schedule each action individually and set different schedules to each one.

Not scheduled actions will run immediately and scheduled actions will run at the moment they are scheduled.

= Can actions be scheduled for a specific date? =

Yes, this add-on offers 2 ways to schedule actions, schedule them for a specific date or delay their execution.

= Can actions be delayed? =

Yes, you can delay any action execution for seconds, minutes, hours, days, weeks, months and years of your choice.

== Screenshots ==

== Frequently Asked Questions ==

== Changelog ==

= 1.1.8 =

* **Bug Fixes**
* Fixed issue with delay in getting action options.

= 1.1.7 =

* **Bug Fixes**
* Fixed compatibility with AutomatorWP 5.3.2.

= 1.1.6 =

* **Developer notes**
* New integration logo.

= 1.1.5 =

* **Bug Fixes**
* Fixed Bug related to tags.

= 1.1.4 =

* **Bug Fixes**
* Fixed datetime UTC.

= 1.1.3 =

* **Bug Fixes**
* Fixed datetime to adapt to new WordPress version.

= 1.1.2 =

* **Bug Fixes**
* Fixed actions schedule when Action Scheduler is active.

= 1.1.1 =

* **Developer Notes**
* Added new filter to force the use of WP Cron instead of Action Scheduler.

= 1.1.0 =

* **New Features**
* Added the ability to schedule actions to a specific date.
* New controls to "schedule" or "delay" and action.

= 1.0.9 =

* **Improvements**
* Ensure to get the correct trigger log edit to parse the tags from the correct log entry.
* Make use of the cache to reduce the number of database queries.

= 1.0.8 =

* **New Features**
* Added support to schedule actions for the same automation multiples times (Requires AutomatorWP 1.8.2).

= 1.0.7 =

* **Bug Fixes**
* Fixed typo on plugin main file name.

= 1.0.6 =

* **New Features**
* Added the ability to delay actions in seconds.

= 1.0.5 =

* **New Features**
* Added support to AutomatorWP filters.

= 1.0.4 =

* **Improvements**
* Improved the way to detect if an action has been scheduled for a user or not to ensure to correct schedule the actions.

= 1.0.3 =

* **Improvements**
* Ensure to use UTC time to schedule actions.

= 1.0.2 =

* **Improvements**
* Updated deprecated jQuery functions.

= 1.0.1 =

* **New Features**
* Added support to the AutomatorWP brand new anonymous automations.

= 1.0.0 =

* Initial release.
