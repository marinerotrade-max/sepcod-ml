# Testing Guide - Auto Multilingual SEO Translator

This document provides a comprehensive testing checklist for the Auto Multilingual SEO Translator WordPress plugin.

## Pre-Testing Setup

Before running tests, ensure:

1. WordPress test environment is set up (version 5.0+)
2. PHP 7.2+ is installed
3. MySQL 5.6+ is installed
4. Google Cloud Translation API key is available for testing
5. Test domain/subdomain is available

## Installation Testing

### Test 1: Plugin Activation

**Objective**: Verify the plugin activates without errors

**Steps**:
1. Upload plugin to `/wp-content/plugins/`
2. Go to WordPress admin → Plugins
3. Find "Auto Multilingual SEO Translator"
4. Click "Activate"

**Expected Result**:
- Plugin activates successfully
- No PHP errors or warnings
- Plugin menu appears in admin sidebar
- Database table `wp_amst_translations` is created
- Default options are set

**Validation**:
```sql
-- Check if table exists
SHOW TABLES LIKE '%amst_translations%';

-- Verify table structure
DESCRIBE wp_amst_translations;

-- Check default options
SELECT * FROM wp_options WHERE option_name LIKE 'amst_%';
```

### Test 2: Plugin Deactivation

**Objective**: Verify the plugin deactivates cleanly

**Steps**:
1. Go to Plugins menu
2. Deactivate "Auto Multilingual SEO Translator"

**Expected Result**:
- Plugin deactivates without errors
- Data remains in database (not deleted)
- Site continues to function normally

### Test 3: Plugin Reactivation

**Objective**: Verify the plugin can be reactivated

**Steps**:
1. Reactivate the plugin

**Expected Result**:
- Plugin activates successfully
- Previous settings are retained
- No duplicate data created

## Configuration Testing

### Test 4: API Key Configuration

**Objective**: Verify API key can be saved and tested

**Steps**:
1. Go to Multilingual SEO → Settings
2. Enter a valid Google Cloud Translation API key
3. Click "Test API Connection"
4. Click "Save Settings"

**Expected Result**:
- API test returns success message
- API test shows sample translation
- Settings save successfully
- Success notice appears

### Test 5: Invalid API Key

**Objective**: Verify proper error handling for invalid API key

**Steps**:
1. Enter an invalid API key
2. Click "Test API Connection"

**Expected Result**:
- Error message is displayed
- Specific error from Google API is shown
- No PHP errors occur

### Test 6: Default Language Configuration

**Objective**: Verify default language can be set

**Steps**:
1. Select a default language from dropdown
2. Save settings

**Expected Result**:
- Language is saved
- Default language shows as selected on reload

## Language Management Testing

### Test 7: Enable Multiple Languages

**Objective**: Verify multiple languages can be enabled

**Steps**:
1. Go to Multilingual SEO → Languages
2. Select multiple languages (e.g., Spanish, French, German)
3. Save languages

**Expected Result**:
- Languages are saved
- Selected languages remain checked on reload
- Default language cannot be disabled

### Test 8: Disable Language

**Objective**: Verify languages can be disabled

**Steps**:
1. Uncheck a previously enabled language
2. Save languages

**Expected Result**:
- Language is removed from enabled list
- Existing translations remain in database

## Translation Testing

### Test 9: Automatic Page Translation

**Objective**: Verify pages are translated automatically

**Steps**:
1. Create a test page with content in default language
2. Publish the page
3. Visit the page with language prefix (e.g., `/es/test-page/`)

**Expected Result**:
- Content is translated to target language
- HTML structure is preserved
- Links and images work correctly
- Page title is translated

### Test 10: Post Translation

**Objective**: Verify blog posts are translated

**Steps**:
1. Create a test post
2. Visit post with language prefix

**Expected Result**:
- Post content is translated
- Post title is translated
- Post excerpt is translated

### Test 11: Translation Caching

**Objective**: Verify translations are cached

**Steps**:
1. Visit a translated page for the first time
2. Go to Multilingual SEO → Statistics
3. Note translation count
4. Visit the same translated page again
5. Check statistics again

**Expected Result**:
- First visit creates new translation in database
- Second visit uses cached translation (hits counter increases)
- No duplicate translations created

### Test 12: Batch Translation

**Objective**: Verify batch translation works for pages with multiple text elements

**Steps**:
1. Create a page with multiple paragraphs
2. Visit translated version

**Expected Result**:
- All text blocks are translated
- Translation is relatively fast
- API calls are minimized (batch processing)

## SEO Testing

### Test 13: Hreflang Tags

**Objective**: Verify hreflang tags are generated correctly

**Steps**:
1. Enable hreflang in settings
2. Visit any page
3. View page source
4. Check `<head>` section

**Expected Result**:
```html
<link rel="alternate" hreflang="en" href="https://yoursite.com/page/" />
<link rel="alternate" hreflang="es" href="https://yoursite.com/es/page/" />
<link rel="alternate" hreflang="fr" href="https://yoursite.com/fr/page/" />
<link rel="alternate" hreflang="x-default" href="https://yoursite.com/page/" />
```

