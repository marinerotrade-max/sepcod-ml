/**
 * Language Switcher JavaScript - Enhanced with Cookie Support
 * Works even with aggressive caching (LiteSpeed, server-side cache)
 */

(function($) {
    'use strict';
    
    // REMOVED v1.4.9: Cookie setting functions no longer needed
    // PHP handles all cookie operations server-side via ?set_lang parameter
    
    // FIXED: Extract language from URL - with validation against known languages
    function getLangFromUrl() {
        var path = window.location.pathname;
        var segments = path.split('/').filter(function(s) { return s.length > 0; });
        
        // Known EU language codes (24 languages)
        var knownLangs = ['bg', 'hr', 'cs', 'da', 'nl', 'en', 'et', 'fi', 'fr', 'de', 
                          'el', 'hu', 'ga', 'it', 'lv', 'lt', 'mt', 'pl', 'pt', 'ro', 
                          'sk', 'sl', 'es', 'sv'];
        
        // Check if first segment is a valid 2-letter language code
        if (segments.length > 0 && segments[0].length === 2 && knownLangs.indexOf(segments[0]) !== -1) {
            console.log('Language from URL: ' + segments[0]); // DEBUGGING
            return segments[0];
        }
        console.log('No language in URL, defaulting to: en'); // DEBUGGING
        return 'en'; // Default language
    }
    
    // CRITICAL FIX: Remove ALL language prefixes from path (prevents /fr/de/ stacking)
    function removeAllLanguagePrefixes(path) {
        var knownLangs = ['bg', 'hr', 'cs', 'da', 'nl', 'en', 'et', 'fi', 'fr', 'de', 
                          'el', 'hu', 'ga', 'it', 'lv', 'lt', 'mt', 'pl', 'pt', 'ro', 
                          'sk', 'sl', 'es', 'sv'];
        
        var segments = path.split('/').filter(function(s) { return s.length > 0; });
        
        // Keep removing first segment while it's a language code
        while (segments.length > 0 && segments[0].length === 2 && knownLangs.indexOf(segments[0]) !== -1) {
            segments.shift();
        }
        
        // Return cleaned path
        return segments.length > 0 ? '/' + segments.join('/') : '';
    }
    
    // REMOVED v1.4.9: Cookie deletion no longer needed - PHP handles everything
    
    // INITIALIZATION: Set active language based on URL (URL has priority over cookie)
    function initializeLanguageSwitcher() {
        // URL PRIORITY: Always check URL first, not cookie
        var currentLang = getLangFromUrl();
        
        // Update dropdown to show current language and mark as active
        var knownLangs = ['bg', 'hr', 'cs', 'da', 'nl', 'en', 'et', 'fi', 'fr', 'de', 
                          'el', 'hu', 'ga', 'it', 'lv', 'lt', 'mt', 'pl', 'pt', 'ro', 
                          'sk', 'sl', 'es', 'sv'];
        
        $('.amst-language-dropdown option').each(function() {
            var optUrl = $(this).val();
            var optPath = optUrl.replace(window.location.origin, '');
            var optSegments = optPath.split('/').filter(function(s) { return s.length > 0; });
            var optLang = (optSegments.length > 0 && optSegments[0].length === 2 && knownLangs.indexOf(optSegments[0]) !== -1) ? optSegments[0] : 'en';
            
            if (optLang === currentLang) {
                $(this).prop('selected', true);
                $(this).addClass('active'); // Mark current language as active
            } else {
                $(this).removeClass('active');
                $(this).prop('selected', false); // Ensure others are not selected
            }
        });
        
        // If using i18next, sync it with current language from URL
        // FIXED v1.4.9: Never fall back to 'de-DE' - always use URL language
        if (typeof i18next !== 'undefined' && i18next.language !== currentLang) {
            console.log('i18next detected, changing language to: ' + currentLang); // DEBUGGING
            i18next.changeLanguage(currentLang);
        }
    }
    
    // Main initialization
    $(document).ready(function() {
        // Style the select dropdown
        $('.amst-language-dropdown').css({
            'padding': '8px 12px',
            'font-size': '16px',
            'border': '1px solid #ddd',
            'border-radius': '4px',
            'background': 'white',
            'cursor': 'pointer'
        });
        
        // Initialize language switcher on page load
        initializeLanguageSwitcher();
        
        // CLICK EVENT: PHP-BASED cookie setting via ?set_lang parameter
        // NEW v1.4.8: JavaScript NO LONGER sets cookie - PHP handles it server-side
        $('.amst-language-dropdown').on('change', function() {
            var targetUrl = $(this).val();
            if (targetUrl) {
                // Extract language code from target URL
                var urlPath = targetUrl.replace(window.location.origin, '');
                var segments = urlPath.split('/').filter(function(s) { return s.length > 0; });
                var targetLang = 'en'; // default
                
                var knownLangs = ['bg', 'hr', 'cs', 'da', 'nl', 'en', 'et', 'fi', 'fr', 'de', 
                                  'el', 'hu', 'ga', 'it', 'lv', 'lt', 'mt', 'pl', 'pt', 'ro', 
                                  'sk', 'sl', 'es', 'sv'];
                
                // Check if first segment is a valid 2-letter language code
                if (segments.length > 0 && segments[0].length === 2 && knownLangs.indexOf(segments[0]) !== -1) {
                    targetLang = segments[0];
                }
                
                // Build clean URL from current page (remove ALL language prefixes)
                // This ensures we never have stacked language codes like /fr/de/
                var currentPath = window.location.pathname;
                var cleanPath = removeAllLanguagePrefixes(currentPath);
                
                // Build new URL with target language prefix
                var newUrl = window.location.origin;
                if (targetLang !== 'en') {
                    newUrl += '/' + targetLang;
                }
                newUrl += cleanPath;
                
                // Ensure trailing slash for consistency if no query string
                if (!newUrl.endsWith('/') && newUrl.indexOf('?') === -1 && cleanPath !== '') {
                    newUrl += '/';
                }
                
                // CRITICAL FIX: Add ?set_lang=XX parameter to trigger PHP cookie setting
                // PHP will set cookie server-side with proper security flags, then redirect to clean URL
                var separator = newUrl.indexOf('?') > -1 ? '&' : '?';
                newUrl += separator + 'set_lang=' + targetLang;
                
                console.log('Redirecting to PHP cookie setter: ' + newUrl); // DEBUGGING
                
                // If using i18next, mark that language change is pending
                if (typeof i18next !== 'undefined') {
                    console.log('i18next: Language change to ' + targetLang + ' will be handled after PHP redirect'); // DEBUGGING
                }
                
                // REDIRECT: Let PHP handle cookie setting, then PHP will redirect to clean URL
                window.location.href = newUrl;
            }
        });
    });
    
})(jQuery);
