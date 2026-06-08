/**
 * EC_Post - Thumbnail Preview Module
 * Hiển thị live preview của hình ảnh từ URL
 */

(function() {
    'use strict';

    const ThumbnailPreviewModule = {
        containerId: 'ec_post_thumbnail_preview_container',
        elements: {},

        init: function() {
            this.initPreview();
        },

        /**
         * Khởi tạo preview container
         */
        initPreview: function() {
            const input = document.getElementById('thumbnail_url') || 
                         document.querySelector('input[name="thumbnail_url"]');
            if (!input) return;

            // Kiểm tra container đã tồn tại chưa
            if (document.getElementById(this.containerId)) {
                this.attachInputListener(input);
                return;
            }

            this.createPreviewContainer(input);
            this.attachInputListener(input);
        },

        /**
         * Tạo preview container HTML
         */
        createPreviewContainer: function(input) {
            const container = document.createElement('div');
            container.id = this.containerId;
            container.style.cssText = 'margin-top:8px;display:flex;align-items:center;gap:8px;';

            // Wrap cho image
            const imgWrap = document.createElement('div');
            imgWrap.style.cssText = 'position:relative;display:inline-block;';

            // Image element
            const img = document.createElement('img');
            img.id = this.containerId + '_img';
            img.alt = 'Preview';
            img.style.cssText = 'max-width:240px;max-height:160px;display:none;object-fit:contain;border:1px solid #ddd;padding:4px;';

            // Close button
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.innerHTML = '&times;';
            closeBtn.title = 'Xóa link ảnh';
            closeBtn.style.cssText = 'position:absolute;top:-8px;right:-8px;background:#fff;border:1px solid #ccc;border-radius:50%;width:28px;height:28px;cursor:pointer;display:none;padding:0;';

            imgWrap.appendChild(img);
            imgWrap.appendChild(closeBtn);

            // Info text
            const info = document.createElement('div');
            info.id = this.containerId + '_info';
            info.style.cssText = 'color:#666;font-size:12px;';

            container.appendChild(imgWrap);
            container.appendChild(info);
            input.parentNode.insertBefore(container, input.nextSibling);

            // Store references
            this.elements = {
                input: input,
                container: container,
                img: img,
                closeBtn: closeBtn,
                info: info
            };

            // Attach close button listener
            closeBtn.addEventListener('click', () => {
                this.clearPreview();
            });
        },

        /**
         * Gắn event listener cho input
         */
        attachInputListener: function(input) {
            if (input._previewAttached) return;
            input._previewAttached = true;

            input.addEventListener('input', () => {
                this.updatePreview();
            });

            input.addEventListener('change', () => {
                this.updatePreview();
            });

            input.addEventListener('paste', () => {
                setTimeout(() => {
                    this.updatePreview();
                }, 50);
            });

            // Update on load
            this.updatePreview();
        },

        /**
         * Cập nhật preview
         */
        updatePreview: function() {
            const img = document.getElementById(this.containerId + '_img');
            const info = document.getElementById(this.containerId + '_info');
            const input = document.querySelector('input[name="thumbnail_url"]');
            const closeBtn = document.querySelector('#' + this.containerId + ' button');

            if (!img || !input) return;

            const val = input.value && input.value.trim();

            // Không có URL
            if (!val) {
                this.hidePreview();
                return;
            }

            // URL không giống hình ảnh
            if (!this.isLikelyImageUrl(val)) {
                input.style.display = '';
                img.style.display = 'none';
                img.removeAttribute('src');
                info.textContent = 'URL không giống ảnh';
                if (closeBtn) closeBtn.style.display = 'none';
                return;
            }

            // Đang tải
            info.textContent = 'Đang tải ảnh...';
            img.style.display = 'none';
            if (closeBtn) closeBtn.style.display = 'none';

            // Load image
            img.onload = () => {
                input.style.display = 'none';
                img.style.display = 'inline-block';
                info.textContent = '';
                if (closeBtn) closeBtn.style.display = '';
            };

            img.onerror = () => {
                input.style.display = '';
                img.style.display = 'none';
                info.textContent = 'Không tải được ảnh';
                if (closeBtn) closeBtn.style.display = 'none';
            };

            img.src = val;
        },

        /**
         * Kiểm tra URL có giống hình ảnh không
         */
        isLikelyImageUrl: function(url) {
            if (!url) return false;
            
            // Data URI
            if (url.indexOf('data:image/') === 0) return true;
            
            // Check extension
            const ext = url.split('?')[0].split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].indexOf(ext) !== -1) {
                return true;
            }
            
            // Check path patterns
            if (url.indexOf('/uploads/') !== -1 || 
                url.indexOf('cdn') !== -1 || 
                url.indexOf('img') !== -1) {
                return true;
            }
            
            return false;
        },

        /**
         * Ẩn preview
         */
        hidePreview: function() {
            const img = document.getElementById(this.containerId + '_img');
            const info = document.getElementById(this.containerId + '_info');
            const input = document.querySelector('input[name="thumbnail_url"]');
            const closeBtn = document.querySelector('#' + this.containerId + ' button');

            if (input) input.style.display = '';
            if (img) {
                img.style.display = 'none';
                img.removeAttribute('src');
            }
            if (info) info.textContent = '';
            if (closeBtn) closeBtn.style.display = 'none';
        },

        /**
         * Xóa preview
         */
        clearPreview: function() {
            const input = document.querySelector('input[name="thumbnail_url"]');
            if (input) {
                input.value = '';
                input.dispatchEvent(new Event('change'));
            }
            this.hidePreview();
        }
    };

    // Register module
    if (window.ECPostApp) {
        window.ECPostApp.register('thumbnailPreview', ThumbnailPreviewModule);
    } else {
        window.addEventListener('ECPostAppReady', () => {
            window.ECPostApp.register('thumbnailPreview', ThumbnailPreviewModule);
        });
    }
})();
