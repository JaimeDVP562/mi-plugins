# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2024

### Added
- **Create Contact Action**: Automatically create new contacts in Keap from WordPress events
  - Supports first name, last name, email, phone, and company fields
  - Uses caching for improved performance
  - Merge field support for dynamic data
  
- **Update Contact Action**: Modify existing contact information
  - Find contacts by email
  - Update specific fields (leave blank to skip)
  - Comprehensive logging

- **Add Contact Tag Action**: Apply tags to organize and segment contacts
  - Find contacts by email
  - Add single or multiple tags
  - Tag management functionality

- **Add to Campaign Action**: Enroll contacts in Keap campaigns and sequences
  - Find contacts by email
  - Add to specific campaigns
  - Supports all Keap campaign types

- **Contact Added Trigger**: Fire automations when new contacts are added
  - Trigger workflows based on contact creation
  - Filter by specific emails
  - Extensible for webhook integration

- **Logger System** (`includes/logger.php`)
  - Comprehensive error logging with severity levels
  - Debug mode support
  - Log file management
  - Context-aware logging

- **Cache System** (`includes/cache.php`)
  - Smart transient-based caching
  - Configurable expiration (default: 12 hours)
  - Cache clearing utilities
  - WP-CLI integration ready

- **Extended API Functions** (`includes/api-functions.php`)
  - `automatorwp_keap_create_contact()` - Create contacts
  - `automatorwp_keap_update_contact()` - Update contacts
  - `automatorwp_keap_add_contact_tag()` - Add tags
  - `automatorwp_keap_remove_contact_tag()` - Remove tags
  - `automatorwp_keap_get_contact_by_email()` - Fetch by email
  - `automatorwp_keap_add_contact_to_campaign()` - Campaign enrollment
  - `automatorwp_keap_send_email()` - Send emails
  - `automatorwp_keap_get_campaigns()` - List campaigns
  - `automatorwp_keap_get_tags()` - List tags

- **Improved Admin Interface**
  - New callback functions for form fields
  - Better error handling in admin
  - Ajax improvements

- **Documentation**
  - Comprehensive DOCUMENTATION.md with setup guide
  - CONFIGURATION.md with advanced setup options
  - Inline code documentation
  - Setup examples

- **Version Bump**: Updated to 1.1.0

### Improved
- **Error Handling**: Replaced direct error_log with `automatorwp_keap_handle_api_response()`
- **API Calls**: Reduced API calls through intelligent caching
- **Code Organization**: Better file structure with dedicated logger, cache, and API files
- **Security**: Added nonce verification and input sanitization
- **Performance**: Transient-based caching for API responses
- **Logging**: Structured logging with context and severity levels
- **Documentation**: All functions properly documented with JSDoc comments

### Fixed
- Cleaned up legacy Trello references in function comments
- Fixed error log path issues
- Improved JSON response handling
- Better API timeout configuration (30 seconds)
- Fixed missing function calls in old code

### Changed
- Updated readme.txt with accurate Keap information (was showing Trello)
- Reorganized file includes in main plugin file
- Updated version to 1.1.0 in all locations
- Transitioned from hardcoded URLs to API wrapper functions

### Removed
- Old Trello-specific code and functions
- Problematic error_log() direct calls
- Unused board/list/card retrieval functions
- Legacy option callback functions

## [1.0.0] - Initial Release

### Added
- Create Mail action - Create emails in Keap
- Send Message action - Send messages to contacts
- OAuth authentication with Keap
- Basic API integration
- AutomatorWP integration framework
- Admin settings interface
- Language support (pot file)

## Upgrade Guide

### From 1.0.0 to 1.1.0

**No breaking changes!** Simply update the plugin and:

1. New actions are automatically available
2. Existing automations continue to work
3. Enable debug logging if needed: `define( 'AUTOMATORWP_KEAP_DEBUG', true );`
4. Customize cache duration with filter: `automatorwp_keap_cache_expiration`

## Future Roadmap

- [ ] Webhook support for real-time triggers
- [ ] More advanced filtering options
- [ ] Bulk import/export functionality
- [ ] Custom field mapping
- [ ] Two-way sync with Keap
- [ ] Automation cloning and templates
- [ ] REST API endpoints for extensibility

## Contributing

Issues and pull requests are welcome. Please ensure:
- Code follows WordPress coding standards
- All functions are documented
- Debug logging is implemented where appropriate
- Tests are included for new features

## Support

For issues or feature requests:
1. Check DOCUMENTATION.md
2. Enable debug logging
3. Contact AutomatorWP support
