=== BBForms - Flexible Contact Forms, Survey, Quiz, Poll & Custom Forms Editor ===
Contributors: rubengc, eneribs, dioni00, tinocalvo, flabernardez
Tags: contact form, custom form, forms, shortcode, code
Requires at least: 4.4
Tested up to: 6.9
Stable tag: 1.0.9
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Build your [forms] faster and easily just by typing them!

== Description ==

[BBForms](https://bbforms.com "BBForms") is a code form plugin designed to help you build multipurpose forms in seconds!

Build your forms in a few lines, configure their actions & change their options using the BBCodes syntax (the same as WordPress Shortcodes syntax).

https://youtu.be/cfD8wqzgchs

= How does it works? =

1. Code the form fields:
```
[email* name="your_email" label="Email" desc="Enter your email."]
[submit value="Send"]
```
2. Code the form actions:
```
[record]
[email to="{field.your_email}" subject="Submission received!"]
Here is the data entered: {fields_table}
[/email]
[redirect to="https://wp.org/"]Redirecting...[/redirect]
```
3. Change the form options:
```
require_login=yes
require_login_message=You must be logged in.
unique_field=your_email
unique_field_message=A submission with {field.your_email} already exists.
submissions_limit=10
submissions_limit_message=Sorry, only the first 10 submissions can join the giveaway :)
```
4. Place the shortcode to show your form anywhere:
```
[bbforms id="123"]
```

= Features =

Forms:

* Form editor designed to build faster.
* [20 fields](https://bbforms.com/docs/features/fields/).
* [20 BBCodes](https://bbforms.com/docs/features/bbcodes/) (columns, table, bold, etc).
* Responsive multi-column form layout.
* File uploads.
* Quiz field to act as custom captcha.
* Form templates ([live demo](https://bbforms.com/docs/features/form-templates/)).
* Export/Import forms by copying & pasting code or TXT file.
* Live form preview.
* Categories & tags to organize your forms.

Actions:

* [6 actions](https://bbforms.com/docs/features/actions/) (record, email, redirect, etc).
* Unlimited form actions (send as many email notifications as you wish!).
* GDPR actions to automate export & deletion requests.
* [Tags](https://bbforms.com/docs/features/tags/) to place dynamic content anywhere.

Form options:

* [14 form options](https://bbforms.com/docs/features/form-options/) (clear & hide form on success, show required fields notice, etc).
* Restrict form access to logged in users only.
* Limit by a unique field value.
* Apply a submissions limit.
* Anti-bot & anti-spam protection.

Submissions:

* Advanced submissions view.
* Store, view, filter, edit & delete your submissions.
* Export submissions to CSV.
* Submissions auto-cleanup.

Extra:

* Role management settings.
* Override form & field messages globally.
* Light & dark mode editor.
* Mobile ready & designed for accessibility.
* Optimized for speed (CSS: 4kb & JS:9kb).

= The perfect form solution for everyone =

BBForms is designed to be accessible to everyone helping you to create your forms easily, whether you build them for yourself or for a client:

**Site owners**

Build your own forms without coding experience! BBForms is shipped with several form templates as sample forms and our form editor includes controls to help you code your form.

Each control includes a list of examples of common configurations like an email field auto-filled with the logged in user email or a URL field that only accepts https URLs.

**Developers**

Building forms for your customers has never been so easy! You can store your own form templates and import them in your customer website in no time.

All documentation can be found inside the form editor, so you can access them without abandon the form editor screen.

**Sites network owners**

Do you manage a large amount of sites? BBForms makes super easy to bring support! Your customers can send you an entire form configuration by copy & pasting their code or as a TXT file.

Also, you are able to provide them the code required for any configuration or provide TXT files in your site to let them import a form designed by yourself.

= Powerful add-ons to extend BBForms =

* [Address Autocomplete](https://bbforms.com/add-ons/address-autocomplete/)
* [Calculations](https://bbforms.com/add-ons/calculations/)
* [Conditional Display](https://bbforms.com/add-ons/conditional-display/)
* [Conditional Logic](https://bbforms.com/add-ons/conditional-logic/)
* [Email Balancer](https://bbforms.com/add-ons/email-balancer/)
* [Geolocation](https://bbforms.com/add-ons/geolocation/)
* [Multi-Step Forms](https://bbforms.com/add-ons/multi-steps-forms/)
* [Post Submissions](https://bbforms.com/add-ons/posts/)
* [Rating Field](https://bbforms.com/add-ons/rating/)
* [Repeatable Fields](https://bbforms.com/add-ons/repeatable-fields/)
* [Save Progress](https://bbforms.com/add-ons/save-progress/)
* [Signature Field](https://bbforms.com/add-ons/signature/)
* [Users Management](https://bbforms.com/add-ons/users/)

Spam protection add-ons:

* [Google reCAPTCHA v2 & v3](https://bbforms.com/add-ons/recaptcha/)
* [hCaptcha](https://bbforms.com/add-ons/hcaptcha/)
* [Cloudflare Turnstile](https://bbforms.com/add-ons/cloudflare-turnstile/)

= Integrations =

**WooCommerce**

* Tags:
    * **{woocommerce.is_customer}** - Shows "yes" or "no" based if user or an email address is a registered customer.
    * **{woocommerce.order_history_table}** - Shows an HTML table with the customer order history. Useful for email resumes.
    * **{woocommerce.admin_order_history_table}** - Shows an HTML table with the customer order history with direct links to the admin area. Useful to have direct links to the customer information & orders.

**Easy Digital Downloads**

* Tags:
    * **{edd.is_customer}** - Shows "yes" or "no" based if user or an email address is a registered customer.
    * **{edd.order_history_table}** - Shows an HTML table with the customer order history. Useful for email resumes.
    * **{edd.admin_order_history_table}** - Shows an HTML table with the customer order history with direct links to the admin area. Useful to have direct links to the customer information & orders.

**GamiPress**

In [GamiPress](https://wordpress.org/plugins/gamipress/) plugin you will find new events to let you configure rewards based on BBForms interactions.

* Events:
    * Submit a form.
    * Submit a form of a category.
    * Submit a form of a tag.
    * Submit a field value.

**AutomatorWP**

In [AutomatorWP](https://wordpress.org/plugins/automatorwp/) plugin you will find new triggers and tags to let you configure automations based on BBForms interactions.

* Triggers:
    * Submit a form.
    * Submit a form of a category.
    * Submit a form of a tag.
    * Submit a field value.

* Tags:
    * Tags to use any field submitted value on AutomatorWP actions.

= More plugins from the BBForms team =

If you like BBForms, you will love our other plugins!

* [GamiPress](https://wordpress.org/plugins/gamipress/) - Flexible gamification plugin to reward your users with points, achievements, badges & ranks based on their activity in your WordPress.
* [AutomatorWP](https://wordpress.org/plugins/automatorwp/) - Powerful no-code automator plugin that lets you connect +200 plugins together or with apps, platforms with webhooks plus other WordPress sites.
* [ShortLinks Pro](https://wordpress.org/plugins/shortlinkspro/) - Complete link management plugin that not only powers WordPress websites with shortened URLs, also empowers site owners to create clean, branded and unique affiliate links easily.

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

== Screenshots ==

1. Create forms with an easy-to-use editor.
2. Responsive multi-column form layout.
3. Advanced submissions view with the ability to export them as CSV.
4. Easily edit any submission entry.
5. Reusable form templates.
6. Export/Import forms by copying & pasting code or TXT file.
7. Configure the actions to process anything you want (record, email, redirect, etc).
8. Change the form options easily including require to log in, unique field & submissions limit.
9. Form editor comes with helper controls to build forms faster.
10. Light & dark mode included.
11. Check the documentation inside the plugin, without abandon your dashboard.


== Frequently Asked Questions ==

= Why code forms instead of a visual builder? =

Coding your forms brings greater flexibility, control, and scalability compared to relying on a visual builder.

While visual builders may seem easier at first, they quickly reveal their limitations, becoming less flexible and slower to use.

Defining your forms in text gives you complete control over their content. You can insert custom elements wherever you need, copy and paste only the parts you want, and easily share your form code with others.

By contrast, visual builders force you to work within a graphical interface and restrict you to the actions they support. As a result, even a simple task like duplicating one or multiple fields depends on whether the editor includes a specific control for it.

In short, a text-based editor offers a level of freedom and efficiency that visual builders simply cannot match.

= Will BBForms slow down my website? =

Absolutely not. BBForms was built carefully with performance in mind to make BBForms run smoothly & fast with just 4kb CSS & 9kb JS.

In addition, BBForms only loads styles & scripts in the pages where you place a form.

= What types of forms can I build with BBForms? =

BBForms is designed to build multipurpose forms, which means that you can build almost any kind of form you need. Here are some types of WordPress forms you can create:

* Contact Forms
* Job Application Forms
* File Upload Forms
* Quiz Forms
* Lead Forms
* Feedback Forms
* Polling & Survey Forms
* Appointment Forms
* Booking Forms
* Event Registration Forms
* RSVP Forms
* Newsletter Signup Forms
* CRM Forms
* Email Forms
* Support Ticket Forms
* Request Forms
* Sales Forms
* Questionnaire Forms
* Export or Delete Data Request Forms

With our premium add-ons you can build even more advanced forms:

* Calculation Forms
* Multi-Step Forms
* Signature Contract Forms
* Conditional Forms
* Repeatable Fields Forms
* Address Autocomplete Forms
* User Login Forms
* User Registration Forms
* User Account Management Forms
* Content Contribution Forms
* Post Creation Forms
* Payment Calculator Forms
* Quote & Cost Calculator Forms
* Health & Fitness Calculator Forms

... and many more!

= Which form fields are included in BBForms? =

* [text] - Text
* [textarea] - Text Area
* [email] -Email
* [tel] - Phone
* [url] - URL
* [password] - Password
* [date] - Date
* [time] - Time
* [file] - File
* [number] - Number
* [range] - Range
* [check] - Checkbox
* [radio] - Radio
* [select] - Select
* [country] - Country
* [quiz] - Quiz
* [hidden] - Hidden
* [honeypot] - Honeypot
* [submit] - Submit
* [reset] - Reset

Documentation about fields can be found [here](https://bbforms.com/docs/features/fields/).

= Which actions are included in BBForms? =

* [record] - Records the submission in the database.
* [email] - Sends an email.
* [redirect] - Redirects user to the URL of your choice.
* [message] - Shows a message in the form.
* [export_request] - Registers an email in the WP personal data export tool.
* [delete_request] - Registers an email in the WP personal data deletion tool.

Documentation about actions can be found [here](https://bbforms.com/docs/features/actions/).

= Can I connect BBForms with other plugins and platforms? =

Yes. We are the authors of [AutomatorWP](https://wordpress.org/plugins/automatorwp/), an automation plugin that can connect your forms with +200 plugins & platforms like OpenAI, HubSpot, Learndash & WooCommerce.

In addition, you can create custom workflows like:

Enroll in a LifterLMS course after submit a form

* Update a Google Sheets with the submitted data
* Register as contact in WPFusion after submit a form
* Send a webhook to Zapier with the submitted data
* Register as affiliate in AffiliateWP after submit a form

The possibilities are unlimited!

= Is BBForms compatible with any theme? =

We have tested BBForms with the most popular WordPress themes and since it uses HTML5 inputs, almost all themes should display them correctly.

If your theme does not display a field correctly, just [let us know](https://bbforms.com/contact-us/) to fix it as soon as possible.

= Is BBForms translation ready? =

Yes, BBForms is stored in the official WordPress plugins repository where you (and anyone) are able to [submit your own translations](https://translate.wordpress.org/projects/wp-plugins/bbforms).

= Is BBForms GDPR compliant? =

Yes. All user submitted data is stored locally on your server only, unless you expressly configure BBForms to send it elsewhere, for example via an email action.

We never see or collect any user submitted data and we do not act as Data Controllers or Data Processors per GDPR Article 4.

If you collect Personally Identifiable Information (PII) using BBForms, here is a guide to [make your forms GDPR compliant](https://bbforms.com/docs/miscellaneous/gdpr-make-your-forms-gdpr-compliant/) and also you can [automate export and deletion data requests](https://bbforms.com/docs/miscellaneous/gdpr-automate-export-deletion-requests/).

= Does BBForms include spam protection? =

Yes, BBForms includes anti-bot & anti-spam protection through honeypot which is enabled on all forms by default in the form options.

Additionally, there are [premium integrations](https://bbforms.com/add-ons/) with Google reCAPTCHA (v2 & v3), hCaptcha, & Cloudflare Turnstile.

Lastly, you can add a custom Captcha using the Quiz field to create math or question-based captcha for your forms.

== Changelog ==

= 1.0.9 =

* **Improvements**
* Fixed PHP Deprecated: strip_tags() notice for newer versions of PHP.

= 1.0.8 =

* **Improvements**
* Tooltip library updated.

= 1.0.7 =

* **Developer Notes**
* Tested with WordPress 6.9.

= 1.0.6 =

* **Improvements**
* Tooltip library updated.

= 1.0.5 =

* **Improvements**
* Improved video upload handler.

= 1.0.4 =

* **Improvements**
* Improved field descriptions.

= 1.0.3 =

* **Improvements**
* Prevent hidden and honeypot fields to create extra margin.

= 1.0.2 =

* **Improvements**
* Improved columns display on mobiles when column has a custom width.

= 1.0.1 =

* **Improvements**
* Improve tags display.

= 1.0.0 =

* Initial Release.
