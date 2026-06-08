/**
 * EC_Post - Taxonomy AJAX Module
 * Xử lý AJAX request để tạo taxonomy mới (categories/tags)
 */

(function() {
    'use strict';

    const TaxonomyAjaxModule = {
        endpoint: 'index.php?entryPoint=epCreateTaxonomy',
        timeout: 15000,

        init: function() {
            // Module này không cần initialization đặc biệt
            // Chỉ cung cấp methods cho các module khác
        },

        /**
         * Tạo taxonomy mới qua AJAX
         * @param {string} name - Tên taxonomy
         * @param {string} type - Loại: 'tags' hoặc 'categories'
         * @param {function} callback - Callback nhận response
         */
        create: function(name, type, callback) {
            if (!name || !name.trim()) {
                callback && callback({ success: false, error: 'empty_name' });
                return;
            }

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.endpoint, true);
                xhr.withCredentials = true;
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.onreadystatechange = () => {
                    if (xhr.readyState !== 4) return;
                    this.handleResponse(xhr, callback);
                };

                xhr.onerror = () => {
                    callback && callback({ success: false, error: 'network_error' });
                };

                xhr.timeout = this.timeout;
                xhr.ontimeout = () => {
                    callback && callback({ success: false, error: 'timeout' });
                };

                const params = 'type=' + encodeURIComponent(type) + 
                              '&name=' + encodeURIComponent(name);
                xhr.send(params);
            } catch (e) {
                callback && callback({ success: false, error: String(e) });
            }
        },

        /**
         * Xử lý response từ AJAX request
         */
        handleResponse: function(xhr, callback) {
            let respObj = { success: false };

            try {
                respObj = JSON.parse(xhr.responseText);
            } catch (e) {
                respObj = {
                    success: false,
                    error: 'invalid_response (HTTP ' + xhr.status + ')',
                    raw: xhr.responseText
                };
            }

            respObj._status = xhr.status;
            this.logResponse(respObj);
            callback && callback(respObj);
        },

        /**
         * Log response để debug
         */
        logResponse: function(respObj) {
            if (respObj.success) {
                console.log('[Taxonomy] ✓ Created:', respObj.name, '(ID: ' + respObj.id + ')');
            } else {
                console.warn('[Taxonomy] ✗ Error:', respObj.error, respObj.raw);
            }
        },

        /**
         * Xử lý lỗi response
         */
        handleError: function(respObj, type) {
            const msg = (respObj && respObj.error) ? respObj.error : 'Lỗi không xác định';
            const snippet = (respObj && respObj.raw) ? 
                '\nSnippet: ' + String(respObj.raw).substring(0, 200) : '';
            
            return 'Không tạo được ' + type + ': ' + msg + snippet;
        }
    };

    // Register module
    if (window.ECPostApp) {
        window.ECPostApp.register('taxonomyAjax', TaxonomyAjaxModule);
    } else {
        window.addEventListener('ECPostAppReady', () => {
            window.ECPostApp.register('taxonomyAjax', TaxonomyAjaxModule);
        });
    }
})();
