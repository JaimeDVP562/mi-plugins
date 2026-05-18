=== AutomatorWP - Keap ===
Contributors: automatorwp, rubengc
Tags: keap, automatorwp, crm, marketing automation, email marketing
Requires at least: 4.4
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with Keap - Powerful CRM & Email Marketing Automation

== Description ==

[Keap](https://www.keap.com/ "Keap") is a comprehensive CRM and marketing automation platform designed to help small businesses and entrepreneurs automate their sales, marketing, and customer relationship management.

This plugin seamlessly integrates AutomatorWP with Keap, enabling you to create sophisticated automation workflows based on WordPress events.

= Features =

* **Actions**: Automate tasks in Keap based on WordPress events
  - Create and send emails
  - Send messages to contacts
  - Create new contacts
  - Update existing contacts
  - Add contacts to campaigns and sequences
  - Manage contact tags and custom fields
  - Create transactions (sales)

* **Tags/Merge Fields**: Use dynamic WordPress data in your automations
  - User data
  - Post/Page data
  - Custom field values

* **Smart Caching**: Optimized API performance with intelligent data caching
* **Error Handling**: Comprehensive logging and error reporting
* **Easy Setup**: Simple OAuth authentication with Keap


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

= How do I get my Keap API credentials? =

1. Log in to your Keap account
2. Go to Settings → API
3. Create a new API Key or OAuth app
4. Copy your Access Token and paste it in AutomatorWP Settings

= Can I use this with multiple Keap accounts? =

Currently, this integration supports one Keap account per WordPress install. You can configure multiple automations within that single account.

= What merge fields are available? =

You can use any AutomatorWP merge fields including:
- {user_email}, {user_first_name}, {user_last_name} - User data
- {post_title}, {post_content} - Post/Page data
- Custom fields via AutomatorWP's merge field system

= How often is the cache refreshed? =

By default, cache is set to 12 hours. You can modify this via the `automatorwp_keap_cache_expiration` filter.

== Screenshots ==

== Changelog ==

= 1.1.0 =

* NEW: Create Contact action - Automatically create new contacts in Keap
* NEW: Update Contact action - Modify existing contact information
* NEW: Add Contact Tag action - Apply tags to organize contacts
* NEW: Add to Campaign action - Enroll contacts in campaigns
* NEW: Contact Added trigger - Fire automations when new contacts are added
* NEW: Intelligent caching system - Reduces API calls and improves performance
* NEW: Enhanced error logging - Debug logs in /logs/keap.log
* NEW: Cache management utilities
* IMPROVED: Better API error handling with `automatorwp_keap_handle_api_response()`
* IMPROVED: Comprehensive documentation and setup guide
* IMPROVED: Security and validation improvements
* IMPROVED: Support for more Keap API endpoints

= 1.0.0 =

* Initial release
* Create Mail action - Create emails in Keap
* Send Message action - Send messages to contacts
