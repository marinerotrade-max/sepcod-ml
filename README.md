# Auto Multilingual SEO Translator

A powerful WordPress plugin that automatically translates all website content into multiple languages using Google Cloud Translation API with server-side rendering for optimal SEO performance.

## Features

### Core Translation Features
- **Automatic Translation**: Translates all website content automatically using Google Cloud Translation API
- **60+ Languages**: Support for over 60 languages including major world languages
- **Server-Side Rendering**: All translations are rendered server-side for SEO benefits
- **URL-Based Language Detection**: Languages are detected from URL prefixes (e.g., `/es/`, `/fr/`)
- **Smart Caching**: Database-backed translation cache reduces API calls and improves performance

### SEO Optimization
- **Hreflang Tags**: Automatic generation of hreflang tags for all language versions
- **Canonical Tags**: Proper canonical tags for each language version
- **Multilingual Sitemap**: Automatic sitemap generation with language alternatives
- **Meta Tag Translation**: Translates SEO meta descriptions and titles

### Plugin Integrations
- **Elementor**: Full compatibility with Elementor page builder
- **Directorist**: Integration with Directorist directory plugin
- **LiteSpeed Cache**: Optimized for LiteSpeed Cache plugin with language-specific cache variants

### Admin Features
- **Intuitive Dashboard**: Easy-to-use admin interface for managing translations
- **Language Management**: Enable/disable languages with a single click
- **Translation Statistics**: View detailed statistics about translations and cache usage
- **API Testing**: Built-in API connection testing
- **Cache Management**: Clear translation cache with one click

## Installation

### Requirements
- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher
- Google Cloud Translation API key

### Standard Installation

1. Download the plugin files
2. Upload the `auto-multilingual-seo-translator` folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **Multilingual SEO** → **Settings** to configure the plugin

### Configuration

#### 1. Get Google Cloud Translation API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the [Cloud Translation API](https://console.cloud.google.com/apis/library/translate.googleapis.com)
4. Go to **APIs & Services** → **Credentials**
5. Click **Create Credentials** → **API Key**
6. Copy your API key

#### 2. Configure Plugin Settings

1. Go to **Multilingual SEO** → **Settings** in WordPress admin
2. Enter your Google Cloud Translation API key
3. Select your default language (the original language of your content)
4. Configure content translation options:
   - Enable translation for Pages
   - Enable translation for Posts
   - Enable translation for Custom Post Types
5. Configure SEO settings:
   - Enable Hreflang Tags
   - Enable Canonical Tags
   - Enable Multilingual Sitemap
6. Configure cache settings:
   - Enable translation cache
   - Set cache expiry time (default: 30 days)
7. Click **Test API Connection** to verify your setup
8. Click **Save Settings**

#### 3. Enable Languages

1. Go to **Multilingual SEO** → **Languages**
2. Select the languages you want to enable
3. Click **Save Languages**

## Usage

### Accessing Translated Pages

Once configured, your content is automatically available in all enabled languages:

- Default language: `https://yoursite.com/page/`
- Spanish: `https://yoursite.com/es/page/`
- French: `https://yoursite.com/fr/page/`
- German: `https://yoursite.com/de/page/`

### Language Switcher

Add a language switcher to your site using:

**Shortcode:**
```
[amst_language_switcher]
```

**PHP:**
```php
<?php echo amst()->language_detector->get_language_switcher(); ?>
```

**Widget:**
Add the shortcode to any widget area that supports shortcodes.

### Additional Shortcodes

**Display Current Language:**
```
[amst_current_language format="code"]  // Displays: en, es, fr, etc.
[amst_current_language format="name"]  // Displays: English, Spanish, French, etc.
```

**Translate Specific Text:**
```
[amst_translate target="es"]Hello World[/amst_translate]
```

## Supported Languages

The plugin supports 60+ languages including:

- English (en)
- Spanish (es)
- French (fr)
- German (de)
- Italian (it)
- Portuguese (pt)
- Russian (ru)
- Chinese Simplified (zh)
- Chinese Traditional (zh-TW)
- Japanese (ja)
- Korean (ko)
- Arabic (ar)
- Hindi (hi)
- And many more...

## Performance & Caching

### Translation Cache

Translations are automatically cached in a custom database table:
- Reduces API calls and costs
- Improves page load times
- Tracks cache hit statistics
- Configurable cache expiry time

### LiteSpeed Cache Integration

The plugin is optimized for LiteSpeed Cache:
- Language-specific cache variants
- Automatic cache purging when translations are cleared
- ESI support for dynamic content

### Best Practices

1. **Enable Caching**: Always keep translation cache enabled
2. **Set Appropriate Expiry**: Use longer cache expiry (30 days) for stable content
3. **Monitor API Usage**: Check statistics regularly to optimize API usage
4. **Clear Cache Strategically**: Only clear cache when content significantly changes

## Troubleshooting

### Translations Not Showing

1. Verify API key is correct in Settings
2. Check that target language is enabled in Languages page
3. Test API connection using the "Test API Connection" button
4. Clear translation cache and try again

### Performance Issues

1. Ensure translation cache is enabled
2. Increase cache expiry time
3. Use a caching plugin (LiteSpeed Cache recommended)
4. Consider enabling only necessary languages

### API Errors

1. Check API key has proper permissions
2. Verify Cloud Translation API is enabled in Google Cloud Console
3. Check API quota limits in Google Cloud Console
4. Review API error messages in browser console

## API Costs

The plugin uses Google Cloud Translation API which has the following pricing:

- **Free Tier**: 500,000 characters per month
- **Paid**: $20 per million characters

### Cost Optimization Tips

1. Enable translation cache to minimize API calls
2. Only enable languages you need
3. Use longer cache expiry times
4. Monitor usage in Google Cloud Console

## Security

The plugin follows WordPress security best practices:

- All inputs are sanitized and validated
- Output is properly escaped
- Nonces protect against CSRF attacks
- Capability checks restrict admin access
- SQL queries use prepared statements

## Support

For support, please visit:

- GitHub Issues: [https://github.com/marinerotrade-max/sepcod-ml/issues](https://github.com/marinerotrade-max/sepcod-ml/issues)
- Documentation: This README file

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Marine Rotrade Max

---

**Note**: This plugin requires a Google Cloud Translation API key which may incur costs based on usage. Please review Google Cloud pricing before use.