# Auto Multilingual SEO Translator

A comprehensive WordPress plugin that automatically translates website content into multiple languages using Google Translate API, with advanced SEO features and extensive plugin compatibility.

## Features

- 🌍 **Automatic Translation**: Seamlessly translate your entire WordPress site into multiple languages
- 🔍 **SEO Optimized**: Built-in SEO enhancements with hreflang tags, Open Graph tags, and sitemap support
- ⚡ **High Performance**: Advanced caching system for fast page loads
- 🎯 **Smart Detection**: Automatic language detection based on user browser settings
- 🔌 **Plugin Compatibility**: 
  - LiteSpeed Cache integration
  - Rank Math SEO support
  - Wordfence security compatibility
  - Elementor page builder integration
  - Directorist directory plugin support
- 🎨 **User-Friendly**: Easy-to-use language switcher widget and shortcode
- 🛡️ **Secure**: Server-side translations with security best practices

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- Google Translate API key
- MySQL 5.6 or higher

## Installation

1. Download the plugin files
2. Upload the `auto-multilingual-seo-translator` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > Multilingual SEO to configure the plugin

## Configuration

### Getting a Google Translate API Key

1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the "Cloud Translation API"
4. Create credentials (API key)
5. Copy the API key to the plugin settings

### Basic Setup

1. Navigate to **Settings > Multilingual SEO** in your WordPress admin
2. Enter your Google Translate API key
3. Select your default language
4. Choose which languages to enable
5. Configure caching and auto-detection settings
6. Save changes

## Usage

### Language Switcher Widget

Add the language switcher to your site in three ways:

#### 1. WordPress Widget
- Go to **Appearance > Widgets**
- Drag "Language Switcher" to your desired widget area
- Configure the widget settings

#### 2. Shortcode
Add this shortcode anywhere in your content:
```
[language_switcher style="dropdown"]
```

Supported styles:
- `dropdown` - Dropdown selector
- `list` - List of language links

#### 3. PHP Code
Add this code to your theme template:
```php
<?php echo do_shortcode('[language_switcher]'); ?>
```

### For Elementor Users

1. Edit your page with Elementor
2. Search for "Language Switcher" widget
3. Drag it to your desired location
4. Customize the style and appearance

### For Directorist Users

The plugin automatically translates:
- Listing titles and descriptions
- Custom fields (where applicable)
- Categories and tags
- Search form labels

## Compatibility

### LiteSpeed Cache

The plugin automatically:
- Creates separate cache entries for each language
- Excludes translation AJAX requests from cache
- Purges cache when translations are updated

### Rank Math SEO

Automatic integration with:
- SEO titles and descriptions
- Breadcrumbs
- Schema markup
- XML sitemaps

### Wordfence Security

The plugin:
- Whitelists translation requests
- Excludes language cookies from security scans
- Maintains security best practices

## Advanced Features

### Caching System

The plugin uses a multi-layer caching system:
1. WordPress object cache (in-memory)
2. Transient cache (database)
3. Compatible with LiteSpeed Cache

### SEO Features

- **hreflang tags**: Automatic generation for better international SEO
- **Open Graph tags**: Language-specific social media tags
- **Multilingual sitemaps**: XML sitemaps with language alternates
- **Translated meta tags**: SEO titles and descriptions in all languages

### Language Detection

Automatic language detection based on:
- User browser settings (Accept-Language header)
- Previously selected language (stored in cookie)
- Fallback to default language

## Hooks and Filters

### Actions

```php
// Clear all translation cache
do_action('amst_clear_cache');

// After translation is saved
do_action('amst_translation_saved', $text, $source_lang, $target_lang, $translation);
```

### Filters

```php
// Modify translation before saving
add_filter('amst_before_translate', function($text, $source, $target) {
    return $text;
}, 10, 3);

// Modify translation after translation
add_filter('amst_after_translate', function($translation, $original, $source, $target) {
    return $translation;
}, 10, 4);

// Exclude content from translation
add_filter('amst_exclude_from_translation', function($exclude, $content) {
    return $exclude;
}, 10, 2);
```

## Troubleshooting

### Translations not appearing

1. Verify your Google Translate API key is correct
2. Check that the target language is enabled in settings
3. Clear the translation cache
4. Check PHP error logs for API errors

### Cache issues

1. Go to Settings > Multilingual SEO
2. Clear the translation cache
3. If using LiteSpeed Cache, purge the LiteSpeed cache as well

### Compatibility issues

1. Deactivate other translation plugins
2. Check for JavaScript conflicts in browser console
3. Test with a default WordPress theme to rule out theme issues

## Performance Optimization

### Recommended Settings

For best performance:
1. Enable caching in plugin settings
2. Use LiteSpeed Cache if available
3. Enable CDN for static assets
4. Use object caching (Redis/Memcached)

### API Usage Optimization

To reduce API calls:
1. Enable caching (enabled by default)
2. Set appropriate cache expiration
3. Consider translating only essential content
4. Use exclusion filters for non-translatable content

## Support

For support and feature requests:
- GitHub: [https://github.com/marinerotrade-max/sepcod-ml](https://github.com/marinerotrade-max/sepcod-ml)
- Issues: [https://github.com/marinerotrade-max/sepcod-ml/issues](https://github.com/marinerotrade-max/sepcod-ml/issues)

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Changelog

### Version 1.0.0
- Initial release
- Google Translate API integration
- Multi-language support (30+ languages)
- Caching system
- Language detection
- SEO enhancements
- LiteSpeed Cache compatibility
- Rank Math SEO compatibility
- Wordfence security compatibility
- Elementor integration
- Directorist integration
- Language switcher widget and shortcode

## Credits

Developed by Marine Ro Trade Max

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
