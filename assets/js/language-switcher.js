/**
 * Language Switcher JavaScript - COMPLETELY REWRITTEN FOR STABILITY
 * Simple, robust approach using direct DOM manipulation
 */

(function($) {
    'use strict';
    
    // Cookie utilities
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
    
    // Main initialization
    $(document).ready(function() {
        var hasSeenModal = getCookie('amst_language_selected') === 'yes';
        
        // If user already interacted, hide modal completely
        if (hasSeenModal) {
            $('.amst-modal-overlay').each(function() {
                $(this).remove(); // Don't just hide - REMOVE it
            });
        }
        
        // Setup all event handlers
        setupModalHandlers();
        setupDropdownHandlers();
        setupLanguageSelection();
        applyFixedPosition();
    });
    
    // Modal handlers
    function setupModalHandlers() {
        // Open modal on button click
        $(document).on('click', '.amst-modal-style .amst-current-language', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $overlay = $(this).siblings('.amst-modal-overlay');
            $overlay.fadeIn(300);
            $('body').css('overflow', 'hidden');
        });
        
        // Close button - uses event delegation for reliability
        $(document).on('click', '.amst-modal-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
            return false;
        });
        
        // Click outside modal to close
        $(document).on('click', '.amst-modal-overlay', function(e) {
            if ($(e.target).hasClass('amst-modal-overlay')) {
                closeModal();
            }
        });
        
        // ESC key to close
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('.amst-modal-overlay:visible').length) {
                closeModal();
            }
        });
    }
    
    // Dropdown handlers
    function setupDropdownHandlers() {
        $(document).on('click', '.amst-dropdown-style .amst-current-language', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $dropdown = $(this).siblings('.amst-dropdown-menu');
            $dropdown.slideToggle(200);
            $(this).toggleClass('active');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.amst-dropdown-style').length) {
                $('.amst-dropdown-menu').slideUp(200);
                $('.amst-dropdown-style .amst-current-language').removeClass('active');
            }
        });
    }
    
    // Language selection handler
    function setupLanguageSelection() {
        $(document).on('click', '.amst-language-option', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var href = $(this).attr('href');
            
            // Set cookie immediately
            setCookie('amst_language_selected', 'yes', 365);
            
            // Hide everything immediately
            $('.amst-modal-overlay').hide().remove();
            $('.amst-dropdown-menu').hide();
            $('body').css('overflow', '');
            
            // Navigate
            window.location.href = href;
            
            return false;
        });
    }
    
    // Close modal function
    function closeModal() {
        // Set cookie so it never shows again
        setCookie('amst_language_selected', 'yes', 365);
        
        // Remove modal from DOM completely
        $('.amst-modal-overlay').fadeOut(200, function() {
            $(this).remove();
        });
        
        // Restore body scroll
        $('body').css('overflow', '');
        
        // Remove active states
        $('.amst-current-language').removeClass('active');
    }
    
    // Apply fixed positioning
    function applyFixedPosition() {
        if (typeof amstSwitcher !== 'undefined' && amstSwitcher.position && amstSwitcher.position !== 'inline') {
            $('.amst-language-switcher').addClass('amst-fixed').addClass(amstSwitcher.position);
        }
    }
    
})(jQuery);
