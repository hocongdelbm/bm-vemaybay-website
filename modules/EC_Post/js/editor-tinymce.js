/**
 * EC_Post - TinyMCE Editor Module
 * Khởi tạo TinyMCE WYSIWYG editor cho post_content
 */

(function() {
    'use strict';

    const TinyMceEditorModule = {
        editorLoaded: false,
        cdnUrl: 'https://cdn.tiny.cloud/1/2bd0uutn4lrzz9y2bsgpwtw8h8ell7vq83bvos474i5rnmjr/tinymce/6/tinymce.min.js',

        init: function() {
            this.loadEditor();
        },

        /**
         * Load TinyMCE từ CDN
         */
        loadEditor: function() {
            if (typeof tinymce !== 'undefined') {
                // TinyMCE đã được load
                this.initEditor();
            } else {
                // Load script từ CDN
                const script = document.createElement('script');
                script.src = this.cdnUrl;
                script.referrerPolicy = 'origin';
                script.onload = () => {
                    this.initEditor();
                };
                script.onerror = () => {
                    console.error('[TinyMCE] Failed to load from CDN');
                };
                document.head.appendChild(script);
            }
        },

        /**
         * Khởi tạo TinyMCE editor
         */
        initEditor: function() {
            if (typeof tinymce === 'undefined') {
                console.error('[TinyMCE] tinymce object not found');
                return;
            }

            try {
                tinymce.init({
                    selector: 'textarea[name="post_content"]',
                    height: 420,
                    menubar: false,
                    plugins: [
                        'advlist',
                        'autolink',
                        'lists',
                        'link',
                        'image',
                        'charmap',
                        'preview',
                        'anchor',
                        'searchreplace',
                        'visualblocks',
                        'code',
                        'table',
                        'paste',
                        'help',
                        'wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                            'alignleft aligncenter alignright | bullist numlist outdent indent | ' +
                            'link image | removeformat | code',
                    branding: false,
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,
                    paste_as_text: false,
                    setup: (editor) => {
                        this.setupFormHandler(editor);
                    }
                });

                this.editorLoaded = true;
                console.log('[TinyMCE] ✓ Editor initialized');
            } catch (e) {
                console.error('[TinyMCE] Init error:', e);
            }
        },

        /**
         * Thiết lập event handler cho form submission
         */
        setupFormHandler: function(editor) {
            const form = document.querySelector('form[name=EditView]');
            if (!form) return;

            // Kiểm tra đã bind chưa để tránh bind nhiều lần
            if (form._tinymceBound) return;
            
            form._tinymceBound = true;

            form.addEventListener('submit', () => {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
            });

            console.log('[TinyMCE] ✓ Form handler attached');
        },

        /**
         * Get editor instance
         */
        getEditor: function() {
            if (typeof tinymce === 'undefined') return null;
            return tinymce.get('post_content');
        },

        /**
         * Set content programmatically
         */
        setContent: function(content) {
            const editor = this.getEditor();
            if (editor) {
                editor.setContent(content);
            }
        },

        /**
         * Get content
         */
        getContent: function() {
            const editor = this.getEditor();
            return editor ? editor.getContent() : null;
        }
    };

    // Register module
    if (window.ECPostApp) {
        window.ECPostApp.register('tinyMceEditor', TinyMceEditorModule);
    } else {
        window.addEventListener('ECPostAppReady', () => {
            window.ECPostApp.register('tinyMceEditor', TinyMceEditorModule);
        });
    }
})();
