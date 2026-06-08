/**
 * EC_Post - Slug Autofill Module
 * Tự động sinh slug từ post_title khi slug chưa được chỉnh sửa
 */

(function() {
    'use strict';

    const SlugAutofillModule = {
        slugTouched: false,
        lastGenerated: '',

        init: function() {
            this.setupSlugAutofill();
        },

        /**
         * Thiết lập auto-fill slug
         */
        setupSlugAutofill: function() {
            const title = document.getElementById('post_title') || 
                         document.querySelector('input[name="post_title"]');
            const slug = document.getElementById('slug') || 
                        document.querySelector('input[name="slug"]');

            if (!title || !slug) return;

            // Reset state
            this.slugTouched = false;
            this.lastGenerated = '';

            // Track khi user chỉnh sửa slug
            slug.addEventListener('input', () => {
                this.slugTouched = true;
            });

            // Auto-generate slug khi thay đổi title
            title.addEventListener('input', () => {
                if (this.slugTouched) return; // Nếu user đã chỉnh slug thì không tự động

                const generated = this.slugify(title.value.trim());
                
                // Chỉ update nếu slug trống hoặc là slug được generate trước đó
                if (!slug.value || slug.value === this.lastGenerated) {
                    slug.value = generated;
                    this.lastGenerated = generated;
                    slug.dispatchEvent(new Event('change'));
                }
            });

            // Initial slug generation nếu slug chưa có
            if (!slug.value) {
                slug.value = this.slugify(title.value.trim());
                this.lastGenerated = slug.value;
            }
        },

        /**
         * Chuyển text thành slug
         * - Xóa diacritics (dấu)
         * - Chỉ giữ lại chữ cái, số, dấu gạch ngang
         * - Chuyển thành chữ thường
         */
        slugify: function(text) {
            if (!text) return '';

            // Remove diacritics
            try {
                if (typeof text.normalize === 'function') {
                    text = text
                        .normalize('NFD')
                        .replace(/\p{Diacritic}/gu, '');
                }
            } catch (e) {
                // Fallback for older browsers
                console.warn('[Slug] normalize not supported, using simple method');
            }

            // Convert to slug
            return text
                .replace(/[^A-Za-z0-9]+/g, '-')  // Replace non-alphanumeric with -
                .replace(/^-+|-+$/g, '')           // Remove leading/trailing -
                .toLowerCase();
        },

        /**
         * Reset state (dùng khi form reset)
         */
        reset: function() {
            this.slugTouched = false;
            this.lastGenerated = '';
        }
    };

    // Register module
    if (window.ECPostApp) {
        window.ECPostApp.register('slugAutofill', SlugAutofillModule);
    } else {
        window.addEventListener('ECPostAppReady', () => {
            window.ECPostApp.register('slugAutofill', SlugAutofillModule);
        });
    }
})();
