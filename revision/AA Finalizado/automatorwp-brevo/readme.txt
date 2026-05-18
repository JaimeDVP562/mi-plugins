=== AutomatorWP - Brevo ===
Contributors: automatorwp, rubengc, dioni00, DavidHervas12
Tags: brevo, automatorwp, marketing, automation, crm, management
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with Brevo

== Description ==

[Brevo](https://www.brevo.com/ "Brevo") helps you to simplify your work and get more done. Plan, track, and manage any type of work with project management that flexes to your team's needs.

= Actions =

* Add contact to a list.
* Create contact.
* Remove contact from a list.
* Create a list.
* Send transactional email.
* Create a deal

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

== API Token management ==

= From Brevo account =

1.Log in to your Brevo account at https://app.brevo.com
2.Click on your profile icon in the top-right corner and select SMTP & API (or API keys) from the dropdown.
3.Click Create a New API Key.
4.Give your API key a descriptive name (e.g., "AutomatorWP Integration") and select the permissions you need.
5.Click Generate and copy the API key. Keep it safe, as you won’t be able to see it again.

= From WordPress backend (AutomatorWP) =

1.Navigate to AutomatorWP → Settings → Integrations.
2.Find Brevo (Sendinblue) in the list and click Connect or Configure.
3.Paste the API key you copied from Brevo into the API Key field.
4.Click Save Changes to finalize the connection.
5.Test the connection to ensure AutomatorWP can communicate with Brevo successfully.

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.0.0 =

* Initial release.

* Improved handling when creating contacts:
	- Added error handling (`is_wp_error`) after the POST attempt to create a contact.
	- If creation fails, the plugin searches for an existing contact by email (`/contacts?email=...`) and, if found, performs a `PUT` to update it with available attributes.
	- The update payload preserves the `email` and includes `attributes` when present.
	- Returns more precise HTTP codes from the `PUT` when applicable.

* Fixed last name attribute mapping:
	- The attribute key was changed from `APELLIDO` (singular) to `APELLIDOS` (plural) to match Brevo's expected attribute name and ensure the last name is stored correctly.

* General robustness and HTTP code handling:
	- Better handling of network/API errors and clearer return codes (e.g. `201` for creation, `204` for update).

* Files without relevant changes:
	- `includes/ajax-functions.php` — AJAX behavior unchanged.
	- `assets/js/automatorwp-brevo.js` — admin/config JS unchanged.
	- Other files in `includes/actions/` (except `create-contact.php`) keep the same logic.

---
Recommendations:

- Test the integration on a staging environment with existing contacts to verify the update (`PUT`) flow.
- Check in your Brevo account the exact custom attribute names (for example `NOMBRE`, `APELLIDOS`) and ensure they match the keys sent by the plugin.
- Consider adding conditional logs or more detailed error messages to simplify future debugging.
