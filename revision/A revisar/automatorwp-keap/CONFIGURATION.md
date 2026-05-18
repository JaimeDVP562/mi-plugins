# Configuration Guide

## Environment Setup

### Enable Debug Logging

Add to your `wp-config.php`:

```php
// Enable AutomatorWP Keap debugging
define( 'AUTOMATORWP_KEAP_DEBUG', true );
```

The plugin will create a log file at: `/wp-content/plugins/automatorwp-keap/logs/keap.log`

### Customize Cache Duration

By your functions.php or custom plugin:

```php
// Set cache to 24 hours (default is 12 hours)
add_filter( 'automatorwp_keap_cache_expiration', function() {
    return 86400; // 24 hours in seconds
});
```

### Clear Cache Programmatically

```php
// Clear specific cache
automatorwp_keap_clear_cache( 'campaigns_list' );

// Clear all Keap caches
automatorwp_keap_clear_cache();
```

## API Configuration

### Getting Your Keap API Credentials

1. **Log in to Keap**
   - Visit https://keap.com and sign in with your account

2. **Navigate to API Settings**
   - Go to Settings → API & Data

3. **Create or Copy API Key**
   - For OAuth: Create a new OAuth app and copy the Access Token
   - For REST API Key: Use your REST API Key directly

4. **Paste in AutomatorWP**
   - Login to WordPress admin
   - Go to AutomatorWP → Settings → Keap
   - Paste your Access Token
   - Click "Save Credentials"

## Troubleshooting

### API Connection Failed

**Solution:**
1. Verify your Access Token is correct in Keap settings
2. Ensure your Keap account has API access enabled
3. Check that your API key hasn't expired
4. Enable debugging to see detailed error messages

### Contact Not Found Error

This occurs when trying to update a contact that doesn't exist in Keap.

**Solution:**
1. Ensure the contact email is correct
2. Check that contacts are being created in Keap (not just WordPress)
3. Verify the email format is valid

### Cache Issues

If data seems outdated:

```php
// Force refresh in your functions.php
automatorwp_keap_clear_cache();
```

## Performance Optimization

### Recommended Settings

- **Cache Duration**: 12 hours (default)
- **Batch Operations**: Enable if creating multiple contacts
- **API Timeout**: 30 seconds (configured in plugin)

### Monitoring API Usage

1. Log in to Keap Dashboard
2. Check Settings → API & Data → Usage
3. Monitor your API calls to avoid rate limiting

## Security Best Practices

1. **Never** share your Access Token
2. **Store** credentials in wp-config.php constants if possible
3. **Rotate** API keys periodically in Keap
4. **Monitor** automation logs for suspicious activity
5. **Restrict** automation admin access via WordPress roles

## Migration from Older Versions

If upgrading from version 1.0.0:

1. Your existing automations will continue to work
2. New actions are optional - add as needed
3. Cache is cleared automatically on update
4. No database changes required

## Support

For issues:

1. Check the [DOCUMENTATION.md](DOCUMENTATION.md)
2. Enable debug logging and check logs
3. Verify API credentials in Keap
4. Contact AutomatorWP support with logs
