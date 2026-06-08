/**
 * EC_Post - Quick Add Buttons Module
 * Tạo nút "Thêm thẻ" và "Thêm danh mục" bên cạnh nút Hủy
 */

(function() {
    'use strict';

    const QuickAddButtonsModule = {
        buttons: {},

        init: function() {
            this.createButtons();
        },

        /**
         * Tìm nút Cancel và thêm các nút quick-add bên cạnh
         */
        createButtons: function() {
            try {
                const cancelBtn = this.findCancelButton();
                if (!cancelBtn || !cancelBtn.parentNode) return;

                const btnTag = this.createButton('Thêm thẻ');
                const btnCat = this.createButton('Thêm danh mục');

                cancelBtn.parentNode.insertBefore(btnTag, cancelBtn.nextSibling);
                cancelBtn.parentNode.insertBefore(btnCat, btnTag.nextSibling);

                this.buttons.addTags = btnTag;
                this.buttons.addCategories = btnCat;

                this.attachEventListeners(btnTag, btnCat);
            } catch (e) {
                console.error('[QuickAddButtons] Init error:', e);
            }
        },

        /**
         * Tìm nút Hủy (Cancel)
         */
        findCancelButton: function() {
            const allButtons = Array.prototype.slice.call(document.querySelectorAll('button, a'));
            return allButtons.find(b => {
                const text = (b.textContent || b.innerText || '').trim();
                return text === 'Hủy' || text.indexOf('Hủy') !== -1;
            });
        },

        /**
         * Tạo button element
         */
        createButton: function(label) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-secondary';
            btn.style.marginLeft = '8px';
            btn.textContent = label;
            return btn;
        },

        /**
         * Gắn event listeners cho các nút
         */
        attachEventListeners: function(btnTag, btnCat) {
            const self = this;

            btnTag.addEventListener('click', (e) => {
                e.preventDefault();
                self.handleAddTags();
            });

            btnCat.addEventListener('click', (e) => {
                e.preventDefault();
                self.handleAddCategories();
            });
        },

        /**
         * Xử lý thêm thẻ mới
         */
        handleAddTags: function() {
            const names = prompt('Nhập tên thẻ mới (nhiều thẻ cách nhau bởi dấu phẩy):');
            if (!names) return;

            const taxonomyModule = window.ECPostApp.getModule('taxonomyAjax');
            const tomSelectModule = window.ECPostApp.getModule('tomSelect');

            if (!taxonomyModule) return;

            names
                .split(',')
                .map(s => s.trim())
                .filter(Boolean)
                .forEach(name => {
                    taxonomyModule.create(name, 'tags', (resp) => {
                            console.log('AJAX Response:', resp);
                        if (resp && resp.success && resp.id) {
                            if (tomSelectModule) {
                                tomSelectModule.addOption(
                                    'select.ec-multiselect[data-field="tags_selector"]',
                                    { value: resp.id, text: resp.name || name }
                                );
                            }
                        } else {
                            const error = resp && resp.error ? resp.error : 'Lỗi';
                            alert('Không tạo được thẻ "' + name + '": ' + error);
                        }
                    });
                });
        },

        /**
         * Xử lý thêm danh mục mới
         */
        handleAddCategories: function() {
            const name = prompt('Nhập tên danh mục mới:');
            if (!name || !name.trim()) return;

            const taxonomyModule = window.ECPostApp.getModule('taxonomyAjax');
            const tomSelectModule = window.ECPostApp.getModule('tomSelect');

            if (!taxonomyModule) return;

            taxonomyModule.create(name.trim(), 'categories', (resp) => {
                if (resp && resp.success && resp.id) {
                    if (tomSelectModule) {
                        tomSelectModule.addOption(
                            'select.ec-multiselect[data-field="categories_selector"]',
                            { value: resp.id, text: resp.name || name }
                        );
                    }
                } else {
                    const error = resp && resp.error ? resp.error : 'Lỗi';
                    alert('Không tạo được danh mục: ' + error);
                }
            });
        }
    };

    // Register module
    if (window.ECPostApp) {
        window.ECPostApp.register('quickAddButtons', QuickAddButtonsModule);
    } else {
        window.addEventListener('ECPostAppReady', () => {
            window.ECPostApp.register('quickAddButtons', QuickAddButtonsModule);
        });
    }
})();
