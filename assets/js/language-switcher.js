/**
 * Language Switcher JavaScript
 * Handles modal popup and dropdown interactions with proper cookie management
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
    
    $(document).ready(function() {
        // Check if user has already interacted with language switcher
        if (getCookie('amst_language_selected') === 'yes') {
            // User has already made a choice, don't show modal automatically
            $('.amst-modal-overlay').hide();
        }
        
        initLanguageSwitcher();
    });
    
    function initLanguageSwitcher() {
        // Modal Style Switcher
        $('.amst-modal-style .amst-current-language').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $switcher = $(this).closest('.amst-language-switcher');
            var $overlay = $switcher.find('.amst-modal-overlay');
            
            $(this).toggleClass('active');
            $overlay.fadeToggle(300);
            
            // Prevent body scroll when modal is open
            if ($overlay.is(':visible')) {
                $('body').css('overflow', 'hidden');
            }
        });
        
        // Close modal on overlay click
        $('.amst-modal-overlay').on('click', function(e) {
            if ($(e.target).hasClass('amst-modal-overlay')) {
                closeModalPermanently($(this));
            }
        });
        
        // Close modal on close button click - FIXED to work properly
        $('.amst-modal-close').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Close button clicked'); // Debug log
            closeModalPermanently($(this).closest('.amst-modal-overlay'));
            return false;
        });
        
        // Close modal on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('.amst-modal-overlay:visible').length) {
                closeModalPermanently($('.amst-modal-overlay:visible'));
            }
        });
        
        // Dropdown Style Switcher
        $('.amst-dropdown-style .amst-current-language').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $switcher = $(this).closest('.amst-language-switcher');
            var $dropdown = $switcher.find('.amst-dropdown-menu');
            
            $(this).toggleClass('active');
            $dropdown.slideToggle(200);
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.amst-dropdown-style').length) {
                $('.amst-dropdown-style .amst-dropdown-menu').slideUp(200);
                $('.amst-dropdown-style .amst-current-language').removeClass('active');
            }
        });
        
        // Language option click handler
        $('.amst-language-option').on('click', function(e) {
            var $this = $(this);
            var href = $this.attr('href');
            
            // Prevent default to handle closing manually
            e.preventDefault();
            e.stopPropagation();
            
            // Set cookie to remember user made a language selection
            setCookie('amst_language_selected', 'yes', 365); // Remember for 1 year
            
            // Show loading state
            $this.closest('.amst-language-switcher').addClass('loading');
            
            // Close modal COMPLETELY before navigation
            var $switcher = $this.closest('.amst-language-switcher');
            var $overlay = $switcher.find('.amst-modal-overlay');
            
            // Forcefully hide and remove all modal elements
            if ($overlay.length) {
                $overlay.hide();
                $overlay.css({'display': 'none', 'visibility': 'hidden', 'opacity': '0', 'pointer-events': 'none'});
                $overlay.remove(); // Completely remove modal from DOM
            }
            
            // Close dropdown if present
            var $dropdown = $switcher.find('.amst-dropdown-menu');
            if ($dropdown.length) {
                $dropdown.hide();
                $dropdown.css('display', 'none');
            }
            
            // Remove all active classes
            $switcher.find('.amst-current-language').removeClass('active');
            $('.amst-language-switcher').removeClass('active');
            
            // Restore body scroll
            $('body').css('overflow', '').css('overflow-y', '');
            
            // Remove any lingering modals from entire page
            $('.amst-modal-overlay').remove();
            
            // Navigate immediately - no delay needed since we removed the modal from DOM
            window.location.href = href;
        });
        
        // Keyboard navigation for modal
        $('.amst-modal-overlay').on('keydown', '.amst-language-option', function(e) {
            var $options = $('.amst-language-option');
            var currentIndex = $options.index(this);
            
            switch(e.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    var nextIndex = (currentIndex + 1) % $options.length;
                    $options.eq(nextIndex).focus();
                    break;
                    
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    var prevIndex = (currentIndex - 1 + $options.length) % $options.length;
                    $options.eq(prevIndex).focus();
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    $options.first().focus();
                    break;
                    
                case 'End':
                    e.preventDefault();
                    $options.last().focus();
                    break;
            }
        });
        
        // Apply fixed position if set in options
        applyFixedPosition();
    }
    
    // Close modal and save cookie to prevent reappearance
    function closeModalPermanently($overlay) {
        if ($overlay && $overlay.length) {
            console.log('Closing modal permanently'); // Debug log
            
            // Set cookie so modal doesn't appear again
            setCookie('amst_language_selected', 'yes', 365); // Remember for 1 year
            
            // Immediately hide and remove the modal
            $overlay.hide();
            $overlay.css({'display': 'none', 'visibility': 'hidden', 'opacity': '0', 'pointer-events': 'none'});
            
            var $switcher = $overlay.closest('.amst-language-switcher');
            $switcher.find('.amst-current-language').removeClass('active');
            $('body').css('overflow', '');
            
            // Completely remove from DOM after a brief moment
            setTimeout(function() {
                $overlay.remove();
            }, 100);
        }
    }
    
    function applyFixedPosition() {
        if (typeof amstSwitcher !== 'undefined' && amstSwitcher.position && amstSwitcher.position !== 'inline') {
            $('.amst-language-switcher').addClass('amst-fixed').addClass(amstSwitcher.position);
        }
    }
    
})(jQuery);
