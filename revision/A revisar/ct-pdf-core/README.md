# CT PDF Core

Custom WordPress plugin for generating configurable PDF templates using mPDF.

This MVP focuses on building a solid and extensible core for certificate-style PDF generation directly from the WordPress admin panel.

---

##  Objective

The goal of this plugin is to allow administrators to:

* Create editable PDF templates from the WordPress dashboard.
* Configure layout settings (margins, fonts, page size, orientation).
* Apply visual presets.
* Add background and border images.
* Generate dynamic previews using mPDF.
* Replace dynamic tags inside template content.

The current version establishes a stable technical foundation for future expansion.

---

## 🏗 Architecture Overview

### 1. Custom Post Type

`ct_pdf_template`

Used to create and manage PDF templates.

Supports:

* Title
* Content editor (HTML)
* Revisions

Templates are configured via a custom metabox on the right sidebar.

---

### 2. Settings Metabox

Each template includes configurable options:

* Preset selector (Modern, Institution, Certificate Fancy)
* Page size (A4, A5, LETTER)
* Orientation (Portrait / Landscape)
* Custom margins (mm)
* Font selection
* Background color
* Background image URL
* Border color
* Border image URL

All settings are stored using `update_post_meta()` and dynamically injected into the PDF rendering process.

---

## 🖼 Image Handling (Localhost-Safe)

One of the key technical challenges was ensuring background and border images render correctly in a local development environment.

mPDF does not reliably load `http://localhost/...` image URLs.

To solve this, I implemented:

```php
ct_pdf_url_to_file_uri()
```

This function:

1. Detects if the image belongs to `/wp-content/uploads`
2. Converts the public URL into an absolute file path
3. Returns a `file:///` URI when the file exists

This ensures consistent image rendering in both local and production environments.

---

## 📄 PDF Rendering Flow

Preview endpoint:

```
admin-post.php?action=ct_pdf_preview&template_id={ID}
```

Rendering steps:

1. Retrieve template post.
2. Load all meta configuration values.
3. Replace dynamic tags:

   * `{date}`
   * `{user.display_name}`
4. Generate dynamic CSS based on preset and margins.
5. Apply background via `@page` rule.
6. Inject border overlay as fixed-position layer.
7. Render with mPDF.
8. Output inline preview.

mPDF configuration includes:

* Custom margin support
* Page format handling
* Orientation control
* Dedicated temporary directory
* Image error debugging enabled

---

## 🎨 Presets

### Modern

Clean layout with strong first line emphasis.

### Institution

More formal spacing and heading structure.

### Certificate Fancy

Designed specifically for certificate layouts:

* Large uppercase title
* Secondary subtitle styling
* Prominent name formatting
* Centered alignment
* Balanced internal padding

The preset system is modular and can be extended easily.

---

## 🧩 Dynamic Tags

Supported tags inside the editor content:

* `{date}` → Current date (Y-m-d format)
* `{user.display_name}` → Logged-in user display name

Tags are replaced before the PDF is rendered.

---

## 🛠 Technical Notes

* Built for WordPress + mPDF.
* Designed with extensibility in mind.
* Avoids external dependencies beyond mPDF.
* Layout layering handled using z-index to prevent content overlap issues.
* Background images applied via `@page` for mPDF compatibility.
* Border overlay handled separately to avoid rendering conflicts.

---

## ✅ Current Status

* PDF template system operational.
* Presets functional.
* Background and border image rendering stable.
* Localhost compatibility resolved.
* Preview endpoint reliable.
* Layout rendering consistent.

This version establishes a strong MVP base ready for iteration and feature expansion.

---

## 🚀 Next Steps

Potential future improvements:

* Signature block system
* Logo positioning controls
* Multi-page support
* Advanced tag engine
* Template export/import
* Automation hooks
* Frontend generation support

---

Developed as part of internal plugin architecture evolution.
MVP completed and ready for review.
