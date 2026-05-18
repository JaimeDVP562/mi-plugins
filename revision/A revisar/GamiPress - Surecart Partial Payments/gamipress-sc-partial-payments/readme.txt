=== GamiPress - SureCart Partial Payments ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, gamify, point, achievement, badge, award, reward, credit, engagement, surecart
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 1.0.0
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Let users partially pay a SureCart purchase by using points.

== Description ==

SureCart Partial Payments gives you the ability to enable GamiPress points types for partially pay any purchase made through SureCart.

In just a few minutes, your users will be able to reduce any purchase total (like a discount) by using an amount of points at checkout.

In addition, this add-on includes options to set different conversions per points type, limit the maximum discount per purchase or customize the input to enter the points amount.

= Features =

* Enable any points type for partial payments.
* Ability to reduce any purchase total by multiples points types.
* Set different conversion rates per each points type.
* Ability to limit the maximum amount allowed per points type.
* Force users to use a fixed amount of points or let them introduce the amount they wish.
* Different inputs to let user choose exactly the amount of points they want to use.
* Settings to limit the maximum discount allowed on a single purchase (flat or percentage limit).
* Live controls to easily view the discount amount based on the points to use.
* Ability to restore user points on refund (via SureCart purchase revocation).
* Seamless integration with SureCart checkout forms (block-based and shortcode).

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

= Does this add-on register points types as payment gateways? =

No, this add-on was designed to register points types to allow users reduce a purchase total by using an amount of points at checkout.
This gives users a discount on their SureCart purchase using their GamiPress points.

= How does this work with SureCart's checkout? =

The add-on injects a partial payments form into SureCart checkout pages using WordPress's render_block filter and content filters.
Users can select a points type, enter the desired amount, and apply a discount before completing their SureCart purchase.

= Does it support refunds? =

Yes, when a SureCart purchase is revoked, the points used for the partial payment will be automatically refunded back to the user.

== Changelog ==

= 1.0.0 =

* Initial release.
* Based on GamiPress - WooCommerce Partial Payments, adapted for SureCart.
