=== AutomatorWP - LatePoint ===
Contributors: automatorwp, rubengc, AdrianRuiz45, SergioGarcia
Tags: latepoint, automatorwp, appointments, automation, booking
Requires at least: 4.4
Tested up to: 6.5
Stable tag: 1.1.0
License: GNU AGPLv3
License URI: https://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with LatePoint to automate your booking workflows.

== Description ==

[LatePoint](https://latepoint.com/ "LatePoint") is a powerful appointment booking plugin that helps businesses streamline their scheduling process.

This integration has been professionally refactored to support advanced filtering and improved data extraction.

= Triggers =

* **User created a booking:** Triggered when a new appointment is made. Supports filtering by specific services.
* **User updated a booking:** Triggered when an appointment is modified. Supports filtering by specific services.
* **A customer is created:** Triggered when a new customer profile is generated in LatePoint.
* **User logs in via LatePoint:** Specific trigger for LatePoint customer authentication.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure both AutomatorWP and LatePoint are active.

== Changelog ==

= 1.1.0 =
* **OOP Refactor:** Implemented a new Abstract Trigger Base class to centralize logic and follow DRY principles.
* **Dynamic Service Filtering:** Triggers now allow selecting specific LatePoint services via dynamic dropdowns.
* **Extended Tag System:** Added new dynamic tags: {service_name}, {agent_name}, and {booking_start_date}.
* **Stability Fix:** Resolved fatal error "Class not found" by optimizing the plugin loading sequence.
* **i18n Sync:** Updated .pot files to include all new technical strings.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.0 =
Major architectural update. Improved stability and added service-specific filtering for booking triggers.