# AutomatorWP – Cloudflare Integration

Connect AutomatorWP with Cloudflare to automate cache purge operations as part
of any automation workflow.

---

## Requirements

- WordPress 5.6+
- PHP 7.4+
- AutomatorWP (free or Pro)
- A Cloudflare account with at least one active zone

---

## Installation

1. Upload the `automatorwp-cloudflare/` folder to `/wp-content/plugins/`.
2. Activate the plugin via **Plugins → Installed Plugins**.
3. Go to **AutomatorWP → Settings → Cloudflare**.
4. Enter your **API Token** and **Zone ID** (see below).
5. Save changes.

### Getting your credentials

**API Token** (recommended over Global API Key)
- Visit https://dash.cloudflare.com/profile/api-tokens
- Click **Create Token**
- Use the **Cache Purge** template (or create a custom token with permission:
  `Zone → Cache Purge → Purge`)
- Scope it to the specific zone (domain) you want to manage

**Zone ID**
- Open your domain in the Cloudflare dashboard
- Scroll down on the **Overview** page → right sidebar
- Copy the **Zone ID** value (32-character hex string)

---

## Available Actions

| Action | Description |
|---|---|
| **Purge all Cloudflare cache** | Wipes every cached asset in the zone |
| **Purge cache for a specific URL** | Targeted purge by full URL (supports tags) |
| **Purge cache for a specific post** | Resolves the permalink automatically from a Post ID |

---

## Example Automation Flows

### Flow 1 — Purge all cache after publishing a post

```
Trigger:  WordPress → User publishes a post
Action:   Cloudflare → Purge all Cloudflare cache
```

Use when your entire site depends on the updated content (menus, sidebars, etc.).

---

### Flow 2 — Purge only the updated post URL

```
Trigger:  WordPress → User updates a post
Action:   Cloudflare → Purge Cloudflare cache for post ID {post:ID}
```

The tag `{post:ID}` is automatically replaced with the ID of the post being
updated. The plugin resolves the permalink and sends a targeted purge —
no other cache is touched.

---

### Flow 3 — Purge a specific page on user registration

```
Trigger:  WordPress → User registers
Action:   Cloudflare → Purge cache for URL https://example.com/members/
```

Ideal for membership sites where the members directory should always reflect
the latest registrations.

---

### Flow 4 — Purge WooCommerce product after order

```
Trigger:  WooCommerce → User completes a purchase of a product
Action:   Cloudflare → Purge Cloudflare cache for post ID {product:ID}
```

Keeps product pages (stock count, review counts) fresh immediately after
a purchase without clearing unrelated cache.

---

## Security Notes

- The API Token is stored in the WordPress options table (same as AutomatorWP
  settings). For extra security, define it as a constant in `wp-config.php`:

  ```php
  define( 'AUTOMATORWP_CLOUDFLARE_API_TOKEN', 'your-token-here' );
  define( 'AUTOMATORWP_CLOUDFLARE_ZONE_ID',   'your-zone-id-here' );
  ```

  Constants take priority over the settings panel values.

- The token is **never exposed to the frontend** or to any AJAX endpoint.
- All API calls are made server-side via `wp_remote_request()`.

---

## File Structure

```
automatorwp-cloudflare/
├── automatorwp-cloudflare.php       ← Main plugin file (singleton bootstrap)
├── assets/
│   └── cloudflare.svg               ← Integration icon (add manually)
└── includes/
    ├── admin.php                    ← Settings fields (API Token, Zone ID)
    ├── functions.php                ← API request handler + cache helpers
    └── actions/
        ├── purge-all.php            ← Action: Purge everything
        ├── purge-url.php            ← Action: Purge specific URL
        └── purge-post.php           ← Action: Purge by Post ID
```
