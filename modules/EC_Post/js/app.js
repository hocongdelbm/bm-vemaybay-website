/**
 * EC_Post Module - JavaScript App Initializer
 * Quản lý khởi động tất cả các modules JavaScript
 */

(function() {
    'use strict';

    // ─── Modules Registry ──────────────────────────────────────────────────
    const ECPostApp = {
        modules: {},
        
        register: function(name, module) {
            this.modules[name] = module;
            console.log('[EC_Post] Module registered:', name);
        },

        init: function() {
            // Khởi động tất cả modules theo thứ tự
            const initOrder = [
                'tomSelect',
                'taxonomyAjax',
                'quickAddButtons',
                'thumbnailPreview',
                'slugAutofill',
                'tinyMceEditor'
            ];

            initOrder.forEach(name => {
                if (this.modules[name] && typeof this.modules[name].init === 'function') {
                    try {
                        this.modules[name].init();
                        console.log('[EC_Post] ✓ ' + name + ' initialized');
                    } catch (error) {
                        console.error('[EC_Post] ✗ Error initializing ' + name + ':', error);
                    }
                }
            });
        },

        getModule: function(name) {
            return this.modules[name] || null;
        }
    };

    // ─── Wait for DOM Ready ─────────────────────────────────────────────────
    function domReady() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ECPostApp.init.bind(ECPostApp));
        } else {
            ECPostApp.init();
        }
    }

    // ─── Expose to global scope ────────────────────────────────────────────
    window.ECPostApp = ECPostApp;
    
    domReady();
})();
