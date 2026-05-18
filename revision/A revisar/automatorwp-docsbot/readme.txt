=== AutomatorWP - DocsBot AI ===
Contributors: automatorwp
Tags: docsbot, automatorwp, ai, chatbot, automation
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with DocsBot AI

== Description ==

[DocsBot AI](https://docsbot.ai/ "DocsBot AI") lets you create AI-powered chatbots trained on your documentation. This integration connects AutomatorWP with DocsBot AI so you can trigger automations from chatbot events and use DocsBot actions in your workflows.

= Triggers =

* A lead is captured in a conversation.
* A conversation is escalated to a human.
* A user rates a conversation.
* A deep research job is completed.

= Actions =

* Send a question to the bot and get an AI answer (Chat Agent API).
* Run a semantic search on the bot knowledge base.
* Add a source to the bot (URL, YouTube, Sitemap, RSS Feed).
* Delete a source from the bot.
* Capture a lead on a conversation.

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

== Configuration ==

= Required credentials =

The settings panel (AutomatorWP → Settings → DocsBot AI) requires four values:

**API Key**

1. Log in to your DocsBot AI account at https://docsbot.ai
2. Click your avatar (top right) → Account Settings.
3. Go to the "API" tab and click "Generate API Key" (or copy an existing one).

**Team ID**

1. In the DocsBot dashboard, look at the URL of any page: `https://docsbot.ai/app/teams/TEAM_ID/...`
2. The segment after `/teams/` is your Team ID.

**Bot ID**

1. Open your bot in the DocsBot dashboard.
2. The URL will be: `https://docsbot.ai/app/teams/TEAM_ID/bots/BOT_ID/...`
3. The segment after `/bots/` is your Bot ID.

**Webhook Secret** *(optional but recommended)*

1. In the DocsBot dashboard, open your bot → Configure → Webhooks.
2. Add a new webhook endpoint and set the URL to the one shown in the plugin settings panel.
3. DocsBot will generate a Signing Secret — copy it and paste it into the "Webhook Secret" field in the plugin settings.
4. The Webhook URL is shown automatically in the plugin settings panel — just copy it from there.
5. Select the events you want to receive: `lead.created`, `conversation.escalated`, `conversation.rated`, `deep_research.done`.

= From WordPress backend (AutomatorWP) =

1. Navigate to AutomatorWP → Settings → DocsBot AI.
2. Paste your API Key, Team ID and Bot ID.
3. Optionally enter the Webhook Secret from your DocsBot webhook configuration.
4. Copy the Webhook URL shown in the settings panel.
5. Register that URL in your DocsBot bot's webhook settings, selecting the events you need: `lead.created`, `conversation.escalated`, `conversation.rated`, `deep_research.done`.
6. Click Save Changes.

== Frequently Asked Questions ==

= Why are triggers not firing? =

Triggers are driven by DocsBot webhooks. Make sure you have registered the Webhook URL in your DocsBot bot settings and selected the correct events. Also verify the Webhook Secret matches on both sides if you have configured one.

= Can I filter conversation.rated triggers by rating? =

Yes. When setting up the trigger in AutomatorWP you can choose to fire only for positive ratings, only for negative, or any rating.

= What happens if no WordPress user matches the lead email? =

If the email in the webhook payload does not match any WordPress user, the trigger will fire using the first administrator account as the user context.

= Can I chain Ask Bot and Delete Source actions in the same automation? =

Yes. Use the `{docsbot_source_id}` tag produced by the "Add source" action as the input to the "Delete source" action. Use the `{docsbot_answer}` tag from "Ask bot" in any subsequent action.

= Is the webhook signature verified? =

Yes. If you configure a Webhook Secret in the plugin settings, every incoming webhook is validated with HMAC-SHA256. Requests with an invalid or missing signature are rejected with HTTP 401.

== Screenshots ==

== Changelog ==

= 1.0.0 =

* Initial release.
* Triggers: Lead Created, Conversation Escalated, Conversation Rated, Deep Research Done.
* Actions: Ask Bot, Search Bot, Create Source, Delete Source, Capture Lead.
* HMAC-SHA256 webhook signature verification.
* Fallback to first administrator when webhook email does not match a WordPress user.
