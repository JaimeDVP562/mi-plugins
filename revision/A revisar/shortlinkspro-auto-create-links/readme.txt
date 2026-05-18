# ShortLinks Pro - Auto-Create Links
 
Add-on for ShortLinks Pro that automatically generates short links for posts, pages, and custom post types the second you hit "Publish".


* **Set it and forget it:** The moment your draft becomes a published post, your short link is instantly created in the background.
* **Tailored to your content:** Want different link prefixes for your blog posts and your e-commerce products? No problem! You can set unique defaults (like prefix, category, tags, and redirect types) for each Post Type.
* **Time-saving bulk actions:** Have hundreds of old posts without short links? Our one-click bulk tools let you generate, update, or clean up links for your entire archive in seconds.
* **Everything right where you write:** We’ve added a handy little box right inside your WordPress post editor. You can tweak the short link slug or category for that specific post before publishing, without ever leaving the page.
* **A two-way street:** When you're managing your links in the ShortLinks Pro dashboard, you'll easily see exactly which post each auto-generated link belongs to.

## How to install

1. Make sure you already have the main **ShortLinks Pro** plugin installed and active.
2. Upload the add-on folder to your `/wp-content/plugins/` directory, or just upload the `.zip` file right from your WordPress dashboard (*Plugins > Add New*).
3. Hit **Activate** and you're ready to roll!

## Use

### 1. Set up your global rules
Head over to **ShortLinks Pro > Settings** and click on the new **Auto-Create Links** tab.
* Pick the Post Types you want to automate.
* Choose your default prefix, category, tags, and redirect types for each one.
* Click the "Create Links" button here if you want to generate short links for all your older published posts!
* Open up any post or page to edit it.
* Look for the **SLP - Auto-Create Links** box on the right sidebar. 
* It will be pre-filled with your default settings, but feel free to change the slug or category just for this specific post.
* Hit **Publish**, and your new link is ready to be shared with the world!


It hooks directly into ShortLinks Pro's custom database tables to keep things running smoothly, no matter how massive your site gets. 

* **No duplicates:** We use native WordPress `post_meta` to securely link your posts and short links together, ensuring you never accidentally create duplicate redirects.
* **Safe processing:** All our background bulk actions are AJAX-powered, batched, and protected by nonces and capability checks.

---
**Requires:** ShortLinks Pro  
**Tested up to:** WordPress 6.x