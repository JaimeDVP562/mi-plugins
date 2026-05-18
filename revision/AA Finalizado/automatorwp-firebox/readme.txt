=== AutomatorWP - FireBox ===
Contributors: automatorwp, rubengc
Tags: firebox, popup, automatorwp, automation, marketing
Requires at least: 4.4
Tested up to: 6.4
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with FireBox

== Description ==

[FireBox](https://wordpress.org/plugins/firebox/ "FireBox") is a powerful popup builder for WordPress that lets you create beautiful popups, slide-ins, and overlays to grow your email list, promote offers, and engage your visitors.

This integration allows you to use FireBox popup events as triggers in AutomatorWP automations, and to control FireBox popups from AutomatorWP actions.

= Triggers (logged-in users) =

* User opens a popup.
* User closes a popup.
* User clicks a conversion element in a popup.
* User submits a form inside a popup.

= Triggers (guests) =

* Guest opens a popup.
* Guest closes a popup.
* Guest clicks a conversion element in a popup.
* Guest submits a form inside a popup.

= Actions =

* Enable a popup (sets status to published).
* Disable a popup (sets status to draft).
* Show a popup to the user on their next page load.

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

== Requirements ==

* [AutomatorWP](https://wordpress.org/plugins/automatorwp/) must be installed and active.
* [FireBox](https://wordpress.org/plugins/firebox/) 3.x must be installed and active.

== Frequently Asked Questions ==

= Does this work with guest (non-logged-in) visitors? =

Yes. Open, close, conversion, and form submission events all have a guest variant that fires for non-logged-in visitors. Guest automations require the "Assign anonymous user" action to be configured in AutomatorWP.

= How does the "Show popup" action work? =

When the action runs, it stores the popup ID in a short-lived transient (10 minutes). On the user's next frontend page load the popup is opened automatically via the FireBox JavaScript API.

= Which FireBox hooks does this integration use? =

* `firebox/box/on_open` — fired when a popup opens.
* `firebox/box/on_close` — fired when a popup closes.
* `firebox/form/success` — fired after a successful form submission inside a popup.
* `FireBoxConversion` (browser CustomEvent) — fired when a user clicks a tracked Button or Image block inside a popup; relayed to PHP via AJAX.

== Screenshots ==

== Changelog ==

= 1.0.0 =

* Initial release.
* Triggers: user/guest opens a popup, closes a popup, clicks a conversion element, submits a form.
* Actions: enable popup, disable popup, show popup to user.
* Verified against FireBox 3.x hook names and JavaScript API.
