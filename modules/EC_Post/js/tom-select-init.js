/**
 * EC_Post - TomSelect Module
 * Khởi tạo TomSelect widgets cho categories và tags
 */

(function () {
    'use strict';

    const TomSelectModule = {
        instances: {},

        init: function () {
            this.ensureLibraryLoaded();
        },

        ensureLibraryLoaded: function () {
            // CSS
            if (!document.querySelector('link[href*="tom-select"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css';
                document.head.appendChild(link);
            }

            // JS
            if (typeof TomSelect === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js';
                script.onload = this.initTomSelects.bind(this);
                document.head.appendChild(script);
            } else {
                this.initTomSelects();
            }
        },

        initTomSelects: function () {
            const selects = document.querySelectorAll('select.ec-multiselect');
            if (!selects || selects.length === 0) return;

            const self = this;
            selects.forEach(function (sel) {
                self.createTomSelect(sel);
            });
        },

        createTomSelect: function (sel) {
            const field = sel.getAttribute('data-field') || '';
            const isTagSel = field.toLowerCase().indexOf('tag') !== -1;
            const type = isTagSel ? 'tags' : 'categories';
            const taxonomyModule = window.ECPostApp.getModule('taxonomyAjax');

            try {
                const ts = new TomSelect(sel, {
                    plugins: ['remove_button'],
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    persist: false,
                    maxItems: null,
                    allowEmptyOption: false,
                    create: (input, callback) => {

    if (!taxonomyModule || !taxonomyModule.create) {
        callback();
        return;
    }

    taxonomyModule.create(input, type, (resp) => {
        if (resp && resp.success) {

            callback({
                value: resp.id,
                text: resp.name
            });

        } else {

            callback();

            alert(
                resp && resp.error
                    ? resp.error
                    : 'Không tạo được taxonomy'
            );
        }
    });
},
                    render: {
                        item: (item, escape) => {
                            return '<div class="ts__item" data-value="' + escape(item.value) + '">' +
                                escape(item.text) + '</div>';
                        }
                    },
                    onChange: () => {
                        this.syncHiddenField(sel);
                    }
                });

                sel._tsInstance = ts;
                this.instances[field] = ts;
                this.syncHiddenField(sel);
            } catch (e) {
                console.error('[TomSelect] Init error:', e);
            }
        },

        syncHiddenField: function (sel) {
            const ts = sel._tsInstance;
            const field = sel.getAttribute('data-field');
            const hidden = document.getElementById(field);

            if (!ts || !hidden) return;

            const val = ts.getValue();
            hidden.value = Array.isArray(val) ? val.join(',') : (val || '');
        },

        addOption: function (selector, option) {
            const sel = document.querySelector(selector);
            if (!sel || !sel._tsInstance) return;

            const ts = sel._tsInstance;
            ts.addOption({ value: option.value, text: option.text });
            ts.addItem(option.value, false);
            this.syncHiddenField(sel);
        },

        getInstance: function (field) {
            return this.instances[field] || null;
        }
    };

    // Register module
    if (window.ECPostApp) {
        window.ECPostApp.register('tomSelect', TomSelectModule);
    } else {
        window.addEventListener('ECPostAppReady', () => {
            window.ECPostApp.register('tomSelect', TomSelectModule);
        });
    }
})();
