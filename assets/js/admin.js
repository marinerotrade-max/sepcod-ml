/**
 * Admin JavaScript for Auto Multilingual SEO Translator.
 *
 * @package AutoMultilingualSEO
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        /**
         * Test API Connection
         */
        $('#amst-test-api').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $status = $('.amst-api-status');
            
            // Disable button
            $button.prop('disabled', true);
            
            // Show loading state
            $status
                .removeClass('success error')
                .addClass('loading')
                .text(amstAdmin.strings.testingApi);
            
            // Make AJAX request
            $.ajax({
                url: amstAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'amst_test_api',
                    nonce: amstAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status
                            .removeClass('loading error')
                            .addClass('success')
                            .text(amstAdmin.strings.apiSuccess);
                        
                        if (response.data.translation) {
                            $status.append(' (' + response.data.translation + ')');
                        }
                    } else {
                        $status
                            .removeClass('loading success')
                            .addClass('error')
                            .text(amstAdmin.strings.apiError + ': ' + response.data.message);
                    }
                },
                error: function() {
                    $status
                        .removeClass('loading success')
                        .addClass('error')
                        .text(amstAdmin.strings.apiError);
                },
                complete: function() {
                    $button.prop('disabled', false);
                    
                    // Clear status after 5 seconds
                    setTimeout(function() {
                        $status.fadeOut(function() {
                            $status.removeClass('success error loading').text('').show();
                        });
                    }, 5000);
                }
            });
        });
        
        /**
         * Clear Cache
         */
        $('#amst-clear-cache').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear all translation cache?')) {
                return;
            }
            
            var $button = $(this);
            var $status = $('.amst-cache-status');
            
            // Disable button
            $button.prop('disabled', true);
            
            // Show loading state
            $status
                .removeClass('success error')
                .addClass('loading')
                .text(amstAdmin.strings.clearingCache);
            
            // Make AJAX request
            $.ajax({
                url: amstAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'amst_clear_cache',
                    nonce: amstAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status
                            .removeClass('loading error')
                            .addClass('success')
                            .text(amstAdmin.strings.cacheCleared);
                        
                        // Reload page after 2 seconds if on statistics page
                        if ($('.amst-statistics-wrap').length) {
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    } else {
                        $status
                            .removeClass('loading success')
                            .addClass('error')
                            .text('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    $status
                        .removeClass('loading success')
                        .addClass('error')
                        .text('Error clearing cache');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    
                    // Clear status after 5 seconds
                    setTimeout(function() {
                        $status.fadeOut(function() {
                            $status.removeClass('success error loading').text('').show();
                        });
                    }, 5000);
                }
            });
        });
        
        /**
         * Language Item Click Handler
         */
        $('.amst-language-item').on('click', function(e) {
            if ($(e.target).is('input[type="checkbox"]')) {
                return;
            }
            
            var $checkbox = $(this).find('input[type="checkbox"]');
            
            if (!$checkbox.prop('disabled')) {
                $checkbox.prop('checked', !$checkbox.prop('checked'));
                $(this).toggleClass('active', $checkbox.prop('checked'));
            }
        });
        
        /**
         * Initialize active state for language items
         */
        $('.amst-language-item input[type="checkbox"]').each(function() {
            var $item = $(this).closest('.amst-language-item');
            $item.toggleClass('active', $(this).prop('checked'));
        });
        
        /**
         * Language checkbox change handler
         */
        $('.amst-language-item input[type="checkbox"]').on('change', function() {
            var $item = $(this).closest('.amst-language-item');
            $item.toggleClass('active', $(this).prop('checked'));
        });
        
    });
    
})(jQuery);