### Test 14: Canonical Tags

**Objective**: Verify canonical tags are correct for each language

**Steps**:
1. Enable canonical tags in settings
2. Visit page in different languages
3. Check canonical tag in each version

**Expected Result**:
- English: `<link rel="canonical" href="https://yoursite.com/page/" />`
- Spanish: `<link rel="canonical" href="https://yoursite.com/es/page/" />`
- Each language has its own canonical

### Test 15: Multilingual Sitemap

**Objective**: Verify sitemap includes all language versions

**Steps**:
1. Enable multilingual sitemap
2. Visit WordPress sitemap (if using WP 5.5+)
3. Or check custom sitemap generation

**Expected Result**:
- Sitemap includes entries for all enabled languages
- Each entry has alternate language links

## Cache Management Testing

### Test 16: Clear Cache

**Objective**: Verify cache can be cleared

**Steps**:
1. Go to Multilingual SEO → Statistics
2. Note current translation count
3. Click "Clear Cache"
4. Confirm action
5. Check statistics

**Expected Result**:
- Cache clearing success message appears
- Translation count drops to zero
- Statistics page reloads
- Database table is cleared

### Test 17: Cache Expiry

**Objective**: Verify cache expiry setting works

**Steps**:
1. Set cache expiry to 60 seconds
2. Visit a translated page
3. Wait 61 seconds
4. Visit the same page again

**Expected Result**:
- After expiry, translation is regenerated
- Fresh API call is made (verify in statistics)

## Integration Testing

### Test 18: Elementor Integration

**Objective**: Verify Elementor content is translated

**Prerequisites**: Elementor plugin installed

**Steps**:
1. Create a page with Elementor
2. Add various widgets (text, heading, button)
3. Publish page
4. Visit translated version

**Expected Result**:
- All Elementor widget text is translated
- Layout is preserved
- Buttons and links work

### Test 19: Directorist Integration

**Objective**: Verify Directorist listings are translated

**Prerequisites**: Directorist plugin installed

**Steps**:
1. Create a directory listing
2. Visit listing in different language

**Expected Result**:
- Listing title is translated
- Listing content is translated
- Listing description is translated

### Test 20: LiteSpeed Cache Integration

**Objective**: Verify LiteSpeed Cache compatibility

**Prerequisites**: LiteSpeed Cache plugin installed

**Steps**:
1. Enable LiteSpeed Cache
2. Visit translated pages
3. Check cache varies by language

**Expected Result**:
- Each language is cached separately
- Cache tags include language identifier
- Purging cache works correctly

## Shortcode Testing

### Test 21: Language Switcher Shortcode

**Objective**: Verify language switcher displays correctly

**Steps**:
1. Add `[amst_language_switcher]` to a page
2. Publish and view page

**Expected Result**:
- Language switcher displays
- All enabled languages are shown
- Current language is highlighted
- Links work and switch languages

### Test 22: Current Language Shortcode

**Objective**: Verify current language shortcode works

**Steps**:
1. Add `[amst_current_language format="code"]` to a page
2. Add `[amst_current_language format="name"]` to a page
3. View page in different languages

**Expected Result**:
- Code format shows language code (e.g., "en", "es")
- Name format shows language name (e.g., "English", "Spanish")
- Updates correctly for each language

### Test 23: Manual Translation Shortcode

**Objective**: Verify manual translation shortcode works

**Steps**:
1. Add `[amst_translate target="es"]Hello World[/amst_translate]` to a page
2. View page

**Expected Result**:
- Text is translated to specified language
- Translation is correct

## Performance Testing

### Test 24: Page Load Speed

**Objective**: Measure impact on page load time

**Steps**:
1. Measure page load without translation
2. Enable translation and measure again
3. After cache warm-up, measure again

**Expected Result**:
- First load (uncached): Some increase expected
- Cached loads: Minimal impact
- Impact should be < 100ms for cached translations

### Test 25: Large Content Translation

**Objective**: Verify handling of large content blocks

**Steps**:
1. Create a page with 5000+ words
2. Translate the page

**Expected Result**:
- Page translates successfully
- No timeouts occur
- Content is complete

### Test 26: High Traffic Simulation

**Objective**: Test performance under load

**Steps**:
1. Use load testing tool (e.g., Apache Bench)
2. Simulate 100 concurrent requests to translated pages

**Expected Result**:
- Server handles load without errors
- Response times remain acceptable
- Database performance is good

## Security Testing

### Test 27: SQL Injection

**Objective**: Verify protection against SQL injection

**Steps**:
1. Try entering SQL commands in API key field
2. Try SQL injection in language settings

**Expected Result**:
- Input is sanitized
- No SQL errors occur
- No database corruption

### Test 28: XSS Protection

**Objective**: Verify protection against XSS attacks

**Steps**:
1. Try entering `<script>alert('XSS')</script>` in various fields
2. Check if script executes

**Expected Result**:
- Scripts are escaped
- No JavaScript execution occurs
- Output is safely escaped

### Test 29: CSRF Protection

**Objective**: Verify CSRF protection

**Steps**:
1. Try submitting forms without nonce
2. Try with invalid nonce

