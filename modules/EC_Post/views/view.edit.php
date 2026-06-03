<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class EC_PostViewEdit extends ViewEdit
{
    public function display()
    {
        global $current_user;

        $postId        = isset($this->bean->id) ? $this->bean->id : null;
        $valCategories = isset($this->bean->categories_selector) ? $this->bean->categories_selector : '';
        $valTags       = isset($this->bean->tags_selector)       ? $this->bean->tags_selector       : '';

        // ── Multi-selector widgets ──────────────────────────────────────────
        $widgetPath   = 'custom/modules/EC_Post/widgets/EC_Post_multiSelectorWidget.php';
        $selectorHtml = '';
        $tagsHtml     = '';

        if (file_exists($widgetPath)) {
            require_once($widgetPath);
            if (class_exists('EC_Post_multiSelectorWidget') && method_exists('EC_Post_multiSelectorWidget', 'render')) {
                $selectorHtml = EC_Post_multiSelectorWidget::render('categories_selector', $valCategories, 'categories', $postId);
                $tagsHtml     = EC_Post_multiSelectorWidget::render('tags_selector',       $valTags,       'tags',       $postId);
            }
        }

        $this->ss->assign('CATEGORIES_SELECTOR', $selectorHtml);
        $this->ss->assign('TAGS_SELECTOR',       $tagsHtml);

        // ── Author field (read-only, auto-filled) ───────────────────────────
        try {
            $authorId   = '';
            $authorName = '';
            if (!empty($current_user) && is_object($current_user)) {
                $authorId = !empty($this->bean->author_id)
                    ? $this->bean->author_id
                    : $current_user->id;
                $authorName = method_exists($current_user, 'get_display_name')
                    ? $current_user->get_display_name()
                    : (!empty($current_user->full_name)
                        ? $current_user->full_name
                        : (!empty($current_user->user_name) ? $current_user->user_name : ''));
            }
            $authorHtml  = "<input type='hidden' id='author_id' name='author_id' value='" . htmlspecialchars($authorId, ENT_QUOTES) . "' />";
            $authorHtml .= "<div class='ec-author-readonly' style='padding:6px 8px;border:1px solid #e1e1e1;background:#fafafa;border-radius:4px;'>"
                         . htmlspecialchars($authorName, ENT_QUOTES) . "</div>";
            $this->ss->assign('AUTHOR_FIELD', $authorHtml);
        } catch (Exception $e) {
            $this->ss->assign('AUTHOR_FIELD', '');
        }

        // ── Render the SugarCRM form ────────────────────────────────────────
        parent::display();

        // ── TomSelect (loaded once, after the form) ─────────────────────────
        echo "<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.default.min.css\">\n";
        echo "<script src=\"https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js\"></script>\n";

        // ── Init TomSelect + AJAX create ───────────────────────────────────
        echo <<<'TS'
<script>
(function(){
    // ── helpers ──────────────────────────────────────────────────────────────
    function syncHidden(sel) {
        var ts     = sel._tsInstance;
        var field  = sel.getAttribute('data-field');
        var hidden = document.getElementById(field);
        if (!ts || !hidden) return;
        var val = ts.getValue();
        hidden.value = Array.isArray(val) ? val.join(',') : (val || '');
    }

    function createTaxonomyAjax(name, type, cb) {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php?entryPoint=epCreateTaxonomy', true);
            xhr.withCredentials = true;
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;
                var respObj = { success: false };
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
                cb && cb(respObj);
            };
            xhr.onerror = function() { cb && cb({ success: false, error: 'network_error' }); };
            xhr.send('type=' + encodeURIComponent(type) + '&name=' + encodeURIComponent(name));
        } catch (e) { cb && cb({ success: false, error: String(e) }); }
    }

    function addOptionToTomSelect(selector, resp) {
        var sel = document.querySelector(selector);
        if (!sel || !sel._tsInstance) return;
        var ts = sel._tsInstance;
        ts.addOption({ value: resp.id, text: resp.name });
        ts.addItem(resp.id, false);
        syncHidden(sel);
    }

    // ── Init all ec-multiselect elements ─────────────────────────────────────
    function initTomSelects() {
        var selects = document.querySelectorAll('select.ec-multiselect');
        if (!selects || selects.length === 0) return;

        selects.forEach(function(sel) {
            var field    = sel.getAttribute('data-field') || '';
            var isTagSel = field.toLowerCase().indexOf('tag') !== -1;
            var type     = isTagSel ? 'tags' : 'categories';

            try {
                var ts = new TomSelect(sel, {
                    plugins:          ['remove_button'],
                    valueField:       'value',
                    labelField:       'text',
                    searchField:      ['text'],
                    persist:          false,
                    maxItems:         null,
                    allowEmptyOption: false,
                    // Cả tags lẫn categories đều cho phép tạo mới trực tiếp
                    create: function(input, callback) {
                        createTaxonomyAjax(input, type, function(resp) {
                            try { console.log('epCreateTaxonomy [' + type + ']', resp); } catch(e){}
                            if (resp && resp.success && resp.id) {
                                callback({ value: resp.id, text: resp.name || input });
                            } else {
                                var msg = (resp && resp.error) ? resp.error : 'Lỗi không xác định';
                                var snippet = (resp && resp.raw) ? '\nSnippet: ' + String(resp.raw).substring(0, 200) : '';
                                try { alert('Không tạo được ' + type + ': ' + msg + snippet); } catch(e){}
                                callback();
                            }
                        });
                    },
                    render: {
                        item: function(item, escape) {
                            return '<div class="ts__item" data-value="' + escape(item.value) + '">' + escape(item.text) + '</div>';
                        }
                    },
                    onChange: function() { syncHidden(sel); }
                });

                sel._tsInstance = ts;
                syncHidden(sel);
            } catch (e) { console && console.error('TomSelect init error', e); }
        });
    }

    // ── "Thêm thẻ / Thêm danh mục" buttons next to Cancel ────────────────────
    function initQuickAddButtons() {
        try {
            var allBtns   = Array.prototype.slice.call(document.querySelectorAll('button, a'));
            var cancelBtn = allBtns.find(function(b) {
                var t = (b.textContent || b.innerText || '').trim();
                return t === 'Hủy' || t.indexOf('Hủy') !== -1;
            });
            if (!cancelBtn || !cancelBtn.parentNode) return;

            function makeBtn(label) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-secondary';
                btn.style.marginLeft = '8px';
                btn.textContent = label;
                return btn;
            }

            var btnTag = makeBtn('Thêm thẻ');
            var btnCat = makeBtn('Thêm danh mục');
            cancelBtn.parentNode.insertBefore(btnTag, cancelBtn.nextSibling);
            cancelBtn.parentNode.insertBefore(btnCat, btnTag.nextSibling);

            btnTag.addEventListener('click', function() {
                var names = prompt('Nhập tên thẻ mới (nhiều thẻ cách nhau bởi dấu phẩy):');
                if (!names) return;
                names.split(',').map(function(s){ return s.trim(); }).filter(Boolean).forEach(function(n) {
                    createTaxonomyAjax(n, 'tags', function(resp) {
                        if (resp && resp.success && resp.id) {
                            addOptionToTomSelect('select.ec-multiselect[data-field="tags_selector"]', resp);
                        } else {
                            alert('Không tạo được thẻ "' + n + '": ' + (resp && resp.error ? resp.error : 'Lỗi'));
                        }
                    });
                });
            });

            btnCat.addEventListener('click', function() {
                var name = prompt('Nhập tên danh mục mới:');
                if (!name || !name.trim()) return;
                createTaxonomyAjax(name.trim(), 'categories', function(resp) {
                    if (resp && resp.success && resp.id) {
                        addOptionToTomSelect('select.ec-multiselect[data-field="categories_selector"]', resp);
                    } else {
                        alert('Không tạo được danh mục: ' + (resp && resp.error ? resp.error : 'Lỗi'));
                    }
                });
            });
        } catch (e) { console && console.error('initQuickAddButtons error', e); }
    }

    function init() {
        initTomSelects();
        initQuickAddButtons();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
</script>
TS;

        // ── Thumbnail URL live preview ────────────────────────────────────────
        echo <<<'PREVIEW'
<script>
;(function(){
    function initThumbnailPreview() {
        var input = document.getElementById('thumbnail_url') || document.querySelector('input[name="thumbnail_url"]');
        if (!input) return;

        var containerId = 'ec_post_thumbnail_preview_container';
        if (!document.getElementById(containerId)) {
            var container = document.createElement('div');
            container.id = containerId;
            container.style.cssText = 'margin-top:8px;display:flex;align-items:center;gap:8px;';

            var imgWrap = document.createElement('div');
            imgWrap.style.cssText = 'position:relative;display:inline-block;';

            var img = document.createElement('img');
            img.id  = containerId + '_img';
            img.alt = 'Preview';
            img.style.cssText = 'max-width:240px;max-height:160px;display:none;object-fit:contain;border:1px solid #ddd;padding:4px;';

            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.innerHTML = '&times;';
            closeBtn.title = 'Xóa link ảnh';
            closeBtn.style.cssText = 'position:absolute;top:-8px;right:-8px;background:#fff;border:1px solid #ccc;border-radius:50%;width:28px;height:28px;cursor:pointer;display:none;padding:0;';

            imgWrap.appendChild(img);
            imgWrap.appendChild(closeBtn);

            var info = document.createElement('div');
            info.id = containerId + '_info';
            info.style.cssText = 'color:#666;font-size:12px;';

            container.appendChild(imgWrap);
            container.appendChild(info);
            input.parentNode.insertBefore(container, input.nextSibling);
        }

        var previewImg  = document.getElementById(containerId + '_img');
        var previewInfo = document.getElementById(containerId + '_info');
        var closeBtn    = document.querySelector('#' + containerId + ' button');

        function isLikelyImageUrl(url) {
            if (!url) return false;
            if (url.indexOf('data:image/') === 0) return true;
            var ext = url.split('?')[0].split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','gif','webp','bmp','svg'].indexOf(ext) !== -1) return true;
            if (url.indexOf('/uploads/') !== -1 || url.indexOf('cdn') !== -1 || url.indexOf('img') !== -1) return true;
            return false;
        }

        var loadTimeout = null;
        function updatePreview() {
            var val = input.value && input.value.trim();
            if (!val) {
                input.style.display = '';
                previewImg.style.display = 'none';
                previewImg.removeAttribute('src');
                previewInfo.textContent = '';
                if (closeBtn) closeBtn.style.display = 'none';
                return;
            }
            if (!isLikelyImageUrl(val)) {
                input.style.display = '';
                previewImg.style.display = 'none';
                previewInfo.textContent = 'URL không giống ảnh';
                if (closeBtn) closeBtn.style.display = 'none';
                return;
            }
            previewInfo.textContent = 'Đang tải ảnh...';
            previewImg.style.display = 'none';
            if (closeBtn) closeBtn.style.display = 'none';
            if (loadTimeout) clearTimeout(loadTimeout);
            loadTimeout = setTimeout(function() {
                previewImg.onload = function() {
                    input.style.display = 'none';
                    previewImg.style.display = 'inline-block';
                    previewInfo.textContent = '';
                    if (closeBtn) closeBtn.style.display = '';
                };
                previewImg.onerror = function() {
                    input.style.display = '';
                    previewImg.style.display = 'none';
                    previewInfo.textContent = 'Không tải được ảnh';
                    if (closeBtn) closeBtn.style.display = 'none';
                };
                previewImg.src = val;
            }, 150);
        }

        input.addEventListener('input',  updatePreview);
        input.addEventListener('change', updatePreview);
        input.addEventListener('paste',  function(){ setTimeout(updatePreview, 50); });

        if (closeBtn) closeBtn.addEventListener('click', function() {
            input.value = '';
            input.style.display = '';
            previewImg.style.display = 'none';
            previewImg.removeAttribute('src');
            previewInfo.textContent = '';
            closeBtn.style.display = 'none';
            input.dispatchEvent(new Event('change'));
        });

        updatePreview();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initThumbnailPreview);
    else initThumbnailPreview();
})();
</script>
PREVIEW;

        // ── Slug auto-fill from title ──────────────────────────────────────
        echo <<<'SLUGJS'
<script>
;(function(){
    function initSlugAutoFill() {
        var title = document.getElementById('post_title') || document.querySelector('input[name="post_title"]');
        var slug  = document.getElementById('slug')       || document.querySelector('input[name="slug"]');
        if (!title || !slug) return;

        var slugTouched   = false;
        var lastGenerated = '';

        slug.addEventListener('input', function(){ slugTouched = true; });

        function slugifyClient(text) {
            if (!text) return '';
            try {
                if (typeof text.normalize === 'function') {
                    text = text.normalize('NFD').replace(/\p{Diacritic}/gu, '');
                }
            } catch(e){}
            return text.replace(/[^A-Za-z0-9]+/g, '-').replace(/^-+|-+$/g, '').toLowerCase();
        }

        title.addEventListener('input', function() {
            if (slugTouched) return;
            var gen = slugifyClient(title.value.trim());
            if (!slug.value || slug.value === lastGenerated) {
                slug.value = gen;
                lastGenerated = gen;
                slug.dispatchEvent(new Event('change'));
            }
        });

        if (!slug.value) {
            slug.value = slugifyClient(title.value.trim());
            lastGenerated = slug.value;
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSlugAutoFill);
    else initSlugAutoFill();
})();
</script>
SLUGJS;

        // ── TinyMCE WYSIWYG for post_content ──────────────────────────────
        echo <<<'TINYMCE'
<script>
(function(){
    function initEditor() {
        if (typeof tinymce === 'undefined') return;
        tinymce.init({
            selector:           'textarea[name="post_content"]',
            height:             420,
            menubar:            false,
            plugins:            'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code table paste help wordcount',
            toolbar:            'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image | removeformat | code',
            branding:           false,
            relative_urls:      false,
            remove_script_host: false,
            convert_urls:       true,
            paste_as_text:      false,
            setup: function(editor) {
                var form = document.querySelector('form[name=EditView]');
                if (form && !form._tinymceBound) {
                    form._tinymceBound = true;
                    form.addEventListener('submit', function() {
                        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
                    });
                }
            }
        });
    }

    if (typeof tinymce === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://cdn.tiny.cloud/1/2bd0uutn4lrzz9y2bsgpwtw8h8ell7vq83bvos474i5rnmjr/tinymce/6/tinymce.min.js';
        s.referrerPolicy = 'origin';
        s.onload = initEditor;
        document.head.appendChild(s);
    } else {
        initEditor();
    }
})();
</script>
TINYMCE;
    }
}