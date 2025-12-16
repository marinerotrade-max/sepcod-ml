# Quick Start Guide

Get your multilingual WordPress site running in 5 minutes!

## 1. Get Google Translate API Key (2 minutes)

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a project
3. Enable "Cloud Translation API"
4. Create an API key under Credentials
5. Copy the API key

## 2. Install Plugin (1 minute)

**Via WordPress Admin:**
```
Plugins > Add New > Upload Plugin > Choose ZIP file > Install > Activate
```

**Via FTP:**
```
Upload folder to: /wp-content/plugins/auto-multilingual-seo-translator/
Then activate in WordPress admin
```

## 3. Configure Settings (2 minutes)

1. Go to **Settings > Multilingual SEO**
2. Paste your API key
3. Select default language (e.g., English)
4. Check languages you want to support
5. Keep "Enable Caching" checked (recommended)
6. Keep "Enable Auto Detection" checked (recommended)
7. Click **Save Changes**

## 4. Add Language Switcher

**Quick Widget Method:**
```
Appearance > Widgets > Drag "Language Switcher" to sidebar
```

**Quick Shortcode Method:**
Add to any page/post:
```
[language_switcher style="dropdown"]
```

**Quick PHP Method:**
Add to your theme's header.php or footer.php:
```php
<?php echo do_shortcode('[language_switcher]'); ?>
```

## 5. Test It!

1. Visit your website
2. You should see the language switcher
3. Select a different language
4. Content should translate automatically
5. Done! 🎉

## Common Use Cases

### For Blogs
- Add language switcher to sidebar
- All posts and pages translate automatically
- SEO tags are generated automatically

### For Business Sites
- Add language switcher to header/footer
- Product descriptions translate automatically
- Contact forms work in all languages

### For E-Commerce (WooCommerce)
- Translate product titles and descriptions
- Category names translate automatically
- Checkout process in user's language

### For Directories (Directorist)
- Listings translate automatically
- Search filters work in all languages
- Custom fields translate where applicable

## Troubleshooting Quick Fixes

### Not translating?
```
✓ Check API key is correct in settings
✓ Verify API is enabled in Google Cloud Console
✓ Enable billing in Google Cloud (required even for free tier)
✓ Clear cache: Settings > Multilingual SEO
```

### Switcher not showing?
```
✓ Verify multiple languages are enabled in settings
✓ Clear browser cache
✓ Check widget/shortcode is added correctly
```

### Slow performance?
```
✓ Ensure "Enable Caching" is ON (settings)
✓ Install Redis/Memcached for WordPress
✓ Use LiteSpeed Cache if available
✓ Enable CDN for static assets
```

## Quick Commands (WP-CLI)

```bash
# Install
wp plugin install auto-multilingual-seo-translator.zip --activate

# Get settings
wp option get amst_options

# Clear cache
wp cache flush

# Deactivate
wp plugin deactivate auto-multilingual-seo-translator
```

## Quick Customization

### Change switcher colors:
```css
/* Add to your theme's style.css */
.amst-language-select {
    background-color: #your-color;
    border-color: #your-border-color;
}
```

### Change active language color:
```css
.amst-language-item.active .amst-language-link {
    background-color: #your-active-color;
}
```

## Performance Checklist

- ✓ Enable caching in plugin settings
- ✓ Use LiteSpeed Cache (if available)
- ✓ Install Redis or Memcached
- ✓ Enable WordPress object cache
- ✓ Use CDN for assets
- ✓ Only enable languages you need

## Security Checklist

- ✓ Restrict API key in Google Cloud Console
- ✓ Use HTTPS on your site
- ✓ Keep WordPress and plugins updated
- ✓ Use Wordfence (fully compatible)
- ✓ Monitor API usage regularly

## Next Steps

1. **Read full documentation:** [README.md](README.md)
2. **Optimize SEO:** Install Rank Math (automatically compatible)
3. **Customize design:** Edit CSS in your theme
4. **Monitor usage:** Check Google Cloud Console regularly
5. **Get support:** [GitHub Issues](https://github.com/marinerotrade-max/sepcod-ml/issues)

## Pro Tips

💡 **Use caching** - Reduces API calls by 90%+
💡 **Auto-detect language** - Better user experience
💡 **Limit languages** - Only enable what you need
💡 **Monitor API quota** - Set up billing alerts
💡 **Test thoroughly** - Try different languages and content types

## Need Help?

- 📖 **Full Guide:** [INSTALLATION.md](INSTALLATION.md)
- 🐛 **Report Bugs:** [GitHub Issues](https://github.com/marinerotrade-max/sepcod-ml/issues)
- 💬 **Questions:** Open a discussion on GitHub
- 📚 **Examples:** Check [README.md](README.md) for code examples

---

**That's it!** Your WordPress site is now multilingual. 🌍✨
