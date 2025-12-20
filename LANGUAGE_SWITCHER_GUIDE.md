# Language Switcher Guide

## Overview

The Auto Multilingual SEO Translator plugin includes an enhanced language switcher with flag icons, modal popup interface, and full customization options.

## Features

### Flag Icons
- **All 24 EU languages** have emoji flag icons
- **No external dependencies** - flags work immediately without downloading images
- **Three sizes available**: Small (18px), Medium (24px), Large (32px)

### Display Styles

#### 1. Modal Popup (Recommended)
- Click current language flag to open centered modal
- Grid layout showing all available languages
- Professional design with smooth animations
- Click any flag to switch languages
- Close with X button or click outside modal

#### 2. Dropdown Menu
- Click current language to reveal dropdown
- Compact list of other languages
- Ideal for headers/navigation bars

#### 3. Inline List
- Shows all languages in a horizontal row
- Simple and space-efficient
- Good for footers or sidebars

## Installation & Usage

### Using Shortcode

Add to any page, post, or widget:
```
[amst_language_switcher]
```

With custom attributes:
```
[amst_language_switcher style="modal" show_names="no" flag_size="large"]
```

### Using Template Code

Add to your theme files (header.php, footer.php, etc.):
```php
<?php if ( function_exists( 'amst' ) ) { 
    echo do_shortcode( '[amst_language_switcher]' ); 
} ?>
```

### Using Widget

1. Go to **Appearance → Widgets**
2. Add a **Text** or **HTML** widget
3. Insert the shortcode: `[amst_language_switcher]`

## Configuration

### Admin Settings Page

Navigate to: **WordPress Admin → Multilingual SEO → Language Switcher**

#### Available Options:

**Switcher Style**
- Modal Popup (Recommended) - Centered overlay with all languages
- Dropdown Menu - Compact dropdown from current language
- Inline List - Horizontal row of all languages

**Show Language Names**
- No - Flags Only (Recommended) - Clean, minimal design
- Yes - Flags + Names - Shows language names next to flags

**Flag Size**
- Small (18px) - Compact, space-efficient
- Medium (24px) - Balanced, default size
- Large (32px) - Prominent, easy to click

**Fixed Position**
- Inline (No Fixed Position) - Stays where you place it
- Bottom Right - Floating button in bottom-right corner
- Bottom Left - Floating button in bottom-left corner
- Top Right - Floating button in top-right corner
- Top Left - Floating button in top-left corner

**Modal Title**
- Customize the text shown at the top of the modal popup
- Default: "Select Language"
- Example: "Choose Your Language", "Sprache wählen", etc.

### Preview

The settings page includes a **live preview** showing how the switcher looks with your current settings.

## Shortcode Attributes

Override global settings for specific instances:

| Attribute | Options | Default | Description |
|-----------|---------|---------|-------------|
| `style` | `modal`, `dropdown`, `inline` | `modal` | Display style |
| `show_names` | `yes`, `no` | `no` | Show language names |
| `flag_size` | `small`, `medium`, `large` | `medium` | Flag icon size |

### Examples

Dropdown style with names:
```
[amst_language_switcher style="dropdown" show_names="yes"]
```

Inline list with large flags:
```
[amst_language_switcher style="inline" flag_size="large"]
```

Modal with small flags (flags only):
```
[amst_language_switcher style="modal" flag_size="small" show_names="no"]
```

## Styling & Customization

### Custom CSS

Add custom styles in **Appearance → Customize → Additional CSS**:

```css
/* Change button background color */
.amst-current-language {
    background: #your-color !important;
}

/* Change modal header color */
.amst-modal-header h3 {
    color: #your-color !important;
}

/* Change hover color */
.amst-language-option:hover {
    background: #your-color !important;
}

/* Adjust spacing */
.amst-languages-grid {
    gap: 20px !important;
}
```

### Fixed Position Customization

For fixed position switchers, you can adjust the offset:

```css
.amst-language-switcher.amst-fixed.bottom-right {
    bottom: 30px !important;
    right: 30px !important;
}
```

## Accessibility

