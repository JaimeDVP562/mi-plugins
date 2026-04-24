# AutomatorWP SendPulse – Testing & Configuration Guide

This document explains how to configure the SendPulse integration for AutomatorWP and how to test all available actions and triggers.

---

## 1. Configuration

### Prerequisites
- You must have an active SendPulse account.
- AutomatorWP and the SendPulse integration plugin must be installed and activated in your WordPress site.

### Steps
1. **Get SendPulse API Credentials:**
   - After creating your SendPulse account, go to **Account Settings > API > API access > Client credentials**.
   - Click on **Generate new API keys** to create your `App ID` and `Secret`.
   - Enable REST API if it is not already enabled.
   - Copy your `App ID` and `Secret` for use in WordPress.

2. **Configure in WordPress:**
   - Go to `AutomatorWP > Settings > SendPulse` in your WordPress admin.
   - Paste your `App ID` and `Secret`.
   - Click "Save" and then "Authorize" to connect.
   - If successful, you will see a confirmation message.

3. **Set up Webhooks (for triggers):**
   - In SendPulse, go to Account Settings > API > Webhooks.
   - Add a new webhook with the URL:
     `https://your-site.com/wp-json/automatorwp-sendpulse/v1/webhook`
   - Select the events you want to send (e.g., subscriber added, email opened, etc.).
   - Save the webhook.

---

## 2. Testing Actions

Actions are AutomatorWP steps that perform operations in SendPulse (e.g., add subscriber, remove tag).

### How to Test
1. Create a new Automation in AutomatorWP.
2. Add a trigger (e.g., "User registers").
3. Add a SendPulse action (e.g., "Add subscriber to addressbook").
4. Fill in the required fields (email, addressbook, etc.).
5. Save and activate the automation.
6. Perform the trigger action (e.g., register a new user).
7. Check SendPulse to verify the subscriber was added/updated.

### Available Actions
- Add subscriber to addressbook
- Remove subscriber from addressbook
- Add tag to subscriber (stored as custom variable)
- Remove tag from subscriber (custom variable)
- Create or update subscriber

---

## 3. Testing Triggers

Triggers are AutomatorWP events that fire when something happens in SendPulse (e.g., a subscriber clicks an email link).

### How to Test
1. Create a new Automation in AutomatorWP.
2. Add a SendPulse trigger (e.g., "Subscriber added").
3. Add any action (e.g., send email, add to group) for testing.
4. Save and activate the automation.
5. In SendPulse, perform the event (e.g., add a subscriber, send a campaign, click a link).
6. Check AutomatorWP logs (`AutomatorWP > Logs`) to verify the trigger fired.

### Available Triggers
- Subscriber added
- Subscriber deleted
- Subscriber updated
- Subscriber unsubscribed
- Email received
- Email opened
- Email link clicked

---

## 4. Troubleshooting
- Ensure your webhook URL is correct and publicly accessible.
- Check AutomatorWP logs for errors or status messages.
- Make sure your API credentials are valid and authorized.
- If actions or triggers do not work, re-save your SendPulse settings and re-authorize.

---

## 5. Additional Notes
- Tags in SendPulse are implemented as custom variables, not native tags.
- Only events supported by SendPulse webhooks and API are available.
- For advanced debugging, enable `WP_DEBUG` and check the `debug.log` file in `wp-content`.

---

For further help, contact AutomatorWP support or consult the official documentation.
