/**
 * Auto Multilingual SEO Translator - Admin JavaScript
 */

(function($) {
    'use strict';
    
    /**
     * Admin functionality
     */
    const AMST_Admin = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Clear cache button
            $('.amst-clear-cache').on('click', this.clearCache);
            
            // Test API connection
            $('.amst-test-api').on('click', this.testApiConnection);
            
            // Form validation
            $('form[action="options.php"]').on('submit', this.validateForm);
        },
        
        /**
         * Initialize tabs
         */
        initTabs: function() {
            $('.amst-tab-nav a').on('click', function(e) {
                e.preventDefault();
                
                const target = $(this).attr('href');
                
                // Update nav active state
                $('.amst-tab-nav a').removeClass('active');
                $(this).addClass('active');
                
                // Update content active state
                $('.amst-tab-content').removeClass('active');
                $(target).addClass('active');
            });
        },
        
        /**
         * Clear cache
         */
        clearCache: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear all translation cache?')) {
                return;
            }
            
            const $button = $(this);
            $button.prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'amst_clear_cache',
                    nonce: $button.data('nonce')
                },
                success: function(response) {
                    if (response.success) {
                        alert('Cache cleared successfully!');
                    } else {
                        alert('Failed to clear cache.');
                    }
                    $button.prop('disabled', false).text('Clear Cache');
                },
                error: function() {
                    alert('An error occurred.');
                    $button.prop('disabled', false).text('Clear Cache');
                }
            });
        },
        
        /**
         * Test API connection
         */
        testApiConnection: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const apiKey = $('input[name="amst_options[api_key]"]').val();
            
            if (!apiKey) {
                alert('Please enter an API key first.');
                return;
            }
            
            $button.prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'amst_test_api',
                    nonce: $button.data('nonce'),
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        alert('API connection successful!');
                    } else {
                        alert('API connection failed: ' + response.data.message);
                    }
                    $button.prop('disabled', false).text('Test API Connection');
                },
                error: function() {
                    alert('An error occurred while testing the connection.');
                    $button.prop('disabled', false).text('Test API Connection');
                }
            });
        },
        
        /**
         * Validate form
         */
        validateForm: function(e) {
            const apiKey = $('input[name="amst_options[api_key]"]').val();
            
            if (!apiKey || apiKey.trim() === '') {
                alert('Please enter a Google Translate API key.');
                e.preventDefault();
                return false;
            }
            
            return true;
        },
        
        /**
         * Show notification
         */
        showNotification: function(message, type) {
            const $notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($notification);
            
            setTimeout(function() {
                $notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        AMST_Admin.init();
    });
    
})(jQuery);
