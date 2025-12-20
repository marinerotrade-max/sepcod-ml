# Installation Guide - Auto Multilingual SEO Translator

This guide provides detailed instructions for installing and configuring the Auto Multilingual SEO Translator WordPress plugin.

## Prerequisites

Before installing the plugin, ensure your environment meets these requirements:

- **WordPress**: Version 5.0 or higher
- **PHP**: Version 7.2 or higher
- **MySQL**: Version 5.6 or higher
- **Google Cloud Account**: Required for Translation API access

## Step 1: Plugin Installation

### Method 1: Manual Installation

1. Download the plugin files from the repository
2. Extract the ZIP file
3. Upload the `auto-multilingual-seo-translator` folder to `/wp-content/plugins/` directory
4. Activate the plugin through the **Plugins** menu in WordPress

### Method 2: WordPress Dashboard Installation (if available on WordPress.org)

1. Go to **Plugins** → **Add New** in WordPress admin
2. Search for "Auto Multilingual SEO Translator"
3. Click **Install Now**
4. Click **Activate**

## Step 2: Google Cloud Translation API Setup

### 2.1 Create Google Cloud Project

1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Sign in with your Google account
3. Click on the project dropdown at the top
4. Click **New Project**
5. Enter a project name (e.g., "My Website Translations")
6. Click **Create**

### 2.2 Enable Cloud Translation API

1. In the Google Cloud Console, go to **APIs & Services** → **Library**
2. Search for "Cloud Translation API"
3. Click on **Cloud Translation API**
4. Click **Enable**

### 2.3 Create API Key

1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **API Key**
3. Your API key will be generated and displayed
4. (Recommended) Click **Restrict Key** to add security restrictions:
   - Under "Application restrictions", select "HTTP referrers"
   - Add your website URL (e.g., `yoursite.com/*`)
   - Under "API restrictions", select "Restrict key"
   - Select only "Cloud Translation API"
5. Click **Save**
6. Copy your API key

### 2.4 Enable Billing (Required)

Google Cloud Translation API requires billing to be enabled:

