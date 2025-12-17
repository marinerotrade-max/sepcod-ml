/**
 * Language Switcher JavaScript
 * Handles modal popup and dropdown interactions
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
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
                closeModal($(this));
            }
        });
        
        // Close modal on close button click
        $('.amst-modal-close').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModal($(this).closest('.amst-modal-overlay'));
        });
        
        // Close modal on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('.amst-modal-overlay:visible').length) {
                closeModal($('.amst-modal-overlay:visible'));
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
            // Show loading state
            $(this).closest('.amst-language-switcher').addClass('loading');
            
            // Allow default behavior (link navigation)
            // The page will reload with the new language
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
    
    function closeModal($overlay) {
        if ($overlay && $overlay.length) {
            $overlay.fadeOut(300, function() {
                var $switcher = $overlay.closest('.amst-language-switcher');
                $switcher.find('.amst-current-language').removeClass('active');
                $('body').css('overflow', '');
            });
        }
    }
    
    function applyFixedPosition() {
        if (typeof amstSwitcher !== 'undefined' && amstSwitcher.position && amstSwitcher.position !== 'inline') {
            $('.amst-language-switcher').addClass('amst-fixed').addClass(amstSwitcher.position);
        }
    }
    
})(jQuery);
