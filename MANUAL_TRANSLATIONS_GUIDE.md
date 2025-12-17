# Manual Translations System Guide

## Overview

The Auto Multilingual SEO Translator plugin now features a comprehensive manual translation system with automatic fallback. This ensures:

1. **Manual translations are ALWAYS prioritized** over automatic translations
2. **Automatic translations are NEVER stored permanently** (only in temporary cache)
3. **Consistent language application** across all site elements
4. **Professional-grade stability** for production websites

## How It Works

### Translation Priority Hierarchy

```
1. Manual Translation (from database) ← HIGHEST PRIORITY
   ↓ (if not found)
2. Automatic Translation (from transient cache, 1 hour TTL)
   ↓ (if not found or expired)
3. Generate New Automatic Translation (via Google API, save to transient)
   ↓ (if API fails)
4. Fallback to Default Language ← LOWEST PRIORITY
```

### Storage Strategy

- **Manual Translations**: Permanently stored in `wp_amst_translations` table with `translation_type='manual'`
- **Automatic Translations**: Stored in WordPress transients for 1 hour only (NOT in database)
- **Default Fallback**: Original content in default language

## Adding Manual Translations

### Method 1: REST API

```php
// Add manual translation
POST /wp-json/amst/v1/manual-translations
{
  "original_text": "Welcome to our website",
  "translated_text": "Dobrodošli na našu web stranicu",
  "target_lang": "hr",
  "source_lang": "en",
  "content_type": "general"
}

// Update existing translation
PUT /wp-json/amst/v1/manual-translations/123
{
  "translated_text": "Updated translation text"
}

// Delete manual translation
DELETE /wp-json/amst/v1/manual-translations/123

// Get all manual translations
GET /wp-json/amst/v1/manual-translations?target_language=hr
```

### Method 2: PHP Code

```php
// Get the manual translations handler
$manual_translations = amst()->manual_translations;

// Add a manual translation
$manual_translations->add_manual_translation(
    'Welcome to our website',           // Original text
    'Dobrodošli na našu web stranicu', // Translated text
    'hr',                               // Target language (Croatian)
    'en',                               // Source language (English)
    'general'                           // Content type
);

// Update a manual translation
$manual_translations->update_manual_translation(
    123,                                // Translation ID
    'Updated translation text'          // New translated text
);

// Delete a manual translation
$manual_translations->delete_manual_translation( 123 );

// Get all manual translations for a language
$translations = $manual_translations->get_manual_translations(
    array( 'target_language' => 'hr' )
);

// Export manual translations
$export_data = $manual_translations->export_manual_translations( 'hr' );

// Import manual translations
$result = $manual_translations->import_manual_translations( $translations_array );
```

## Language Consistency Features

### Content Elements Automatically Translated

The plugin now applies translations consistently to:

- **Page Content**: `the_content`, `the_excerpt`
- **Titles**: `the_title`, `single_post_title`, document title
- **Menus**: Navigation menus and menu items
- **Widgets**: Widget titles and content
- **Site Info**: Blogname and blogdescription
- **Metadata**: SEO metadata (Yoast, etc.)
- **Archive Titles**: Category, tag, and archive titles
- **Comments**: Comment text and excerpts
- **Custom Fields**: ACF and other custom fields
- **Buttons**: Submit buttons and form text

### No Language Mixing

The comprehensive filter system ensures that once a language is selected:
- ALL content on the page uses that language
- NO mixing of languages occurs
- Consistent experience across the entire website

## Database Schema

### Translation Table Structure

```sql
CREATE TABLE wp_amst_translations (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    content_hash varchar(64) NOT NULL,
    source_language varchar(10) NOT NULL,
    target_language varchar(10) NOT NULL,
    original_text longtext NOT NULL,
    translated_text longtext NOT NULL,
    content_type varchar(50) DEFAULT 'general',
    translation_type ENUM('manual', 'automatic') DEFAULT 'automatic',
    is_manual tinyint(1) DEFAULT 0,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    hits bigint(20) DEFAULT 0,
    PRIMARY KEY (id),
    KEY translation_type (translation_type),
    KEY is_manual (is_manual),
    UNIQUE KEY unique_translation (content_hash, source_language, target_language)
);
```

### Migration

The plugin automatically migrates existing installations:
- Adds `translation_type` and `is_manual` columns
- Preserves existing data
- Marks all existing translations as 'automatic'

## Best Practices

### When to Use Manual Translations

1. **Brand Names**: Company names, product names that shouldn't be translated
2. **Technical Terms**: Industry-specific terminology
3. **Marketing Copy**: Headlines, slogans, CTAs that need precise wording
4. **Legal Text**: Terms of service, privacy policy
5. **UI Strings**: Button text, form labels for consistency

### When to Use Automatic Translations

1. **General Content**: Blog posts, articles, descriptions
2. **User-Generated Content**: Comments, reviews
3. **Dynamic Content**: Date/time strings, notifications
4. **High-Volume Content**: Large amounts of text that change frequently

### Performance Considerations

- Manual translations have zero API cost
- Automatic translations cached for 1 hour reduce API calls
- Transient storage keeps database lean and fast
- Object caching further improves performance

## Import/Export

### Export Manual Translations

```php
$export_data = amst()->manual_translations->export_manual_translations( 'hr' );

// Returns array of translations:
[
    [
        'original_text' => 'Welcome',
        'translated_text' => 'Dobrodošli',
        'source_language' => 'en',
        'target_language' => 'hr',
        'content_type' => 'general'
    ],
    // ...
]

// Save to JSON file
file_put_contents( 'translations.json', json_encode( $export_data, JSON_PRETTY_PRINT ) );
```

### Import Manual Translations

```php
// Load from JSON file
$translations = json_decode( file_get_contents( 'translations.json' ), true );

// Import
$result = amst()->manual_translations->import_manual_translations( $translations );

// Result contains:
// [
//     'imported' => 150,
//     'failed' => 5,
//     'total' => 155
// ]
```

## Troubleshooting

### Manual Translation Not Showing

1. Check if translation exists in database:
```sql
SELECT * FROM wp_amst_translations 
WHERE is_manual = 1 
AND target_language = 'hr';
```

2. Clear cache:
```php
wp_cache_flush();
delete_transient( 'amst_auto_*' );
```

3. Verify content hash matches:
```php
$hash = hash( 'sha256', 'Your exact original text' );
```

### Automatic Translation Not Working

1. Check API key is configured
2. Verify language is enabled
3. Check transient exists: `get_transient( 'amst_auto_...' )`
4. Review error logs for API failures

### Language Mixing on Page

1. Purge all caches (LiteSpeed, object cache, transients)
2. Verify comprehensive filters are loaded
3. Check theme/plugins aren't bypassing WordPress filters

## Support

For issues, feature requests, or questions:
- GitHub: https://github.com/marinerotrade-max/sepcod-ml
- Documentation: See INSTALLATION.md and README.md