The language switcher includes full accessibility support:

- **Keyboard Navigation**: Tab through options, use arrow keys in modal
- **ARIA Labels**: Screen reader friendly
- **Focus States**: Clear visual focus indicators
- **Semantic HTML**: Proper heading structure and roles

### Keyboard Shortcuts (Modal)
- **Tab** - Navigate between languages
- **Arrow Keys** - Move between language options
- **Enter** - Select language
- **Escape** - Close modal
- **Home** - Jump to first language
- **End** - Jump to last language

## Supported Languages

The switcher automatically detects enabled languages from your configuration. All 24 EU official languages are supported:

🇧🇬 Bulgarian | 🇭🇷 Croatian | 🇨🇿 Czech | 🇩🇰 Danish | 🇳🇱 Dutch | 🇬🇧 English
🇪🇪 Estonian | 🇫🇮 Finnish | 🇫🇷 French | 🇩🇪 German | 🇬🇷 Greek | 🇭🇺 Hungarian
🇮🇪 Irish | 🇮🇹 Italian | 🇱🇻 Latvian | 🇱🇹 Lithuanian | 🇲🇹 Maltese | 🇵🇱 Polish
🇵🇹 Portuguese | 🇷🇴 Romanian | 🇸🇰 Slovak | 🇸🇮 Slovenian | 🇪🇸 Spanish | 🇸🇪 Swedish

## Responsive Design

The switcher automatically adapts to different screen sizes:

- **Desktop**: Full-size flags and optional names
- **Tablet**: Slightly smaller layout, optimized touch targets
- **Mobile**: Compact design, larger touch areas, simplified modal

## Troubleshooting

### Switcher Not Appearing
1. Ensure plugin is activated
2. Check that at least one language is enabled in **Multilingual SEO → Languages**
3. Verify shortcode syntax is correct
4. Check theme compatibility - some themes may override styles

### Flags Not Showing
- Flags use Unicode emoji characters
- Ensure your font supports emoji (most modern fonts do)
- Check browser compatibility (all modern browsers support emoji)

### Modal Not Opening
1. Check browser console for JavaScript errors
2. Ensure jQuery is loaded (WordPress loads it by default)
3. Clear browser cache
4. Disable conflicting plugins temporarily to test

### Styles Not Applied
1. Clear WordPress cache (if using caching plugin)
2. Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
3. Check for CSS conflicts with theme
4. Try adding `!important` to custom CSS rules

## Best Practices

### Recommended Settings

For most websites:
- **Style**: Modal Popup
- **Show Names**: No (Flags Only)
- **Flag Size**: Medium
- **Position**: Bottom-right (fixed) or Inline in header

### Placement Suggestions

**Header/Navigation**: Use inline or dropdown style
```php
// In header.php
<?php if ( function_exists( 'amst' ) ) { 
    echo do_shortcode( '[amst_language_switcher style="dropdown"]' ); 
} ?>
```

**Footer**: Use inline style with names
```php
// In footer.php
<?php if ( function_exists( 'amst' ) ) { 
    echo do_shortcode( '[amst_language_switcher style="inline" show_names="yes"]' ); 
} ?>
```

**Floating Button**: Use fixed position with modal
- Set position to "Bottom-right" in admin settings
- Add shortcode to footer: `[amst_language_switcher]`

## Performance

The language switcher is optimized for performance:

- **Lightweight**: Minimal CSS and JavaScript (~15KB total)
- **No External Dependencies**: Uses emoji flags, not images
- **Cached Assets**: CSS/JS files are cached by WordPress
- **Lazy Loading**: Modal content only loads when needed

## Support

For issues or questions:
1. Check this guide first
2. Review plugin documentation in `MANUAL_TRANSLATIONS_GUIDE.md`
3. Check WordPress Admin → Multilingual SEO → Statistics for system info
4. Contact plugin support with error details

## Version History

### Version 1.0.0
- Initial release with flag icons, modal popup, and admin settings
- Support for 24 EU languages
- Three display styles (modal, dropdown, inline)
- Fully responsive and accessible design
- Comprehensive customization options
