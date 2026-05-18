=== AutomatorWP - WooCommerce ===
Contributors: automatorwp, rubengc, eneribs
Tags: woocommerce, automatorwp, store, ecommerce, product
Requires at least: 4.4
Tested up to: 6.8
Stable tag: 1.5.2
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Connect AutomatorWP with WooCommerce

== Description ==

[WooCommerce](https://woocommerce.com/?aff=28863 "WooCommerce") is a free, easily customizable eCommerce WordPress plugin for selling physical products and building an online business. WooCommerce allows you to create an online store, list your products or services, and sell them on your WordPress website.

= Triggers =

* User views a product.
* User adds a product to cart.
* User adds a product variation to cart.
* User removes a product from cart.
* User removes a product variation from cart.
* User purchases a product.
* User purchases a product variation.
* User purchases a product of a category.
* User purchases a product of a tag.
* User completes a purchase.
* User completes a purchase with a total greater than, less than or equal to a specific amount.
* User completes a purchase with a payment method.
* User completes a number of total purchases.
* User's order status changes to a status.
* User's order with a product changes its status to a status.
* User's order with a product of a category changes its status to a status.
* User's order with a product of a tag changes its status to a status.
* User completes an order with specific products.
* User completes an order containing specific products.
* Vendor gets a sale on a product.
* User reviews a product.
* User reviews a product with a rating greater than, less than or equal to a specific amount.
* User lifetime value is equal, less or greater than a value.
* **WooCommerce Memberships**
* User is added to a membership.
* User's access to membership gets cancelled.
* User's access to membership expires.
* User's access to membership changes to a status.
* **WooCommerce Subscriptions**
* User purchases subscription of a product.
* User purchases a subscription variation of a product.
* User renews subscription of a product.
* User renews subscription of a product variation.
* User cancels subscription of a product.
* User subscription of a product expires.
* User subscription of a product changes to a status.
* User's subscription payment retry status changes.
* User switches a subscription variation of a product.
* User cancels subscription of a category.
* User renews subscription of a category.
* User subscription of a category expires.

= Anonymous Triggers =

* Guest purchases a product.
* Guest purchases a product variation.
* Guest purchases a product of a category.
* Guest purchases a product of a tag.
* Guest completes a purchase.
* Guest completes a purchase with a total greater than, less than or equal to a specific amount.
* Guest completes a purchase with a payment method
* Guest's order status changes to a status.
* Guest's order with a product changes its status to a status.
* Guest's order with a product of a category changes its status to a status.
* Guest's order with a product of a tag changes its status to a status.

= Actions =

* Add email(s) to coupon.
* Add user to coupon.
* Create a coupon.
* Remove user from a coupon.
* Set status to order.
* **WooCommerce Memberships**
* Add user to membership.
* Remove user from membership.
* **WooCommerce Subscriptions**
* Cancel the user's subscription to all/specific product.
* Retry user's subscription payment

= Tags =

* Customer tags with the customer billing and shipping information.
* Order tags to use the order information (subtotal, tax, ip address, discount codes, etc).

= Filters =

* User has purchased a product.
* User has not purchased a product.
* User subscription for a product is active.
* User subscription for a product is inactive.
* User subscription variation for a product is active.
* User subscription variation for a product is inactive.
* User lifetime value is equal, less or greater than a value.

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

== Screenshots ==

== Changelog ==

= 1.5.2 =

* **New Features**
* New trigger: User subscription of a category changes to a status.
* New trigger: User purchases subscription of a category.
* **Improvements**
* Improved variation selection product variation triggers.

= 1.5.1 =

* **Improvements**
* Added renewal check on new subscription purchases. 

= 1.5.0 =

* **Improvements**
* Improved action Remove user from membership to get previously cancelled status.

= 1.4.9 =

* **New Features**
* New trigger: User cancels subscription of a category.
* New trigger: User renews subscription of a category.
* New trigger: User subscription of a category expires.

= 1.4.8 =

* **New Features**
* New trigger: User completes an order with specific products.
* New trigger: User completes an order containing specific products.

= 1.4.7 =

* **Bug fixes**
* Fixed field to indicate the order ID in the action Set status to order.

= 1.4.6 =

* **New Features**
* New action: Set status to order.

= 1.4.5 =

* **New Features**
* New trigger: User completes a number of total purchases.

= 1.4.4 =

* **New Features**
* New trigger: User's access to membership changes to a status.

= 1.4.3 =

* **Bug Fixes**
* Fixed tag "woocommerce_order_number".

= 1.4.2 =

* **New Features**
* New action: Remove user from a coupon.

= 1.4.1 =

* **Bug Fixes**
* Fixed renewal subscription events.

= 1.4.0 =

* **New Features**
* New trigger: User switches a subscription variation of a product.
* New filter: User subscription variation for a product is active/inactive.

= 1.3.9 =

* **Bug Fixes**
* Prevent to force a date format in the "Create a coupon" action to allow use tags.

= 1.3.8 =

* **New Features**
* New trigger: User renews subscription of a product variation.

= 1.3.7 =

* **Bug Fixes**
* Fixed check on filter "User subscription" when working with "any product" option.
* Fixed status checking for inactive users.

= 1.3.6 =

* **Bug Fixes**
* Fixed check on filters "User has purchased" and "User has not purchased" when working with "any product" option.

= 1.3.5 =

* **New Features**
* New trigger: User lifetime value is equal, less or greater than a value.
* New filter: User has purchased a product.
* New filter: User has not purchased a product.
* New filter: User subscription for a product is active.
* New filter: User subscription for a product is inactive.
* New filter: User lifetime value is equal, less or greater than a value.

= 1.3.4 =

* **New Features**
* New tag: Order Currency.
* New tag: Order Currency Symbol.

= 1.3.3 =

* **Bug Fixes**
* Fixed duplicate key for "User removes a product variation from cart" trigger.

= 1.3.2 =

* **New Features**
* New trigger: User removes a product from cart.
* New trigger: User removes a product variation from cart.

= 1.3.1 =

* **Improvements**
* Added a link to the order on logs entries where an order is assigned.

= 1.3.0 =

* **Improvements**
* Improved "User is added to a membership" trigger detection to make it more accurate.

= 1.2.9 =

* **Improvements**
* Added support to detect manual memberships assignations.

= 1.2.8 =

* **New Features**
* Added the order customer note tag.

= 1.2.7 =

* **Improvements**
* Improved "User is added to a membership" trigger detection.

= 1.2.6 =

* **New Features**
* New trigger: User's subscription payment retry status changes.
* New action: Retry user's subscription payment.

= 1.2.5 =

* **New Features**
* Added support for tags in the "Amount" option from the "Create a coupon" action.

= 1.2.4 =

* **New Features**
* New Trigger: User's order with a product of a category changes its status to a status.
* New Trigger: User's order with a product of a tag changes its status to a status.
* New Anonymous Trigger: Guest's order with a product of a category changes its status to a status.
* New Anonymous Trigger: Guest's order with a product of a tag changes its status to a status.

= 1.2.3 =

* **Bug Fixes**
* Fixed subscription variation selector.

= 1.2.2 =

* **New Features**
* New Trigger: User purchases any/specific product variation.
* New Anonymous Trigger: Guest purchases any/specific product variation.
* New Trigger: User purchases a subscription variation of any/specific product.
* New Trigger: User completes a purchase with any/specific payment method.
* New Anonymous Trigger: Guest completes a purchase with any/specific payment method.
* New Trigger: User adds any/specific product to cart.
* New Trigger: User adds any/specific product variation to cart.

= 1.2.1 =

* **Improvements**
* Improved checks to detect order status changes.

= 1.2.0 =

* **New Features**
* New Anonymous Trigger: Guest's order with any/specific product changes its status to specific status.

= 1.1.9 =

* **New Features**
* New Trigger: User's order with any/specific product changes its status to specific status.

= 1.1.8 =

* **Bug Fixes**
* Fixed issue that prevents to load all actions correctly.

= 1.1.7 =

* **New Features**
* New trigger: User subscription of any/specific product changes to any/specific status.

= 1.1.6 =

* **Improvements**
* Improved checks for User is added to a membership trigger.

= 1.1.5 =

* **Improvements**
* Ensure that cancelled subscription event is triggered only when subscription has the status "cancelled".

= 1.1.4 =

* **New Features**
* Added support to WooCommerce Memberships.
* New trigger: User is added to a membership.
* New trigger: User's access to membership gets cancelled.
* New trigger: User's access to membership expires.
* New action: Add user to membership.
* New action: Remove user from membership.

= 1.1.3 =

* **New Features**
* Added a new tag to get any order meta data.

= 1.1.2 =

* **New Features**
* Added support to the AutomatorWP custom values feature.

= 1.1.1 =

* **New Features**
* New anonymous trigger: Guest purchases any/specific product.
* New anonymous trigger: Guest purchases a product of any/specific category.
* New anonymous trigger: Guest purchases a product of any/specific tag.
* New anonymous trigger: Guest completes a purchase.
* New anonymous trigger: Guest completes a purchase with a total greater than, less than or equal to a specific amount.
* New anonymous trigger: Guest's order status changes to specific status.

= 1.1.0 =

* **New Features**
* New action: Cancel the user's subscription to all/specific product.

= 1.0.9 =

* **New Features**
* New action: Add email(s) to coupon.
* New action: Add user to coupon.
* New action: Create a coupon.

= 1.0.8 =

* **New Features**
* Added support to the AutomatorWP brand new anonymous automations.

= 1.0.7 =

* **New Features**
* Added 22 tags to use customer billing and shipping information on actions.
* Added 33 order tags to use the order information from a trigger on actions (like subtotal, tax, ip address, discount codes, etc).

= 1.0.6 =

* **Improvements**
* Improved the way to detect an order gets completed to run the required triggers.

= 1.0.5 =

* **Bug Fixes**
* Fixed trigger "User purchases subscription of any/specific product" visibility.

= 1.0.4 =

* **New Features**
* New trigger: User completes a purchase.
* New trigger: User's order status changes to specific status.
* **Improvements**
* Improved the way to detect an order gets completed to run the required triggers.

= 1.0.3 =

* **Improvements**
* Added more checks on subscription expired trigger to only handle subscriptions correctly expired.

= 1.0.2 =

* **Bug Fixes**
* Fixed typo on purchase subscription trigger class name.

= 1.0.1 =

* **New Features**
* Added support to WooCommerce Subscriptions.
* New trigger: User purchases subscription of any/specific product.
* New trigger: User renews subscription of any/specific product.
* New trigger: User cancels subscription of any/specific product.
* New trigger: User subscription of any/specific product expires.

= 1.0.0 =

* Initial release.
