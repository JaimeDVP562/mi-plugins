=== GamiPress - Referrals ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, gamify, point, achievement, badge, award, reward, credit, engagement, ajax
Requires at least: 4.4
Tested up to: 6.8
Stable tag: 1.2.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Add a complete referral system to award users who refer visitors, sign ups and sales.

== Description ==

Referrals gives you the ability to add a complete referral system to your website and award users who refer visitors, sign ups and sales.

In just a few minutes, you will be able to to add affiliate marketing to your site and boost your traffic.

Referrals extends and expands GamiPress adding new activity events and features.

= New Events =

* Get a referral visit: When an affiliate gets a referral visit.
* Get specific referral visit: When an affiliate gets a referral visit on a specific post.
* Get a referral sign up: When an affiliate gets a referral converted as a new user.
* Register to website through a referral: When a user registers through a referral.
* Reach a referral visits amount: When an affiliate reaches a referral visits amount.
* Reach a referral sign ups amount: When an affiliate reaches a referral sign ups amount.

= New events from the WooCommerce & Easy Digital Downloads integration =

* Get a referral sale: When an affiliate gets a new referral sale.
* Complete a purchase through a referral: When a user completes through a referral.
* Get a referral product sale: When an affiliate gets a new referral sale of a product.
* Purchase a product through a referral: When a user purchases a product through a referral.
* Get a referral sale refunded: When an affiliate sale gets refunded.
* Refund product purchased through a referral: When a user refunds a product that was purchased through a referral.
* Reach a referral sales amount: When an affiliate reaches a referral sales amount.
* Reach a referral sales refunded amount: When an affiliate reaches a referral sales refunded amount.

= Features =

* Award users for refer new visitors, sign ups and sales.
* Complete referrals tracking.
* Configurable referrals URL schema.
* Referral URL generator where users can convert any website URL into a referral URL.
* Block, Shortcode and widget to place user's referrals counters separated by referral types (visits or sign ups).
* Blocks, shortcodes and widgets to place the affiliate ID and referral URLs anywhere.

= WooCommerce & Easy Digital Downloads integration features =

* Built-in integration with WooCommerce and Easy Digital Downloads.
* Track referral sales and award your affiliates for each sale they refer (with support for refunds).
* Setup a commission percent of points for referral purchases (with support to revoke commissions on refunds).

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

== Frequently Asked Questions ==

== Changelog ==

= 1.2.0 =

* **New Features**
* Migrated referral storage from GamiPress logs to a dedicated custom table using CT library.
* New admin views: Referrals list page (with delete) and detail page (read-only with delete button).
* New shortcode: [gamipress_referrals_list] to display referral details for the logged-in user on the frontend.
* Upgrade process with admin notification and one-click migration from old logs to new custom table.
* All queries and inserts check migration status to support both old (logs) and new (custom table) storage.

= 1.1.6 =

* **Bug Fixes**
* Fixed bug related with duplicity in referral sales.

= 1.1.5 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.
* Added button to copy referral URL

= 1.1.4 =

* **Improvements**
* Store affiliate counts in user meta for faster loading and extensibility.

= 1.1.3 =

* **Improvements**
* Added cache to the user's affiliate to speed up event listeners.

= 1.1.2 =

* **New Features**
* New event for WooCommerce: Get a referral product sale.
* New event for WooCommerce: Purchase a product.
* New event for Easy Digital Downloads: Get a referral product sale.
* New event for Easy Digital Downloads: Purchase a product.

= 1.1.1 =

* **Bug Fixes**
* Prevent to query affiliates by ID if parameter is set by username.

= 1.1.0 =

* **Bug Fixes**
* Fixed typo in EDD integration causing errors when registering a referral refund.

= 1.0.9 =

* **Improvements**
* Multisite: Added support to detect if WooCommerce and EDD are installed in a subsite.

= 1.0.8 =

* **New Features**
* New event: Reach a referral visits amount.
* New event: Reach a referral sign ups amount.
* New event: Reach a referral sales amount.
* New event: Reach a referral sales refunded amount.

= 1.0.7 =

* **Bug Fixes**
* Fixed a typo in the Referrals Count block type options.

= 1.0.6 =

* **New Features**
* Added affiliate information in the user profile.
* Integration with WooCommerce to track referral purchases.
* Integration with Easy Digital Downloads to track referral purchases with support for refunds.
* Added new options to the [gamipress_referrals_count] shortcode to display the count of referral sales and referral sales refunded.
* Added the ability to award a commission percent of points in WooCommerce/EDD referral purchases with support for refunds.
* **Improvements**
* Renamed some events to make them more clear.

= 1.0.5 =

* **Improvements**
* Updated deprecated jQuery functions.

= 1.0.4 =

* **New Features**
* Added support to GamiPress 1.8.0.

= 1.0.3 =

* **New Features**
* New event: Register to website through a referral.
* Added support to GamiPress 1.7.0.
* **Improvements**
* Improved user selector on widgets area and shortcode editor.
* Great amount of code reduction thanks to the new GamiPress 1.7.0 API functions.

= 1.0.2 =

* **New Features**
* Added the event "Specific referral visit".

= 1.0.1 =

* **New Features**
* Full support to GamiPress Gutenberg blocks.

= 1.0.0 =

* Initial release.
