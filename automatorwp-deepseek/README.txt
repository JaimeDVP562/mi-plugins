# AutomatorWP - DEEPSEEK Integration

Custom integration between **AutomatorWP** and **DEEPSEEK API**.

This plugin enables AI-powered actions within AutomatorWP by connecting to the DEEPSEEK API and processing prompts dynamically.

---

## 📌 Description

This project integrates DEEPSEEK with AutomatorWP to allow automated workflows powered by AI-generated responses.

The plugin provides:

- Admin configuration for API credentials
- Secure storage of API key
- API connection handler
- Prompt submission and response handling
- Basic error management

---

## 🚀 Features

- Admin settings page for API configuration
- Secure API key storage using AutomatorWP options
- HTTP request handling to DEEPSEEK API
- JSON payload processing for Chat and Reasoner models
- Response parsing and return via {deepseek_response} tag
- Real-time connection verification using AJAX

---

## 🛠 Technical Overview

- Built as a custom WordPress plugin
- Uses WordPress HTTP API (`wp_remote_request`)
- Hooks into AutomatorWP actions/triggers using official classes
- Modular structure with separation of logic (Admin, API, Actions, Tags)
- Follows WordPress plugin development standards

---

## 📂 Project Structure


- Automatorwp-deepseek.php: Main plugin file
- Includes/admin.php: Settings interface
- Includes/functions.php: API connection logic
- Includes/actions/generate-text.php: Action implementation
- Includes/tags.php: Response tags handler
- Assets/: Minified CSS/JS and SVG icons
