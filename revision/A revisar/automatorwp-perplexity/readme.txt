=== AutomatorWP - Perplexity ===
Contributors: automatorwp
Tags: automatorwp, automation, perplexity, ai, openai
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 7.4
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with Perplexity AI to send prompts, run deep research, hold multi-turn conversations, summarize URLs, translate text, analyse sentiment, extract structured data and classify content.

== Description ==

[AutomatorWP](https://automatorwp.com/) is the most powerful automation plugin for WordPress. This add-on connects it with [Perplexity AI](https://www.perplexity.ai/), an AI search engine that combines large language models with real-time internet search.

= Actions =

* Send a prompt to Perplexity and store the AI-generated response
* Run a deep research on Perplexity and store the full report
* Send a message in a Perplexity conversation maintaining context across turns
* Clear a Perplexity conversation history
* Summarize a URL with Perplexity
* Translate text with Perplexity
* Analyse the sentiment of a text with Perplexity
* Extract structured data from text with Perplexity
* Classify text into categories with Perplexity

= Tags =

* **Perplexity Response** – The full AI-generated text response
* **Perplexity Search Results** – Source URLs and titles cited by Perplexity
* **Perplexity Research Report** – Full deep-research report
* **Perplexity Research Search Results** – Sources cited in the research report
* **Perplexity Conversation Response** – AI reply in a multi-turn conversation
* **Perplexity Conversation Search Results** – Sources cited in the conversation
* **Perplexity Conversation Turns** – Number of turns in the conversation
* **Perplexity Summary** – URL summary text
* **Perplexity Translation** – Translated text
* **Perplexity Sentiment** – Detected sentiment (positive/negative/neutral/mixed)
* **Perplexity Sentiment Score** – Confidence score for the detected sentiment
* **Perplexity Extracted Data** – Extracted structured data as JSON
* **Perplexity Category** – Best matching category for classified text
* **Perplexity All Matched Categories** – All matching categories (comma-separated)
* **Perplexity Category Confidence** – Confidence score for the primary category
* **Perplexity Category Reason** – Brief explanation for the classification

= Requirements =

* [AutomatorWP](https://wordpress.org/plugins/automatorwp/)
* A Perplexity API key (see the FAQ for instructions)

== Installation ==

= From your WordPress dashboard =

1. Visit **Plugins > Add New**
2. Search for **AutomatorWP Perplexity**
3. Activate **AutomatorWP - Perplexity** from your Plugins page

= From WordPress.org =

1. Download AutomatorWP - Perplexity
2. Upload the `automatorwp-perplexity` directory to your `/wp-content/plugins/` directory
3. Activate AutomatorWP - Perplexity from your Plugins page

= Configure =

1. Go to **AutomatorWP > Settings > Perplexity**
2. Enter your Perplexity API Key
3. Click **Save credentials** to connect

== Frequently Asked Questions ==

= How do I get my Perplexity API key? =

1. Log in or create an account at https://www.perplexity.ai/
2. Go to **Settings → API** (or visit https://www.perplexity.ai/settings/api)
3. Click **Generate** / **+ New API Key**
4. Name it and click **Create**
5. Copy the key — it is only shown once

= Which models are available? =

* **sonar** – Lightweight, fast, ideal for simple queries
* **sonar-pro** – Advanced reasoning with real-time web search
* **sonar-deep-research** – Comprehensive multi-step research
* **sonar-reasoning-pro** – Advanced chain-of-thought with highest accuracy

= Does Perplexity access the internet in real time? =

Yes! Perplexity's Sonar models include real-time web search, so the responses contain up-to-date information with source citations.

= Is AutomatorWP required? =

Yes, you need the free [AutomatorWP](https://wordpress.org/plugins/automatorwp/) plugin installed and active.

== Screenshots ==

1. Configure your Perplexity API Key in AutomatorWP Settings
2. Available Perplexity actions in the action selector
3. Configure the model, prompt, and response options

== Changelog ==

= 1.1.0 =
* Added Deep Research action
* Added Multi-turn Conversation action
* Added Clear Conversation action
* Added Summarize URL action
* Added Translate Text action
* Added Sentiment Analysis action
* Added Extract Structured Data action
* Added Classify Text action
* Removed deprecated sonar-reasoning model (removed by Perplexity Dec 2025)
* Updated citations handling to use search_results field

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
Removed deprecated sonar-reasoning model. Updated API response handling for search results.

= 1.0.0 =
Initial release.
