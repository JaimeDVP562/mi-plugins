=== AutomatorWP - Cohere ===
Contributors: automatorwp
Tags: cohere, automatorwp, ai, automation, nlp
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with Cohere AI.

== Description ==

[Cohere](https://cohere.com/ "Cohere") is an enterprise AI platform offering large language models for text generation, semantic search, classification, and reranking. This integration connects AutomatorWP with Cohere so you can run AI-powered actions directly from your WordPress automations.

= Actions (8 total) =

**Text Generation**

* Send a prompt to Cohere and store the AI-generated response.
* Send a message in a Cohere conversation maintaining context across turns.
* Clear a Cohere conversation history.

**Text Processing**

* Summarize text with Cohere.
* Translate text with Cohere.
* Classify text into categories with Cohere.

**Search & Retrieval**

* Rerank a list of documents by relevance to a query with Cohere.
* Generate text embeddings with Cohere.

= Tags =

* **Cohere Response** — AI-generated response to a prompt.
* **Cohere Citations** — Cited text fragments from documents.
* **Cohere Conversation Response** — AI reply in a multi-turn conversation.
* **Cohere Conversation Turns** — Number of turns in the conversation.
* **Cohere Summary** — AI-generated summary of the text.
* **Cohere Translation** — Translated text.
* **Cohere Category** — Best matching category for classified text.
* **Cohere Category Reason** — Brief explanation for the classification.
* **Cohere Rerank Results** — JSON array of ranked documents with relevance scores.
* **Cohere Top Ranked Document** — Text of the most relevant document.
* **Cohere Embeddings** — JSON array of float values representing the text embedding.
* **Cohere Embedding Dimensions** — Number of dimensions in the embedding vector.

= Requirements =

* [AutomatorWP](https://wordpress.org/plugins/automatorwp/)
* A Cohere account with an API key

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

== Cohere setup ==

= Get your API Key =

1. Log in to your Cohere account at https://dashboard.cohere.com.
2. Navigate to **API Keys** in the left sidebar.
3. Click **New API Key**, give it a name, and click **Create**.
4. Copy the generated key — it is only shown once.

= Configure the plugin =

1. Navigate to **AutomatorWP → Settings → Cohere**.
2. Paste your **API Key** into the corresponding field.
3. Click **Authorize** to verify the connection. A confirmation message will appear if the key is valid.

== Frequently Asked Questions ==

= Does this plugin require AutomatorWP? =

Yes. The free [AutomatorWP](https://wordpress.org/plugins/automatorwp/) plugin must be installed and activated.

= How do I get my Cohere API key? =

1. Log in at https://dashboard.cohere.com.
2. Go to **API Keys** in the left sidebar.
3. Click **New API Key**, name it, and click **Create**.
4. Copy the key — it is only displayed once.

= Which Cohere models are available? =

**Chat (Command):**
* `command-a-03-2025` — Most powerful, 256k context window.
* `command-a-reasoning-08-2025` — Extended thinking, 256k context.
* `command-a-vision-07-2025` — Image and text input, 128k context.
* `command-r-plus-08-2024` — Best for complex RAG workflows, 128k context.
* `command-r7b-12-2024` — Fast and lightweight, 128k context.

**Embed:**
* `embed-v4.0` — Text and images, variable dimensions (256–1536).
* `embed-english-v3.0` / `embed-multilingual-v3.0` — 1024 dimensions.

**Rerank:**
* `rerank-v4.0-pro` — Multilingual, best quality.
* `rerank-v4.0-fast` — Multilingual, low latency.
* `rerank-v3.5` — English and JSON documents.

= How does the multi-turn conversation work? =

The conversation history is stored in WordPress (not on Cohere's servers, since Cohere is stateless). Each message you send includes the full history as context. Use a unique Conversation ID (e.g. `{user_id}`) to maintain separate conversations per user. Use the "Clear conversation" action to reset the history when needed.

= What is the difference between Classify and Rerank? =

* **Classify** assigns a single category label to a piece of text from a list you define (e.g. positive, negative, neutral).
* **Rerank** sorts a list of documents by how relevant they are to a search query, returning a ranked list with relevance scores.

= What are embeddings used for? =

Embeddings are numerical vector representations of text. They are useful for semantic similarity, clustering, and feeding into external search or recommendation systems. The output is stored as a JSON array of float values.

= Which model is used for translation? =

The "Translate text" action uses `command-a-translate-08-2025`, Cohere's dedicated translation model supporting 23 languages.

= Which model is used for classification? =

Classification uses a Command chat model (selectable) with a structured prompt that returns a JSON object with the matched category and a brief reason. This approach does not require pre-labeled training examples.

== Screenshots ==

1. Cohere settings panel in AutomatorWP — enter your API Key and verify the connection.
2. Available Cohere actions in the action selector.
3. Configure the model, prompt, and response options for the Send Prompt action.

== Changelog ==

= 1.0.0 =

* Initial release.
* 8 actions: Send Prompt, Multi-turn Conversation, Clear Conversation, Summarize, Translate, Classify, Rerank, Generate Embeddings.
* Supports Command, Embed, and Rerank model families.
* Conversation history stored in WordPress with configurable max history window.
* Usage limiting per user per action (daily, weekly, or monthly).
* Custom response tags to reuse AI output in subsequent actions.
* "Authorize" button in settings to verify the API key before saving.
