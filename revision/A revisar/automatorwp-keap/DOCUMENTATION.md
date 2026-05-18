# AutomatorWP - Keap Integration

Powerful automation workflows connecting WordPress with Keap CRM.

## Overview

AutomatorWP - Keap seamlessly integrates your WordPress site with Keap, enabling sophisticated automation workflows. Create and manage contacts, send targeted emails, manage campaigns, and automate your marketing and sales processes.

## Features

### Actions (Automations)
- **Create Contact**: Automatically create new contacts in Keap from WordPress events
- **Update Contact**: Modify existing contact information
- **Add Contact Tag**: Apply tags to contacts for organization and segmentation
- **Add to Campaign**: Enroll contacts in Keap campaigns and sequences
- **Send Email**: Send targeted emails to contacts
- **Create Mail/Send Message**: Legacy actions for basic email functionality

### Triggers (Conditions)
- **Contact Added**: Trigger workflows when new contacts are added to Keap

### Smart Features
- **Intelligent Caching**: Reduces API calls with smart data caching
- **Error Handling**: Comprehensive logging for debugging
- **Merge Fields**: Use WordPress user data (name, email, etc.) in automations
- **OAuth Authentication**: Secure connection to Keap

## Setup Instructions

### 1. Get Your Keap API Credentials
1. Log in to your Keap account
2. Navigate to Settings → API
3. Create a new API Key or use existing OAuth app
4. Copy your Access Token

### 2. Configure AutomatorWP Plugin
1. Install and activate AutomatorWP plugin (if not already installed)
2. Install and activate AutomatorWP - Keap plugin
3. Go to AutomatorWP → Settings → Keap
4. Paste your Keap Access Token
5. Click "Save Credentials" to verify connection

### 3. Create Your First Automation
1. Go to AutomatorWP → Automations
2. Create a new automation
3. Choose a trigger (e.g., "User registered")
4. Add an action (e.g., "Create contact in Keap")
5. Configure the action with merge fields
6. Save and activate

## Available Merge Fields

When configuring actions, you can use these merge fields:
- `{user_email}` - User's email address
- `{user_first_name}` - User's first name
- `{user_last_name}` - User's last name
- `{user_ID}` - WordPress User ID
- `{post_title}` - Post/Page title
- `{post_content}` - Post/Page content

## Usage Examples

### Example 1: Create Keap Contact on Registration
1. Trigger: User Registered
2. Action: Create Contact in Keap
3. Fields:
   - Email: `{user_email}`
   - First Name: `{user_first_name}`
   - Last Name: `{user_last_name}`

### Example 2: Tag Contacts Based on Course Completion
1. Trigger: Course Completed
2. Action: Add Tag to Contact
3. Fields:
   - Contact Email: `{user_email}`
   - Tag: `Course_Graduate`

### Example 3: Multi-Step Sales Funnel
1. Trigger: Purchase Completed
2. Action: Create/Update Contact
3. Action: Add to Campaign (VIP Sales Sequence)
4. Action: Send Email (Confirmation)

## Troubleshooting

### "Keap integration is not configured"
- Go to AutomatorWP → Settings → Keap
- Verify your Access Token is entered correctly
- Click "Save Credentials" to test the connection

### API Errors in Logs
Enable debug logging:
1. Add to your `wp-config.php`:
   ```php
   define( 'AUTOMATORWP_KEAP_DEBUG', true );
   ```
2. Check the logs in `/wp-content/plugins/automatorwp-keap/logs/`

### Cache Issues
Clear all Keap caches:
1. Go to AutomatorWP Settings → Keap
2. Click "Refresh Cache" (if available)
3. Or add to your functions.php temporarily:
   ```php
   automatorwp_keap_clear_cache();
   ```

## API Limits

- Keap API limits vary by account type
- The plugin implements intelligent caching (12 hours default)
- Monitor your API usage in Keap Dashboard

## Support

For issues or feature requests:
1. Check the troubleshooting section above
2. Enable debug logging for more information
3. Contact AutomatorWP support with:
   - Debug logs
   - Automation configuration
   - Error messages

## Changelog

### Version 1.1.0
- **NEW**: Create Contact action
- **NEW**: Update Contact action
- **NEW**: Add Contact Tag action
- **NEW**: Add to Campaign action
- **NEW**: Contact Added trigger
- **NEW**: Intelligent caching system
- **NEW**: Enhanced error logging and debugging
- **IMPROVED**: Better API error handling
- **IMPROVED**: Comprehensive documentation

### Version 1.0.0
- Initial release
- Create Mail action
- Send Message action

## Requirements

- WordPress 4.4+
- PHP 7.4+
- AutomatorWP plugin
- Active Keap account with API access

## License

GNU AGPL v3.0 - See LICENSE file for details
