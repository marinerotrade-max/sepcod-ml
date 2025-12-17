# Contributing to Auto Multilingual SEO Translator

Thank you for your interest in contributing to the Auto Multilingual SEO Translator plugin! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title and description**
- **Steps to reproduce** the issue
- **Expected behavior** vs actual behavior
- **WordPress version**
- **PHP version**
- **Plugin version**
- **Active theme and plugins**
- **Error messages** (if any)
- **Screenshots** (if applicable)

### Suggesting Enhancements

Enhancement suggestions are welcome! Please provide:

- **Clear title and description**
- **Use case** and benefit
- **Proposed implementation** (if you have ideas)
- **Alternative solutions** you've considered
- **Impact on existing functionality**

### Pull Requests

#### Before Submitting

1. Check if a similar PR already exists
2. Discuss major changes in an issue first
3. Follow the coding standards
4. Write clear commit messages
5. Update documentation if needed

#### Development Setup

1. **Fork and Clone**
```bash
git clone https://github.com/YOUR-USERNAME/sepcod-ml.git
cd sepcod-ml/auto-multilingual-seo-translator
```

2. **Install Dependencies**
```bash
composer install
```

3. **Create a Branch**
```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

#### Coding Standards

**PHP Code:**
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use PHP 8.0+ features appropriately
- Include PHPDoc blocks for all functions and classes
- Use type hints where applicable
- Maintain singleton pattern for main classes

**Example:**
```php
/**
 * Translate text from source to target language
 * 
 * @param string $text Text to translate
 * @param string $source_lang Source language code
 * @param string $target_lang Target language code
 * @return string Translated text
 */
public function translate(string $text, string $source_lang, string $target_lang): string {
    // Implementation
}
```

**JavaScript Code:**
- Follow WordPress JavaScript standards
- Use ES5 for broad compatibility
- Use jQuery for DOM manipulation
- Comment complex logic
- Use strict mode

**CSS Code:**
- Follow WordPress CSS standards
- Use BEM naming convention where appropriate
- Include vendor prefixes where needed
- Keep mobile-first approach
- Comment sections

#### File Structure

```
auto-multilingual-seo-translator/
├── includes/          # Core PHP classes
├── admin/            # Admin-specific files
├── public/           # Public-facing files
├── assets/
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript files
│   └── images/      # Images and icons
└── languages/       # Translation files
```

#### Naming Conventions

- **Classes:** `class-name.php` → `Class_Name`
- **Functions:** `amst_function_name()`
- **Hooks:** `amst_hook_name`
- **CSS classes:** `.amst-class-name`
- **JavaScript:** `AMST_ObjectName`

#### Testing

Before submitting:

1. **Test PHP Syntax**
```bash
php -l auto-multilingual-seo-translator.php
find includes -name "*.php" -exec php -l {} \;
```

2. **Test in WordPress**
- Install in a WordPress test environment
- Test with WordPress 6.0+
- Test with PHP 8.0+
- Test with common plugins (Elementor, Rank Math, etc.)
- Test language switching
- Test translation functionality
- Test caching

3. **Browser Testing**
- Test in Chrome, Firefox, Safari, Edge
- Test responsive design
- Check console for JavaScript errors
- Test accessibility features

#### Commit Messages

Write clear commit messages:

```
Short summary (50 chars or less)

Detailed explanation if needed. Wrap at 72 characters.
Include the reasoning behind changes, not just what changed.

- Bullet points for multiple changes
- Reference issues: Fixes #123
- Reference PRs: Related to #456
```

**Examples:**
```
Add language detection from IP address

Implements geolocation-based language detection as an alternative
to browser-based detection. Falls back gracefully if service unavailable.

Fixes #42
```

#### Documentation

Update documentation for:
- New features
- Changed functionality
- New configuration options
- New hooks or filters

Files to update:
- `README.md` - Feature overview
- `INSTALLATION.md` - Setup instructions
- `CHANGELOG.md` - Version history
- Code comments - Inline documentation

#### Security

**Never commit:**
- API keys or credentials
- Personal information
- Production database dumps
- Sensitive configuration

**Always:**
- Sanitize input
- Escape output
- Use nonces for forms
- Validate and authorize
- Follow WordPress security best practices

#### Pull Request Process

1. **Update your fork**
```bash
git fetch upstream
git rebase upstream/main
```

2. **Push to your fork**
```bash
git push origin feature/your-feature-name
```

3. **Create Pull Request**
- Use a clear title
- Describe changes in detail
- Link related issues
- Add screenshots if UI changes
- Check all checkboxes in PR template

4. **Code Review**
- Address review comments
- Push additional commits if needed
- Request re-review after changes

5. **Merge**
- Maintainer will merge when approved
- Delete your branch after merge

## Development Guidelines

### Adding New Features

1. **Plan the feature**
   - Create an issue for discussion
   - Get feedback from maintainers
   - Design the implementation

2. **Implement**
   - Create a new class if needed
   - Add hooks and filters
   - Follow singleton pattern
   - Add to main plugin initialization

3. **Document**
   - Add PHPDoc comments
   - Update README
   - Add usage examples
   - Update CHANGELOG

4. **Test**
   - Test thoroughly
   - Test with related plugins
   - Test edge cases

### Adding Integrations

When adding a new plugin integration:

1. Check if plugin is active before initializing
2. Create a separate class in `includes/`
3. Use plugin's hooks and filters
4. Handle gracefully if plugin is deactivated
5. Document compatibility in README

Example:
```php
// In main plugin file
if (class_exists('Plugin_Class')) {
    AMST_Plugin_Integration::get_instance();
}

// In integration class
class AMST_Plugin_Integration {
    private function __construct() {
        add_filter('plugin_filter', [$this, 'method']);
    }
}
```

### Performance Considerations

- Cache expensive operations
- Minimize database queries
- Use transients appropriately
- Lazy load when possible
- Consider mobile devices
- Monitor API usage

### Accessibility

- Use semantic HTML
- Include ARIA labels
- Ensure keyboard navigation
- Test with screen readers
- Maintain color contrast
- Support RTL languages

## Questions?

If you have questions:
- Open an issue for discussion
- Check existing issues and PRs
- Review documentation

## License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later license.

Thank you for contributing! 🎉
