/**
 * Language Switcher JavaScript - Enhanced with Cookie Support
 * Works even with aggressive caching (LiteSpeed, server-side cache)
 */

(function($) {
    'use strict';
    
    // Cookie helper functions
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
    }
    
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
    
    // Extract language from URL - with validation against known languages
    function getLangFromUrl() {
        var path = window.location.pathname;
        var segments = path.split('/').filter(function(s) { return s.length > 0; });
        
        // Known EU language codes (24 languages)
        var knownLangs = ['bg', 'hr', 'cs', 'da', 'nl', 'en', 'et', 'fi', 'fr', 'de', 
                          'el', 'hu', 'ga', 'it', 'lv', 'lt', 'mt', 'pl', 'pt', 'ro', 
                          'sk', 'sl', 'es', 'sv'];
        
        // Check if first segment is a valid 2-letter language code
        if (segments.length > 0 && segments[0].length === 2 && knownLangs.indexOf(segments[0]) !== -1) {
            return segments[0];
        }
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
        
        // Enhanced language switching with STRICT URL cleaning
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
                
                // ALTERNATIVE METHOD: Build clean URL from current page
                // This ensures we never have stacked language codes like /fr/de/
                var currentPath = window.location.pathname;
                var cleanPath = removeAllLanguagePrefixes(currentPath);
                
                // Build new URL with only the target language
                var newUrl = window.location.origin;
                if (targetLang !== 'en') {
                    newUrl += '/' + targetLang;
                }
                newUrl += cleanPath;
                
                // Ensure trailing slash for consistency
                if (!newUrl.endsWith('/') && newUrl.indexOf('?') === -1) {
                    newUrl += '/';
                }
                
                // FORCE cookie update with path=/ to overwrite any existing cookie
                document.cookie = 'amst_language=' + targetLang + '; expires=' + 
                    (new Date(Date.now() + 30*24*60*60*1000)).toUTCString() + 
                    '; path=/; SameSite=Lax';
                
                // Navigate to clean URL - prevents stacking
                window.location.href = newUrl;
            }
        });
        
        // On page load: Check if cookie language matches URL language
        // This handles cache-bypass scenarios
        var currentLang = getLangFromUrl();
        var cookieLang = getCookie('amst_language');
        
        // Update dropdown to reflect current language AND add active class
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
                $(this).addClass('active'); // Add active class for current language
            } else {
                $(this).removeClass('active');
            }
        });
        
        // Store current language in cookie if not already set
        if (!cookieLang || cookieLang !== currentLang) {
            setCookie('amst_language', currentLang, 30);
        }
    });
    
})(jQuery);
