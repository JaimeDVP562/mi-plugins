GAMIPRESS - CT PDF INTEGRATION

Custom integration between GamiPress and CT_PDF to generate and download dynamic PDFs for certificates (achievements) and purchases.

It allows reusable, optimized PDF documents accessible from the frontend for each user.

--------------------------------------------------
MAIN FEATURES
--------------------------------------------------

- Dynamic PDF generation using CT_PDF
- Full integration with GamiPress
- Support for:
  - Certificates (achievements)
  - Purchases (earnings)
- Frontend download button
- Configurable template system
- Custom dynamic tags support
- Cache system using $override
- Prevents unnecessary regeneration
- Support for multiple certificates


--------------------------------------------------
 CERTIFICATES
--------------------------------------------------

1. User earns a Certificate achievement
2. The system checks the assigned template
3. A download button appears in "My Achievements"
4. The download endpoint is triggered
5. The PDF is generated or reused
6. The PDF is served in the browser

--------------------------------------------------
 PURCHASES
--------------------------------------------------

1. User completes a purchase
2. Payment data is retrieved
3. The global template is applied
4. Dynamic PDF data is generated
5. The file is stored on the server
6. The existing file is reused if available

--------------------------------------------------
DOWNLOAD ENDPOINT (CERTIFICATES)
--------------------------------------------------

?ctpdf_download_certificate=1&achievement_id=ID

- Validates logged-in user
- Retrieves the related achievement
- Generates or reuses the PDF
- Returns the file in the browser

--------------------------------------------------
PDF GENERATION
--------------------------------------------------

Certificates:
ctpdf_certificates_get_or_generate_pdf()

Purchases:
ctpdf_purchases_get_or_generate_pdf()

Both functions:
- Generate PDFs from templates
- Inject dynamic data
- Store files on server
- Use $override = false for caching

--------------------------------------------------
TEMPLATE SYSTEM
--------------------------------------------------

Certificates:
- Multiple templates supported
- One template per achievement
- Configured through a metabox
- "Do not issue certificates" option available

Purchases:
- Single global template
- Configurable through settings
- Resettable

--------------------------------------------------
CACHE
--------------------------------------------------

$override = false

- Reuses existing PDFs
- Prevents unnecessary regeneration
- Improves performance
- Avoids duplicates

--------------------------------------------------
PDF DATA AND TAGS
--------------------------------------------------

Certificates:
- {user.display_name}
- {achievement.title}
- {date}
- {certificate.issue_date}

Purchases:
- User data
- Items list
- Company data
- Dynamic template with custom tags

--------------------------------------------------
ARCHITECTURE
--------------------------------------------------

helpers.php -> URLs, buttons, directories, reusable helpers
hooks.php -> endpoint, frontend integration, dynamic buttons, My Achievements integration
pdf-functions.php -> PDF generation, template parsing, mPDF integration

--------------------------------------------------
DOWNLOAD BUTTON
--------------------------------------------------

- Appears in "My Achievements"
- Only displayed if:
  - the user has earned the achievement
  - the certificate has an assigned template
- Works with multiple certificates
- Fully dynamic (no hardcoded logic)

--------------------------------------------------
VALIDATION
--------------------------------------------------

- CT_PDF works in both Certificates and Purchases
- No template conflicts between modules
- $override works correctly
- Optimized generation
- Custom tags working
- Generalized solution (not tied to a single certificate)
- Tested with multiple certificates
- Tested with different templates
- Frontend download working
- Real PDF generated with mPDF
- Purchases working with a global template
- Certificates working with per-achievement templates

--------------------------------------------------
STATUS
--------------------------------------------------

- Implemented
- Tested
- Reusable
- Scalable
- Ready for review

--------------------------------------------------
NOTES
--------------------------------------------------

- vendor/ (mPDF) is included
- For production environments:
  composer install

--------------------------------------------------
AUTHOR
--------------------------------------------------

Custom integration for GamiPress + CT_PDF