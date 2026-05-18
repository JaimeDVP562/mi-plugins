# AutomatorWP - Security Optimizer Integration

This plugin integrates AutomatorWP with SiteGround Security (SG Security), allowing you to automate security actions through AutomatorWP automations.

## Features

* **Block a user:** Block a WordPress user using SG Security's native service.
* **Unblock a user:** Unblock a previously blocked user.
* **Block an IP address:** Block the IP address associated with a selected user.
* **Unblock an IP address:** Unblock the IP address associated with a selected user.
* **Force all users to log out:** Log out all users from the site.
* **Force all users to reset their passwords:** Force all users, except administrators, to reset their passwords on their next login.

## Requirements

* AutomatorWP plugin active.
* SiteGround Security (SG Security) plugin active.

## How it works

1. Activate the **AutomatorWP**, **Security Optimizer**, and **SG Security** plugins, as the new actions will not appear unless these are active.
2. Go to AutomatorWP and create a new automation.
3. Add the trigger of your choice (we recommend testing with the WordPress trigger "User views a post" as it is the fastest).
4. Add the desired action from Security Optimizer, then save and activate.

## Installation

1. Upload the plugin to the `/wp-content/plugins/` directory.
2. Activate it through the WordPress admin panel.
3. Ensure that AutomatorWP and SG Security are active.

## Notes

* The trigger must be fulfilled by an administrator since these are security-related functions.
* The administrator is not affected by these actions, they are only applied to other users.