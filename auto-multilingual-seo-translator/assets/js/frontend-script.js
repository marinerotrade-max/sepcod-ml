/**
 * Auto Multilingual SEO Translator - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    /**
     * Language Switcher functionality
     */
    const AMST_LanguageSwitcher = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.checkRTL();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Handle dropdown language change
            $(document).on('change', '#amst-language-selector', this.handleDropdownChange);
            
            // Handle list language change
            $(document).on('click', '.amst-language-link', this.handleListClick);
        },
        
        /**
         * Handle dropdown language change
         */
        handleDropdownChange: function(e) {
            const language = $(this).val();
            AMST_LanguageSwitcher.switchLanguage(language);
        },
        
        /**
         * Handle list link click
         */
        handleListClick: function(e) {
            e.preventDefault();
            const language = $(this).data('language');
            AMST_LanguageSwitcher.switchLanguage(language);
        },
        
        /**
         * Switch language
         */
        switchLanguage: function(language) {
            // Show loading state
            $('.amst-language-switcher').addClass('amst-loading');
            
            // Set cookie
            this.setCookie('amst_language', language, 30);
            
            // Reload page
            setTimeout(function() {
                window.location.reload();
            }, 300);
        },
        
        /**
         * Set cookie
         */
        setCookie: function(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
        },
        
        /**
         * Get cookie
         */
        getCookie: function(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        },
        
        /**
         * Check if current language is RTL
         */
        checkRTL: function() {
            const rtlLanguages = ['ar', 'he', 'fa', 'ur'];
            const currentLanguage = this.getCookie('amst_language');
            
            if (currentLanguage && rtlLanguages.indexOf(currentLanguage) !== -1) {
                $('body').addClass('rtl');
                $('html').attr('dir', 'rtl');
            }
        }
    };
    
    /**
     * AJAX Translation Handler
     */
    const AMST_TranslationAjax = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // You can add dynamic content translation here
        },
        
        /**
         * Translate text via AJAX
         */
        translateText: function(text, sourceLang, targetLang, callback) {
            $.ajax({
                url: amstData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'amst_translate_text',
                    nonce: amstData.nonce,
                    text: text,
                    source: sourceLang,
                    target: targetLang
                },
                success: function(response) {
                    if (response.success && callback) {
                        callback(response.data.translation);
                    }
                },
                error: function() {
                    if (callback) {
                        callback(text); // Return original on error
                    }
                }
            });
        }
    };
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        AMST_LanguageSwitcher.init();
        AMST_TranslationAjax.init();
    });
    
})(jQuery);
