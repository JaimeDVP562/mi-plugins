=== AutomatorWP - ActiveCampaign ===
Contributors: automatorwp, rubengc, dioni00
Tags: activecampaign, crm, automatorwp, marketing, automation
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.1.3
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with ActiveCampaign

== Description ==

[ActiveCampaign](https://www.activecampaign.com/ "ActiveCampaign") is a powerful platform that lets you connect with customers everywhere they want to interact with you.

= Triggers =

* User added to ActiveCampaign.
* Tag added to user.
* Tag removed from user.
* User unsubscribed from ActiveCampaign.
* User added to list.
* Contact added to ActiveCampaign.
* Tag added to contact.
* Tag removed from contact.
* Contact unsubscribed from ActiveCampaign.
* Contact added to list.

= Actions =

* Add user to ActiveCampaign.
* Add tag to user.
* Remove tag from user.
* Create tag and assign to user.
* Add user to list.
* Remove user from list.
* Add contact to ActiveCampaign.
* Add tag to contact.
* Remove tag from contact.
* Create tag and assign to contact.
* Add contact to list.
* Remove contact from list.
* Update contact in ActiveCampaign.
* Update user custom field with a value.
* Update contact custom field with a value.

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

= 1.1.3 =

* **Bug Fixes**
* Fixed bug related to first name and last name in add user action.

= 1.1.2 =

* **Improvements**
* Fixed deprecated function to adapt to the PHP latest version.

= 1.1.1 =

* **Improvements**
* Improved authorization security checks.

= 1.1.0 =

* **Bug Fixes**
* Fixed bug related to anonymous triggers.

= 1.0.9 =

* **New Features**
* New trigger: User added to list.
* New trigger: Contact added to list.

= 1.0.8 =

* **Bug Fixes**
* Fixed a bug that causes lists are not getting loaded correctly.
* Fixed a PHP warning related to user information.

= 1.0.7 =

* **New Features**
* New action: Update user custom field with a value.
* New action: Update contact custom field with a value.

= 1.0.6 =

* **New Features**
* New action: Update contact in ActiveCampaign.
* **Improvements**
* Improved "Add contact to ActiveCampaign" action to update the contact.

= 1.0.5 =

* **Bug Fixes**
* Fixed a PHP warning related to webhooks.

= 1.0.4 =

* **Bug Fixes**
* Fixed a bug that causes contact is not detected in ActiveCampaign if email contains special characters.

= 1.0.3 =

* **Bug Fixes**
* Fixed a bug that causes tags are not getting loaded correctly.

= 1.0.2 =

* **Bug Fixes**
* Fixed issue with anonymous tags rendering.

= 1.0.1 =

* **New Features**
* New action: Create tag and assign to user.
* New action: Create tag and assign to contact.

= 1.0.0 =

* Initial release.
