=== AutomatorWP - Drip ===
Contributors: automatorwp
Tags: drip, automatorwp, email marketing, automation, ecommerce
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with Drip.

== Description ==

[Drip](https://www.drip.com/ "Drip") is an email and marketing automation platform built for ecommerce brands. This integration connects AutomatorWP with Drip so you can fire automations from any Drip subscriber event and control Drip directly from your WordPress workflows.

= Triggers (29 total) =

**Subscriber Lifecycle**

* A new subscriber is created in Drip.
* A subscriber is deleted from Drip.
* A subscriber is reactivated in Drip.
* A subscriber subscribes to Drip email marketing.
* A subscriber unsubscribes from all Drip email marketing.
* A Drip subscriber updates their email alias.
* A Drip subscriber updates their email address.

**Tags**

* A tag is applied to a Drip subscriber.
* A tag is removed from a Drip subscriber.

**Email Engagement**

* A Drip subscriber receives an email.
* A Drip subscriber opens an email.
* A Drip subscriber clicks a link in an email.
* A Drip subscriber clicks a trigger link.
* A Drip subscriber's email bounces.
* A Drip subscriber marks an email as spam.

**Campaigns**

* A subscriber subscribes to a Drip campaign.
* A subscriber completes a Drip campaign.
* A subscriber unsubscribes from a Drip campaign.
* A subscriber is removed from a Drip campaign.

**Data Changes**

* A Drip subscriber updates a custom field.
* A Drip subscriber's lifetime value is updated.
* A Drip subscriber's time zone is updated.
* A Drip subscriber's lead score is updated.

**Behavioral**

* A subscriber performs a custom event in Drip.
* A Drip subscriber visits a page.

**Lead Scoring / Deliverability**

* A Drip subscriber becomes a lead.
* A Drip subscriber becomes a non-prospect.
* A Drip subscriber is marked as deliverable.
* A Drip subscriber is marked as undeliverable.

= Actions (12 total) =

**Subscriber Management**

* Create or update a subscriber.
* Remove a subscriber.

**Tags**

* Add a tag to a subscriber.
* Remove a tag from a subscriber.

**Campaigns**

* Add a subscriber to a campaign.
* Unsubscribe a subscriber from a campaign.
* Unsubscribe a subscriber from all email marketing.

**Events & Workflows**

* Record a custom event for a subscriber.
* Enroll a subscriber in a workflow.
* Remove a subscriber from a workflow.

**Shopper Activity (API v3)**

* Create or update an order in Drip.
* Create or update a cart in Drip.

= Requirements =

* [AutomatorWP](https://wordpress.org/plugins/automatorwp/)
* A Drip account with API access (see the FAQ for instructions)

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

== Drip setup ==

= Get your Account ID and API Key =

1. Log in to your Drip account at https://www.drip.com.
2. **Account ID** — look at the URL in your browser after logging in: `https://www.getdrip.com/YOUR_ACCOUNT_ID/dashboard`. Copy the numeric segment between `getdrip.com/` and `/dashboard`.
3. **API Key** — click your avatar in the top-right corner → **User Settings** → scroll to the **API Token** section → copy the token shown. If no token exists, click **Generate Token**.

= Configure the plugin =

1. Navigate to **AutomatorWP → Settings → Drip**.
2. Paste your **Account ID** and **API Key** into the corresponding fields.
3. Click **Try credentials** to verify the connection. A confirmation message will appear if the credentials are valid.

= Register the webhook (required for triggers) =

1. Copy the **Webhook URL** shown automatically in the plugin settings panel.
2. In Drip, go to **Settings → Webhooks → New Webhook**.
3. Paste the Webhook URL into the endpoint field.
4. Select the subscriber events you want to receive. Enable all `subscriber.*` events for full trigger coverage.
5. Click **Save**.

= Important notes =

* Your WordPress site must be publicly reachable for Drip webhooks to be delivered. The integration will not receive events on localhost unless you expose it with a tunnel tool such as ngrok or Cloudflare Tunnel.
* For the "Remove from workflow" action to work, the target workflow must be of type **Automation**. One-time broadcasts and Quick Send workflows do not support subscriber management via the API.
* The "Unsubscribe from campaign" action requires the subscriber to have an active subscription to that campaign. Calling it on a draft campaign or on a subscriber who is not currently subscribed will return an error.
* If the email field is left empty in an action, the plugin uses the email address of the WordPress user who triggered the automation.

== Frequently Asked Questions ==

= Does this plugin require AutomatorWP? =

Yes. The free [AutomatorWP](https://wordpress.org/plugins/automatorwp/) plugin must be installed and activated.

= How do I find my Drip Account ID? =

Log in to Drip and look at your browser's address bar. The URL will be in the format `https://www.getdrip.com/ACCOUNT_ID/dashboard`. The number between `getdrip.com/` and the next `/` is your Account ID.

= How do I get my Drip API Key? =

1. Log in to Drip at https://www.drip.com.
2. Click your avatar in the top-right corner → **User Settings**.
3. Scroll down to the **API Token** section.
4. Copy the token. If you do not see one, click **Generate Token**.

= Why are triggers not firing? =

Triggers are driven by Drip webhooks. The most common causes are:

1. The Webhook URL has not been registered in Drip (Settings → Webhooks).
2. The relevant events were not selected when creating the webhook in Drip.
3. Your WordPress site is not publicly reachable (e.g. running on localhost without a tunnel).

= What Drip events should I select when registering the webhook? =

Enable all `subscriber.*` events for full trigger coverage. You can also select only the specific events that match the triggers you plan to use.

= What happens if the subscriber email does not match a WordPress user? =

If the email in the Drip webhook payload does not match any WordPress user, the trigger fires using the first administrator account as the user context.

= Can I select tags, campaigns, and workflows from a dropdown? =

Yes. The "Add tag", "Remove tag", "Add to campaign", "Unsubscribe from campaign", "Enroll in workflow", and "Remove from workflow" actions include AJAX-powered dropdown selectors that load options directly from your Drip account.

= Why does "Remove from workflow" return a 404 error? =

The Drip API only supports removing subscribers from **Automation** type workflows. One-time broadcasts and Quick Send workflows do not support subscriber removal via the API. Make sure the workflow type is set to Automation in your Drip account.

= What is the Shopper Activity API? =

The "Create or update order" and "Create or update cart" actions use Drip's Shopper Activity API (v3), which tracks ecommerce events for abandoned-cart recovery and post-purchase automation.

= Does this plugin work on localhost? =

Actions (sending data to Drip) work on localhost. Triggers require a publicly reachable URL so Drip can deliver webhooks. Use a tunnel such as ngrok or Cloudflare Tunnel during local development.

== Screenshots ==

1. Drip settings panel in AutomatorWP — enter your Account ID and API Key and verify the connection.
2. Webhook URL displayed in the settings panel — copy it into Drip's webhook configuration.
3. Available Drip triggers in the trigger selector.
4. Available Drip actions in the action selector.

== Changelog ==

= 1.0.0 =

* Initial release.
* 29 triggers covering all Drip webhook events (subscriber lifecycle, tags, email engagement, campaigns, data changes, behavioral, lead scoring, and deliverability).
* 12 actions covering subscriber management, tags, campaigns, events, workflows, and shopper activity (API v3).
* Webhook endpoint at `/wp-json/automatorwp-drip/v1/webhook` for receiving all Drip events.
* AJAX-powered dropdown selectors for campaigns, workflows, and tags.
* "Try credentials" button in settings to verify the Account ID and API Key before saving.
* Admin notice displayed on any action when credentials are missing, prompting configuration.