**Expected Result**:
- Requests are rejected
- Error message is shown
- Settings are not saved

### Test 30: Capability Checks

**Objective**: Verify only authorized users can access admin features

**Steps**:
1. Log in as subscriber/contributor
2. Try accessing plugin admin pages

**Expected Result**:
- Non-admin users cannot access settings
- Proper permission error is shown

## Error Handling Testing

### Test 31: API Timeout

**Objective**: Verify handling of API timeouts

**Steps**:
1. Simulate slow/unresponsive API
2. Visit translated page

**Expected Result**:
- Request times out gracefully
- Original content is shown
- No PHP fatal errors

### Test 32: Invalid Language Code

**Objective**: Verify handling of invalid language codes

**Steps**:
1. Try accessing `/xyz/page/` (invalid language)

**Expected Result**:
- Page shows in default language or 404
- No PHP errors

### Test 33: Missing Database Table

**Objective**: Verify handling if database table is missing

**Steps**:
1. Manually drop the translations table
2. Try translating content

**Expected Result**:
- Plugin recreates table or shows admin notice
- No fatal errors

## Compatibility Testing

### Test 34: WordPress Multisite

**Objective**: Verify multisite compatibility

**Steps**:
1. Install on multisite network
2. Activate network-wide or per-site

**Expected Result**:
- Plugin works on each site independently
- Settings are per-site
- No conflicts between sites

### Test 35: Popular Themes

**Objective**: Verify theme compatibility

**Steps**:
1. Test with popular themes (Twenty Twenty-Four, Astra, GeneratePress)

**Expected Result**:
- Plugin works with all tested themes
- Styling is appropriate
- No layout issues

### Test 36: WordPress Updates

**Objective**: Verify compatibility with WordPress updates

**Steps**:
1. Test with latest WordPress version
2. Check for deprecated function warnings

**Expected Result**:
- No deprecated warnings
- All features work correctly

## Documentation Testing

### Test 37: README Accuracy

**Objective**: Verify README instructions are accurate

**Steps**:
1. Follow README installation steps exactly
2. Follow usage examples

**Expected Result**:
- All instructions work as documented
- Examples produce expected results

### Test 38: Installation Guide

**Objective**: Verify installation guide is complete

**Steps**:
1. Fresh install following INSTALLATION.md

**Expected Result**:
- Guide covers all necessary steps
- No missing information
- New user can successfully install

## Regression Testing

After any updates, run full test suite to ensure:
- No existing functionality is broken
- New features work as expected
- Performance remains acceptable
- Security is maintained

## Automated Testing (Future)

Consider adding:
- PHPUnit tests for core classes
- Integration tests for WordPress hooks
- Continuous integration pipeline
- Code coverage analysis

## Test Environment Specifications

Recommended test environments:

1. **Local Development**
   - WordPress: Latest version
   - PHP: 7.4, 8.0, 8.1
   - MySQL: 5.7, 8.0

2. **Staging Server**
   - Similar to production
   - All production plugins installed
   - Real API key (test project)

3. **Production Monitoring**
   - Error logging enabled
   - Performance monitoring
   - User feedback collection

## Reporting Issues

When reporting test failures:

1. Specify test number and name
2. Include environment details (WP version, PHP version, etc.)
3. Provide error messages and logs
4. Include steps to reproduce
5. Attach screenshots if UI-related

## Test Status Tracking

Use this checklist to track testing progress:

```
[ ] Test 1: Plugin Activation
[ ] Test 2: Plugin Deactivation
[ ] Test 3: Plugin Reactivation
[ ] Test 4: API Key Configuration
[ ] Test 5: Invalid API Key
[ ] Test 6: Default Language Configuration
[ ] Test 7: Enable Multiple Languages
[ ] Test 8: Disable Language
[ ] Test 9: Automatic Page Translation
[ ] Test 10: Post Translation
[ ] Test 11: Translation Caching
[ ] Test 12: Batch Translation
[ ] Test 13: Hreflang Tags
[ ] Test 14: Canonical Tags
[ ] Test 15: Multilingual Sitemap
[ ] Test 16: Clear Cache
[ ] Test 17: Cache Expiry
[ ] Test 18: Elementor Integration
[ ] Test 19: Directorist Integration
[ ] Test 20: LiteSpeed Cache Integration
[ ] Test 21: Language Switcher Shortcode
[ ] Test 22: Current Language Shortcode
[ ] Test 23: Manual Translation Shortcode
[ ] Test 24: Page Load Speed
[ ] Test 25: Large Content Translation
[ ] Test 26: High Traffic Simulation
[ ] Test 27: SQL Injection
[ ] Test 28: XSS Protection
[ ] Test 29: CSRF Protection
[ ] Test 30: Capability Checks
[ ] Test 31: API Timeout
[ ] Test 32: Invalid Language Code
[ ] Test 33: Missing Database Table
[ ] Test 34: WordPress Multisite
[ ] Test 35: Popular Themes
[ ] Test 36: WordPress Updates
[ ] Test 37: README Accuracy
[ ] Test 38: Installation Guide
```

---

This testing guide should be updated as new features are added or issues are discovered.
