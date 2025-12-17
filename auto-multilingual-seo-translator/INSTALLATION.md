# Installation Guide

## Auto Multilingual SEO Translator WordPress Plugin

### Prerequisites

Before installing the plugin, ensure your server meets these requirements:

- **WordPress**: Version 6.0 or higher
- **PHP**: Version 8.0 or higher
- **MySQL**: Version 5.6 or higher
- **Google Translate API Key**: Required for translation functionality

### Step 1: Obtain Google Translate API Key

1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the "Cloud Translation API":
   - Go to "APIs & Services" > "Library"
   - Search for "Cloud Translation API"
   - Click "Enable"
4. Create API credentials:
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "API Key"
   - Copy the generated API key
5. (Optional but recommended) Restrict the API key:
   - Click on the API key name
   - Under "Application restrictions", select "HTTP referrers"
   - Add your website domain
   - Under "API restrictions", select "Restrict key"
   - Choose "Cloud Translation API"
   - Save

### Step 2: Install the Plugin

#### Method 1: WordPress Admin Upload

1. Download the plugin as a ZIP file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin**
5. Choose the ZIP file and click **Install Now**
6. Click **Activate Plugin**

#### Method 2: Manual Installation via FTP

1. Download and extract the plugin ZIP file
2. Connect to your server via FTP
3. Upload the `auto-multilingual-seo-translator` folder to `/wp-content/plugins/`
4. Log in to WordPress admin
5. Navigate to **Plugins**
6. Find "Auto Multilingual SEO Translator" and click **Activate**

#### Method 3: WP-CLI Installation

```bash
cd /path/to/wordpress
wp plugin install auto-multilingual-seo-translator.zip --activate
```

### Step 3: Configure the Plugin

1. After activation, navigate to **Settings > Multilingual SEO**
2. Enter your Google Translate API key in the "Google Translate API Key" field
3. Select your **Default Language** (the language your content is originally written in)
4. Choose **Enabled Languages** (check all languages you want to support)
5. Configure additional settings:
   - **Enable Caching**: Recommended for better performance
   - **Enable Auto Language Detection**: Automatically detect user's language from browser
6. Click **Save Changes**

### Step 4: Add Language Switcher

#### Option 1: Widget Area

1. Go to **Appearance > Widgets**
2. Find "Language Switcher" widget
3. Drag it to your desired widget area (e.g., sidebar, footer)
4. Configure the widget:
   - Set a title (optional)
   - Choose display style (dropdown or list)
5. Save

#### Option 2: Shortcode in Content

Add this shortcode to any page, post, or text widget:

```
[language_switcher style="dropdown"]
```

Or for list style:

```
[language_switcher style="list"]
```

#### Option 3: PHP Code in Theme

Add this code to your theme template files (e.g., header.php, footer.php):

```php
<?php 
if (function_exists('amst_init')) {
    echo do_shortcode('[language_switcher]');
}
?>
```

#### Option 4: Elementor (if using Elementor)

1. Edit your page with Elementor
2. Search for "Language Switcher" widget
3. Drag it to your desired location
4. Customize the settings

### Step 5: Verify Installation

1. Visit your website's frontend
2. Look for the language switcher
3. Try switching to a different language
4. Verify that content is being translated
5. Check browser console for any JavaScript errors

### Optional: Configure Compatible Plugins

#### LiteSpeed Cache Configuration

If using LiteSpeed Cache:

1. No additional configuration needed - the plugin automatically integrates
2. Cache will vary by language
3. Translation AJAX requests are automatically excluded from cache

#### Rank Math SEO Configuration

If using Rank Math:

1. No additional configuration needed
2. SEO titles and descriptions will be automatically translated
3. Breadcrumbs will be translated
4. Sitemap will include language alternates

#### Wordfence Configuration

If using Wordfence:

1. No additional configuration needed
2. Translation requests are automatically whitelisted
3. Language cookies are excluded from security scans

### Troubleshooting

#### Plugin Not Translating Content

1. **Check API Key**: Ensure your Google Translate API key is correct
2. **Verify API is Enabled**: Make sure Cloud Translation API is enabled in Google Cloud Console
3. **Check Billing**: Ensure billing is enabled on your Google Cloud project (required even for free tier)
4. **Clear Cache**: Go to Settings > Multilingual SEO and clear the cache
5. **Check Error Logs**: Look at WordPress debug logs for API errors

#### Language Switcher Not Appearing

1. **Check Widget/Shortcode**: Ensure you've added the language switcher via widget or shortcode
2. **Verify Languages**: Make sure you have multiple languages enabled in settings
3. **Check Theme Compatibility**: Test with a default WordPress theme to rule out theme conflicts
4. **Clear Browser Cache**: Clear your browser cache and reload the page

#### Translations Are Cached Incorrectly

1. **Clear Translation Cache**: Go to Settings > Multilingual SEO
2. **Clear LiteSpeed Cache**: If using LiteSpeed, purge all cache
3. **Clear Browser Cache**: Clear your browser cache
4. **Disable and Re-enable Cache**: Toggle the cache setting off and on

#### API Quota Exceeded

1. **Check Google Cloud Console**: View your API usage
2. **Increase Quota**: Request a quota increase or upgrade your billing plan
3. **Enable Caching**: Make sure caching is enabled to reduce API calls
4. **Limit Languages**: Reduce the number of enabled languages

### Performance Optimization

For optimal performance:

1. **Enable Caching**: Keep the caching option enabled
2. **Use Object Cache**: Install Redis or Memcached for WordPress object caching
3. **Use CDN**: Serve static assets (CSS/JS) via CDN
4. **Enable LiteSpeed Cache**: If available on your server
5. **Optimize API Usage**: Only enable languages you actually need

### Security Best Practices

1. **Restrict API Key**: Configure API key restrictions in Google Cloud Console
2. **Keep WordPress Updated**: Always use the latest WordPress version
3. **Use HTTPS**: Ensure your site uses SSL/TLS encryption
4. **Monitor API Usage**: Regularly check Google Cloud Console for unusual activity
5. **Use Wordfence**: Install Wordfence for additional security (fully compatible)

### Uninstallation

To completely remove the plugin:

1. Deactivate the plugin from **Plugins** page
2. Delete the plugin
3. All settings and cached translations will be automatically removed

**Note**: Original content is never modified by this plugin. Deactivating removes all translations.

### Support

For support and bug reports:

- GitHub Issues: [https://github.com/marinerotrade-max/sepcod-ml/issues](https://github.com/marinerotrade-max/sepcod-ml/issues)
- Documentation: [README.md](README.md)

### Next Steps

After installation:

1. Test with a few pages to ensure translations work correctly
2. Monitor Google Translate API usage in Cloud Console
3. Customize the language switcher appearance with CSS if needed
4. Set up multilingual SEO optimization with Rank Math (if using)
5. Consider enabling CDN for better global performance
