# AutomatorWP - Grok Integration

Custom integration between **AutomatorWP** and the **xAI Grok API**.

This plugin enables advanced AI-powered actions within AutomatorWP by connecting to xAI's infrastructure and processing prompts dynamically using Grok-2 models.

---

## 📌 Description

This project integrates Grok (xAI) with AutomatorWP to allow intelligent automated workflows powered by state-of-the-art AI.

The plugin provides:
- **Admin configuration** for xAI API credentials.
- **Secure storage** of the API key using WordPress options.
- **Robust API connection handler** compatible with xAI's endpoints.
- **Multi-action support**: Text generation, Sentiment analysis, and Summarization.
- **Dynamic Response Handling** via the `{response}` tag.

---

## 🚀 Features

- **Admin settings page** for seamless API configuration.
- **Real-time connection verification** using AJAX to ensure the API Key is active.
- **HTTP request handling** via WordPress API (`wp_remote_post`) to `api.x.ai`.
- **JSON payload processing** optimized for `grok-2-1212` and `grok-beta`.
- **Advanced Prompt Engineering**: Specialized actions for Sentiment Analysis and Content Summarization.
- **Modular tag system**: Use AI results in any subsequent automation step.

---

## 🛠 Technical Overview

- Built as a professional, object-oriented WordPress plugin (Singleton pattern).
- High-performance communication using the WordPress HTTP API.
- Deep integration with AutomatorWP core classes and hooks.
- **Security-focused**: Includes Nonce verification and data sanitization at every step.
- **Optimized Assets**: Minified CSS and JavaScript for faster admin performance.

---

## 📂 Project Structure

- **automatorwp-grok.php**: Main plugin file (Singleton instance & Loader).
- **includes/admin.php**: Settings interface and Meta Box registration.
- **includes/functions.php**: Core API connection logic and tag definitions.
- **includes/ajax-functions.php**: Server-side handler for API key verification.
- **includes/scripts.php**: Enqueues and localizes admin assets.
- **includes/tags.php**: Logic for processing and replacing dynamic response tags.
- **includes/actions/**:
    - `generate-text.php`: General AI content creation.
    - `analyze-sentiment.php`: Sentiment classification (Positive/Negative/Neutral).
    - `summarize-content.php`: Post and text condensation logic.
- **assets/**: Minified CSS/JS and official Grok SVG icons.

---

## 🔑 Requirements

- **AutomatorWP** installed and active.
- **xAI API Key** with active credits (managed at [console.x.ai](https://console.x.ai/)).
- PHP 7.4 or higher.