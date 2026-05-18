=== GamiPress - WooCommerce Points Gateway ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, gamify, point, achievement, badge, award, reward, credit, engagement, ajax
Requires at least: 4.4
Tested up to: 6.4
Stable tag: 1.2.1
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Use GamiPress points types as a payment gateway for WooCommerce.

== Description ==

WooCommerce Points Gateway gives you the ability to use GamiPress registered points type as a payment gateway.

In just a few minutes, your users will be able to complete any purchase by expending an amount of points.

Just define the conversion rate and this add-on will make the conversions automatically on each purchase.

Note: This add-on is designed to register points types as standard payment gateway (like PayPal or Stripe) so only full payments are supported.
For partial payments, you may check our [WooCommerce Partial Payments](https://gamipress.com/add-ons/gamipress-wc-partial-payments/) add-on.

= Features =

* Enable any points type as a payment gateway.
* Set different conversion rates by each points type.
* Points will be awarded to the product's vendor.
* Ability to restore user points on refund.
* Points total will be displayed at checkout and on order details.

In addition you can use the [WooCommerce integration](https://wordpress.org/plugins/gamipress-woocommerce-integration/) to add activity triggers related to WooCommerce actions.

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

= Does this add-on allow partial payments? =

No, this add-on was designed to register points types as standard payment gateway (like PayPal or Stripe) so only supports full payments.
For partial payments, you may check our [WooCommerce Partial Payments](https://gamipress.com/add-ons/gamipress-wc-partial-payments/) add-on.

= Does this add-on allow enable or disable gateways per product? =

No, WooCommerce Points Gateway is designed to register each points type as a new payment gateway.
The feature what you are looking for is known as "Conditional Gateway" and WooCommerce already offers a plugin specially for this purpose that you can check [here](https://woocommerce.com/products/conditional-shipping-and-payments/).

== Changelog ==

= 1.2.1 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.

= 1.2.0 =

* **Bug Fixes**
* Ensure that points are shown when the checkout only contains one point type such as gateway.

= 1.1.9 =

* **Improvements**
* Ensure High Performance Order Storage compatibility.

= 1.1.8 =

* **Bug Fixes**
* Fixed WooCommerce Checkout Blocks to adapt to WooCommerce latest version.

= 1.1.7 =

* **Improvements**
* Check for exchange rates close to 0.
* Round values for user earnings registers.

= 1.1.6 =

* **Improvements**
* Added support for Woocommerce Checkout Blocks.

= 1.1.5 =

* **Improvements**
* Added new user earnings when deducting/awarding points during a points transaction.

= 1.1.4 =

* **Improvements**
* Prevent to award points to vendor if is purchasing its own product.
* **Developer Notes**
* Added filter to decide to award points to the product vendor.

= 1.1.3 =

* **Improvements**
* Added sanitization to all conversion values to ensure valid values.

= 1.1.2 =

* **Improvements**
* Ensure that a payment method is selected at checkout to avoid any Javascript error.

= 1.1.1 =

* **New Features**
* Added support to GamiPress 1.8.0.

= 1.1.0 =

* **Improvements**
* Price to points will be rounded up always to the next highest integer (not decimals) value.
* **Developer Notes**
* Added new filters to add-on more extensible and customizable.

= 1.0.9 =

* **Bug Fixes**
* Fixed an infinite loop that sometimes happens on points balance preview at checkout.

= 1.0.8 =

* **New Features**
* Added support to GamiPress 1.7.0.

= 1.0.7 =

* **Bug Fixes**
* Fixed the points balance preview on checkout when switching back to a non points payment method.

= 1.0.6 =

* **Bug Fixes**
* Fixed the points balance preview on checkout if a points gateway is pre-selected as a payment method.

= 1.0.5 =

* **New Features**
* Each purchased product's vendor gets awarded the proportional points amount.
* **Bug Fixes**
* Fixed the points balance preview on checkout.

= 1.0.4 =

* **Bug Fixes**
* Fixed wrong priority when overwriting a template.
* **Developer Notes**
* Added new filters to the checkout output to make it more customizable.

= 1.0.3 =

* **Developer Notes**
* Added new filters to the checkout output to make it more customizable.

= 1.0.2 =

* **Bug Fixes**
* Fixed checkout error messages when user doesn't meet the required amount.
* Fixed some wrong text domains.

= 1.0.1 =

* **Bug Fixes**
* Fixed wrong gateway on checkout when user is not logged in.

= 1.0.0 =

* Initial release.
