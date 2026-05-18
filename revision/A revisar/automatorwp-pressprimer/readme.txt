=== AutomatorWP - PressPrimer Suite ===
Contributors: automatorwp
Tags: pressprimer_suite, automatorwp, integration
Requires at least: 4.4
Tested up to: 6.5
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with PressPrimer Suite

== Description ==

[PressPrimer Suite](https://wordpress.org/plugins/wp-shortcm/ "PressPrimer Suite for WordPress") is the easiest way to generate short links for your WordPress posts.

= Actions =

* Create short link.

== Installation ==

= From WordPress backend =

1. Navigate to Plugins -> Add new.
2. Click Upload Plugin next to "Add Plugins" title.
3. Upload the downloaded zip file and activate it.

= Direct upload =

1. Upload the downloaded zip file into your `wp-content/plugins/` folder.
2. Unzip the uploaded zip file.
3. Navigate to Plugins menu on your WordPress admin area.
4. Activate this plugin.

= Getting your PressPrimer Suite API Key =

1. Go to [PressPrimer Suite](https://pressprimer_suite.com/) and log in to your account (or create one if you don't have it).
2. Click on your profile icon in the top right corner.
3. Select **Settings** from the dropdown menu.
4. In the left sidebar, click on **Developer settings**.
5. Under **API**, click on **Generate token** or use an existing token.
6. Copy your API token.

= Configuring the integration =

1. In your WordPress admin, go to **AutomatorWP → Settings**.
2. Click on the **PressPrimer Suite** tab.
3. Paste your API Key in the **API Key** field.
4. Click **Try credentials** to verify the connection.
5. If successful, you'll see a confirmation message and you're ready to use PressPrimer Suite actions in your automations.

**Note:** This plugin is designed to work with PressPrimer Suite's free tier. Premium features requiring paid accounts are not included.

== Frequently Asked Questions ==



== Screenshots ==

No screenshots available for this integration.

== Changelog ==


= 1.0.2 =

* Rebuild the link‑creation action to remove all features incompatible with PressPrimer Suite’s free tier.
* Ensure the short‑link generation works reliably under free‑account limitations.
* Add on‑screen log output showing the generated short link after submitting the form.

= 1.0.1 = 

* Modify the functions to ensure compatibility with PressPrimer Suite free accounts.
* Remove the domain‑creation functions.

= 1.0.0 =

* Initial release.