/**
 * Language Switcher JavaScript - SIMPLIFIED VERSION
 * Simple dropdown only, no modals
 */

(function($) {
    'use strict';
    
    // Main initialization
    $(document).ready(function() {
        // Simple dropdown - uses native browser select element
        // Navigation handled by inline onchange event
        // No complex JavaScript needed!
        
        // Optional: Style the select dropdown
        $('.amst-language-dropdown').css({
            'padding': '8px 12px',
            'font-size': '16px',
            'border': '1px solid #ddd',
            'border-radius': '4px',
            'background': 'white',
            'cursor': 'pointer'
        });
    });
    
})(jQuery);