1. Go to **Billing** in Google Cloud Console
2. Link a billing account to your project
3. Review pricing at [Cloud Translation Pricing](https://cloud.google.com/translate/pricing)

**Note**: Google offers a free tier of 500,000 characters per month.

## Step 3: Plugin Configuration

### 3.1 Basic Settings

1. In WordPress admin, go to **Multilingual SEO** → **Settings**
2. Enter your **API Key** from Google Cloud
3. Click **Test API Connection** to verify it works
4. Select your **Default Language** (the language of your original content)
5. Click **Save Settings**

### 3.2 Content Translation Settings

In the **Content Translation** section:

1. Check **Pages** to translate all pages
2. Check **Posts** to translate all blog posts
3. Check **Custom Post Types** to translate custom content types
4. Click **Save Settings**

### 3.3 SEO Settings

In the **SEO Settings** section:

1. Check **Enable Hreflang Tags** (recommended for SEO)
2. Check **Enable Canonical Tags** (recommended for SEO)
3. Check **Enable Multilingual Sitemap** (recommended for SEO)
4. Click **Save Settings**

### 3.4 Cache Settings

In the **Cache Settings** section:

1. Check **Enable Cache** (strongly recommended)
2. Set **Cache Expiry** to 2592000 seconds (30 days) or your preferred duration
3. Click **Save Settings**

## Step 4: Enable Languages

1. Go to **Multilingual SEO** → **Languages**
2. Select all languages you want to enable
3. Note: Your default language is automatically enabled
4. Click **Save Languages**

### Recommended Language Selection

Start with languages most relevant to your audience:
- For European sites: English, Spanish, French, German, Italian
- For Asian markets: English, Chinese (Simplified), Japanese, Korean
- For Latin America: English, Spanish, Portuguese
- For Middle East: English, Arabic

## Step 5: Test Your Installation

### 5.1 Verify Translation

1. Visit any page on your site
2. Add a language prefix to the URL (e.g., `/es/` for Spanish)
   - Example: `https://yoursite.com/about/` → `https://yoursite.com/es/about/`
3. Content should be automatically translated
4. Check page source for hreflang tags in `<head>`

### 5.2 Test Language Switcher

1. Add the language switcher to a page:
   ```
   [amst_language_switcher]
   ```
2. View the page
3. Click different language links to verify they work

### 5.3 Check Statistics

1. Go to **Multilingual SEO** → **Statistics**
2. After visiting some translated pages, you should see:
   - Total translations count increasing
   - Cache hits tracking
   - Translations by language

## Step 6: Add Language Switcher

### Option 1: Using Shortcode in Page/Post

1. Edit any page or post
2. Add the shortcode: `[amst_language_switcher]`
3. Publish/Update

### Option 2: Using Widget (if your theme supports shortcodes in widgets)

1. Go to **Appearance** → **Widgets**
2. Add a **Text** or **Custom HTML** widget
3. Add the shortcode: `[amst_language_switcher]`
4. Save

### Option 3: Using PHP in Theme

1. Edit your theme file (e.g., `header.php`)
2. Add this code:
   ```php
   <?php
   if ( function_exists( 'amst' ) ) {
       echo amst()->language_detector->get_language_switcher();
   }
   ?>
   ```
3. Save

## Step 7: Optional Integrations

### LiteSpeed Cache

If you're using LiteSpeed Cache:

1. The plugin automatically integrates
2. Translations are cached with language-specific variants
3. Cache is automatically purged when translations are cleared

### Elementor

If you're using Elementor:

1. No additional configuration needed
2. Elementor content is automatically translated
3. All widgets are supported

### Directorist

If you're using Directorist:

1. No additional configuration needed
2. Directory listings are automatically translated
3. Listing titles and descriptions are supported

## Troubleshooting Installation

### API Key Not Working

**Problem**: "API connection failed" error

**Solutions**:
1. Verify the API key is correct (copy/paste carefully)
2. Ensure Cloud Translation API is enabled in Google Cloud
3. Check API key restrictions aren't blocking your domain
4. Verify billing is enabled for your Google Cloud project

### Translations Not Showing

**Problem**: Content appears in original language

**Solutions**:
1. Verify the language is enabled in **Languages** settings
2. Check you're using the correct URL format (e.g., `/es/page`)
3. Clear translation cache in **Settings**
4. Check WordPress cache (if using caching plugin)
5. Try incognito/private browser window

### Database Error on Activation

**Problem**: Error creating database tables

**Solutions**:
1. Check database user has CREATE TABLE permissions
2. Verify WordPress can access the database
3. Try deactivating and reactivating the plugin
4. Check WordPress debug log for specific error

### High API Costs

**Problem**: Unexpected Google Cloud bills

**Solutions**:
1. Ensure translation cache is enabled
2. Increase cache expiry time
3. Reduce number of enabled languages
4. Monitor usage in Google Cloud Console
5. Set up billing alerts in Google Cloud

## Next Steps

After successful installation:

1. **Monitor Statistics**: Regularly check translation statistics
2. **Optimize Cache**: Adjust cache settings based on your needs
3. **Test SEO**: Use Google Search Console to verify hreflang tags
4. **User Feedback**: Gather feedback on translation quality
5. **Fine-tune Languages**: Add or remove languages based on traffic

## Support

If you encounter issues during installation:

1. Check this documentation thoroughly
2. Review the main README.md file
3. Search existing GitHub issues
4. Create a new issue with detailed information about your problem

## Security Considerations

After installation:

1. Keep your API key secure
2. Use API key restrictions in Google Cloud
3. Enable HTTPS on your website
4. Keep WordPress and plugins updated
5. Use strong WordPress admin passwords

## Performance Optimization

To ensure optimal performance:

1. Enable object caching (Redis/Memcached)
2. Use a CDN for static assets
3. Enable translation cache
4. Use a caching plugin (LiteSpeed recommended)
5. Monitor database performance

---

Congratulations! Your Auto Multilingual SEO Translator is now installed and configured.
