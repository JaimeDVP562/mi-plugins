=== GamiPress - FluentCart Points Gateway ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, gamify, point, achievement, badge, award, reward, credit, engagement, fluentcart, ecommerce
Requires at least: 5.6
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Use GamiPress points types as a payment gateway for FluentCart.

== Description ==

FluentCart Points Gateway gives you the ability to use GamiPress registered points types as a payment gateway in FluentCart.

In just a few minutes, your users will be able to complete any purchase by expending an amount of points.

Just define the conversion rate and this add-on will make the conversions automatically on each purchase.

Note: This add-on is designed to register points types as standard payment gateway (like PayPal or Stripe) so only full payments are supported.

This add-on is a FluentCart adaptation of the original [WooCommerce Points Gateway](https://gamipress.com/add-ons/gamipress-wc-points-gateway/) add-on.

= Features =

* Enable any points type as a FluentCart payment gateway.
* Set different conversion rates for each points type.
* Points will be awarded to the product's vendor.
* Ability to restore user points on refund.
* Points total will be displayed at checkout and on order details.
* Compatible with FluentCart's modern checkout flow.
* REST API endpoint for programmatic access to points checkout data.
* Overrideable templates for checkout display customization.

= Requirements =

* WordPress 5.6+
* PHP 7.4+
* [GamiPress](https://wordpress.org/plugins/gamipress/) 3.0.0+
* [FluentCart](https://wordpress.org/plugins/fluent-cart/)

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

No, this add-on was designed to register points types as standard payment gateways so it only supports full payments.

= How do I configure the conversion rate? =

Navigate to FluentCart > Settings > Payment Methods and find the GamiPress points gateway section. There you can set the exchange conversion rate for each points type.

= Does this add-on support refunds? =

Yes, when an order paid with points is refunded through FluentCart, the corresponding amount of points will be restored to the customer.

= Can I customize the checkout display? =

Yes, you can override the template by copying `templates/fc-points-checkout.php` to your theme's `gamipress/fluentcart-points-gateway/` directory.

== Changelog ==

= 1.0.0 =

* Initial release.
* FluentCart payment gateway integration for all GamiPress points types.
* Configurable exchange conversion rates per points type.
* Vendor points awarding on product sales.
* Refund support with automatic points restoration.
* Checkout points balance display.
* REST API for points checkout data.
* Overrideable templates.
