=== GamiPress - Daily Login Rewards ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, gamify, point, achievement, badge, award, reward, credit, engagement, ajax
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.1.8
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Add daily rewards to perform your site visits.

== Description ==

Daily Login Rewards gives you the ability to award your users for log in to your website daily.

With just a few controls, you will be able to create calendars with rewards for daily log ins and set any limitation as you want like force consecutive log ins with penalties or limit by a time period.

Also, Daily Login Rewards let's you configure which calendars to show on a pop-up when user successfully earns a new reward for log in and includes a configurable stamp effect that will make them look impressive.

Place any calendar of rewards anywhere, including in-line on any page or post, using a simple shortcode, or on any sidebar through a configurable widget.

Daily Login Rewards extends and expands GamiPress adding new activity events and features.

= New Events =

* Earn any reward: When an user earns a new reward for log in.
* Earn any reward of a specific calendar: When an user earns a new reward for log in of a specific calendar.
* Earn any points reward: When an user earns points as a reward for log in.
* Earn any points reward of a specific calendar: When an user earns points as a reward for log in of a specific calendar.
* Earn any achievement reward: When an user earns an achievement as a reward for log in.
* Earn any achievement reward of a specific calendar: When an user earns an achievement as a reward for log in of a specific calendar.
* Earn any a rank reward: When an user earns a rank as a reward for log in.
* Earn any a rank reward of a specific calendar: When an user earns a rank as a reward for log in of a specific calendar.
* Earn all rewards of any calendar: When an user earns all rewards of a calendar.
* Earn all rewards of a specific calendar: When an user earns all rewards of a specific calendar.

= Features =

* Ability to create as many calendars of rewards as you like.
* Set as completion requirement the consecutive log in and the penalty for not log in consecutively.
* Ability to make calendars available by a limited time (with start and/or end date).
* Make any calendar repeatable limited or unlimited times.
* Set as completion requirement the completion of other calendars to being started with a new one.
* Configure which calendars to show on a pop-up when user earns a new reward.
* Stamp effect to let user know which rewards has already earned.
* Configure the display options for each calendar of rewards like columns or rewards image sizes.
* Settings to enable multiple log in rewards with which you will allow your users earn multiple rewards the same day.
* Block to place any calendar of rewards anywhere.
* Shortcode to place any calendar of rewards anywhere (with support to GamiPress live shortcode embedder).
* Widget to place any calendar of rewards on any sidebar.
* Block to place the current day's reward anywhere.
* Shortcode to place the current day's reward anywhere (with support to GamiPress live shortcode embedder).
* Widget to place the current day's reward on any sidebar.

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

= 1.1.8 =

* **New Features**
* New shortcode, block, and widget to display the current day's reward.

= 1.1.7 =

* **Improvements**
* Improved Daily Login Rewards menu.

= 1.1.6 =

* **Bug Fixes**
* Fixed minified css file.

= 1.1.5 =

* **Improvements**
* Improved responsive styles.

= 1.1.4 =

* **Bug Fixes**
* Fixed the calendar selector in the widgets area.

= 1.1.3 =

* **Improvements**
* Added required parameters in the 'get_the_excerpt' filter to avoid compatibility issues.

= 1.1.2 =

* **New Features**
* Added new options in GamiPress Earnings block to display the calendar rewards.
* Added the attribute "show_calendar_rewards" in [gamipress_earnings] shortcode to display the calendar rewards.

= 1.1.1 =

* **Improvements**
* Check for new rewards when visit the website to track users that have checked the option "remember me" on log in.
* Added new checks when applying penalties to only revoke calendar rewards that user has truly earned.
* Added new log entries to meet when a calendar has been restarted or all its rewards revoked when user breaks a consecutive log in streak.

= 1.1.0 =

* **Improvements**
* Greatly improved all the daily login checks.
* Added new checks to prevent duplicated rewards.
* Added new checks to award the correct reward if any of the current rewards have been revoked manually.
* Ensure to correctly reset the rewards count for consecutive calendars.

= 1.0.9 =

* **Improvements**
* Updated deprecated jQuery functions.

= 1.0.8 =

* **New Features**
* Added support to GamiPress 1.8.0.
* **Bug Fixes**
* Fixed achievements and rank ajax selector.
* **Improvements**
Make use of WordPress security functions for ajax requests.

= 1.0.7 =

* **Improvements**
* Increased login listener priority to keep add-on working included if other plugins caused errors on the user log in.
* Improved some text labels and fixed some typos.

= 1.0.6 =

* **Improvements**
* Added add-on icon on settings.

= 1.0.5 =

* **New Features**
* Added support to GamiPress 1.7.0.
* **Bug Fixes**
* Prevent usage of undefined vars on earnings template.

= 1.0.4 =

* **New Features**
* Full support to GamiPress Gutenberg blocks.

= 1.0.3 =

* **New Features**
* New setting to align stamp with reward's image.
* New options to make any calendar repeatable limited or unlimited times.
* **Bug Fixes**
* Fixed wrong reward removed when clicking on the remove reward button.
* **Improvements**
* Added new fallback to the first calendar reward if current reward gets deleted.
* Now, calendar completion will be checked through latest reward and not the full list, this avoid issues if rewards gets deleted before complete the calendar.
* **Developer Notes**
* Reworked full internal checks to make the compatible with the new repeatable feature.
* Added a bunch of new functions and hooks to make add-on more extensible and more easy to customize.
* Added GamiPress caching utility usage to speed up add-on checks.
* Added new CSS rules based on the new stamp alignment functionality.

= 1.0.2 =

* **New Features**
* Added support to GamiPress admin bar menu.

= 1.0.1 =

* **Bug Fixes**
* Fixed typo on user earnings output.
* Fixed wrong priority when overwriting a template.

= 1.0.0 =

* Initial release.
