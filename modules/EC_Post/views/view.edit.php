<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class EC_PostViewEdit extends ViewEdit
{
    public function display()
    {
        global $current_user;
        // Prepare selectors HTML (using existing widget) and assign to Smarty placeholders
        $postId = isset($this->bean->id) ? $this->bean->id : null;
        $valCategories = isset($this->bean->categories_selector) ? $this->bean->categories_selector : '';
        $valTags = isset($this->bean->tags_selector) ? $this->bean->tags_selector : '';

        $selectorHtml = '';
        $widgetPath = 'custom/modules/EC_Post/widgets/EC_Post_multiSelectorWidget.php';
        if (file_exists($widgetPath)) {
            require_once($widgetPath);
            if (class_exists('EC_Post_multiSelectorWidget') && method_exists('EC_Post_multiSelectorWidget', 'render')) {
                $selectorHtml = EC_Post_multiSelectorWidget::render('categories_selector', $valCategories, 'categories', $postId);
            }
        }
        $this->ss->assign('CATEGORIES_SELECTOR', $selectorHtml);

        $tagsHtml = '';
        if (file_exists($widgetPath)) {
            require_once($widgetPath);
            if (class_exists('EC_Post_multiSelectorWidget') && method_exists('EC_Post_multiSelectorWidget', 'render')) {
                $tagsHtml = EC_Post_multiSelectorWidget::render('tags_selector', $valTags, 'tags', $postId);
            }
        }
        $this->ss->assign('TAGS_SELECTOR', $tagsHtml);

        // AUTHOR field: auto-fill with current logged-in user and show read-only
        try {
            $authorId = '';
            $authorName = '';
            if (!empty($current_user) && is_object($current_user)) {
                $authorId = !empty($this->bean->author_id) ? $this->bean->author_id : $current_user->id;
                // prefer full_name if available
                $authorName = method_exists($current_user, 'get_display_name') ? $current_user->get_display_name() : (
                    (!empty($current_user->full_name) ? $current_user->full_name : ( !empty($current_user->user_name) ? $current_user->user_name : '' ))
                );
            }
            $authorHtml = "<input type='hidden' id='author_id' name='author_id' value='" . htmlspecialchars($authorId, ENT_QUOTES) . "' />";
            $authorHtml .= "<div class='ec-author-readonly' style='padding:6px 8px;border:1px solid #e1e1e1;background:#fafafa;border-radius:4px;'>" . htmlspecialchars($authorName, ENT_QUOTES) . "</div>";
            $this->ss->assign('AUTHOR_FIELD', $authorHtml);
        } catch (Exception $e) {
            // fallback: empty
            $this->ss->assign('AUTHOR_FIELD', '');
        }

        // Inject Choices CSS before rendering the form so the select markup is styled
        echo "<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css\">\n";
        echo "<link rel=\"stylesheet\" href=\"/custom/themes/choices_custom.css\">\n";

        // Render the form (this will output the select.ec-multiselect elements)
        parent::display();

    // Inject Choices JS after the form so DOM elements exist, then initialize
    echo "<script src=\"https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js\"></script>\n";

        // Initialization script: set up Choices and sync hidden inputs
        echo <<<'JS'
<script>
(function() {
    function initChoices() {
        var selects = document.querySelectorAll('select.ec-multiselect');
        if (selects.length === 0) return;

        selects.forEach(function(sel) {
            var field = sel.getAttribute('data-field');
            var hidden = document.getElementById(field);

            var choices = new Choices(sel, {
                removeItemButton: true,
                shouldSort: false,
                placeholder: true,
                searchPlaceholderValue: 'Tìm...'
            });
                // keep a reference on the select so external scripts can rebuild the instance
                try { sel._choicesInstance = choices; } catch (e) { /* ignore */ }

            // Sync hidden input dùng Choices instance — KHÔNG dùng sel.selectedOptions
            function syncHidden() {
                if (hidden) {
                    // getValue(true) returns array of values
                    var vals = choices.getValue(true);
                    if (!Array.isArray(vals)) vals = [vals];
                    hidden.value = vals.join(',');
                }
            }

            // Sync lần đầu (load giá trị đã selected)
            syncHidden();

            // Sync khi thay đổi — dùng event của Choices.js
            sel.addEventListener('change', syncHidden);

            // Add item class so CSS can target category vs tag pills
            try {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(m) {
                        m.addedNodes && m.addedNodes.forEach(function(node) {
                            if (node.classList && node.classList.contains('choices__item')) {
                                var isChoice = node.getAttribute('data-type') === 'choice';
                                if (isChoice) {
                                    if (field && field.indexOf('category') !== -1) {
                                        node.classList.add('category-item');
                                    } else if (field && field.indexOf('tag') !== -1) {
                                        node.classList.add('tag-item');
                                    }
                                }
                            }
                        });
                    });
                });
                var choicesList = sel.parentNode.querySelector('.choices__list--multiple');
                if (choicesList) observer.observe(choicesList, { childList: true });
            } catch (e) {
                // ignore observer failures
            }

            // Sync trước khi submit
            var form = document.querySelector('form[name=EditView]');
            if (form && !form._choicesSyncBound) {
                form._choicesSyncBound = true;
                form.addEventListener('submit', function() {
                    document.querySelectorAll('select.ec-multiselect').forEach(function(s) {
                        s.dispatchEvent(new Event('change'));
                    });
                });
            }
        });
    }

    // Chạy ngay vì script đã ở cuối body — DOM đã sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChoices);
    } else {
        initChoices();
    }
})();
</script>
JS;

            // Add small buttons next to the Cancel button: Thêm thẻ, Thêm danh mục
            echo <<<'BTNJS'
    <script>
    (function(){
        try {
            function createTaxonomy(name, type, callback) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'index.php?entryPoint=epCreateTaxonomy', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState !== 4) return;
                    if (xhr.status === 200) {
                        try {
                            var resp = JSON.parse(xhr.responseText);
                            if (resp && resp.success && resp.id) {
                                callback && callback(resp);
                            } else {
                                alert('Không tạo được ' + type + ': ' + (resp && resp.error ? resp.error : 'Lỗi'));
                            }
                        } catch (e) { console && console.log(e); }
                    } else {
                        alert('Lỗi kết nối khi tạo ' + type);
                    }
                };
                var body = 'type=' + encodeURIComponent(type) + '&name=' + encodeURIComponent(name);
                xhr.send(body);
            }

            function addOptionToSelect(selector, resp) {
                var sel = document.querySelector(selector);
                if (!sel) return;
                var opt = document.createElement('option'); opt.value = resp.id; opt.text = resp.name; opt.selected = true; sel.appendChild(opt);
                // rebuild choices instance if present
                try {
                    if (sel._choicesInstance) { sel._choicesInstance.destroy(); }
                } catch (e) {}
                try {
                    var isTagSel = selector.indexOf('tags') !== -1;
                    var newChoices = new Choices(sel, { removeItemButton: true, shouldSort: false, placeholder: true, searchPlaceholderValue: 'Tìm...', addItems: isTagSel, duplicateItemsAllowed: false });
                    sel._choicesInstance = newChoices;
                } catch (e) { console && console.log(e); }
                // sync change so hidden input updates
                sel.dispatchEvent(new Event('change'));
            }

            // find cancel button by text
            var allButtons = Array.prototype.slice.call(document.querySelectorAll('button, a'));
            var cancelBtn = allButtons.find(function(b){ var t = (b.textContent||b.innerText||'').trim(); return t === 'Hủy' || t.indexOf('Hủy') !== -1; });
            if (cancelBtn && cancelBtn.parentNode) {
                var btnTag = document.createElement('button'); btnTag.type = 'button'; btnTag.className = 'btn btn-secondary'; btnTag.style.marginLeft = '8px'; btnTag.textContent = 'Thêm thẻ';
                var btnCat = document.createElement('button'); btnCat.type = 'button'; btnCat.className = 'btn btn-secondary'; btnCat.style.marginLeft = '8px'; btnCat.textContent = 'Thêm danh mục';
                cancelBtn.parentNode.insertBefore(btnTag, cancelBtn.nextSibling);
                cancelBtn.parentNode.insertBefore(btnCat, btnTag.nextSibling);

                btnTag.addEventListener('click', function(){
                    var names = prompt('Nhập tên thẻ mới (có thể nhập nhiều, ngăn cách bởi dấu phẩy):');
                    if (!names) return;
                    names.split(',').map(function(s){ return s.trim(); }).filter(Boolean).forEach(function(n){
                        createTaxonomy(n, 'tags', function(resp){ addOptionToSelect('select.ec-multiselect[data-field="tags_selector"]', resp); });
                    });
                });

                btnCat.addEventListener('click', function(){
                    var name = prompt('Nhập tên danh mục mới:');
                    if (!name) return;
                    createTaxonomy(name, 'categories', function(resp){ addOptionToSelect('select.ec-multiselect[data-field="categories_selector"]', resp); });
                });
            }
        } catch (e) { console && console.log(e); }
    })();
    </script>
    BTNJS;
        // Thumbnail URL live preview script (hide input when image loads, show dismiss X)
        echo <<<'PREVIEW'
<script>
;(function(){
    function initThumbnailPreview() {
        // try by id then by name
        var input = document.getElementById('thumbnail_url') || document.querySelector('input[name="thumbnail_url"]');
        if(!input) return;

        // create preview container if not exists
        var containerId = 'ec_post_thumbnail_preview_container';
        var existing = document.getElementById(containerId);
        if(!existing) {
            var container = document.createElement('div');
            container.id = containerId;
            container.style.marginTop = '8px';
            container.style.display = 'flex';
            container.style.alignItems = 'center';
            container.style.gap = '8px';

            // wrapper for the image with a dismiss button
            var imgWrap = document.createElement('div');
            imgWrap.style.position = 'relative';
            imgWrap.style.display = 'inline-block';

            var img = document.createElement('img');
            img.id = containerId + '_img';
            img.alt = 'Preview';
            img.style.maxWidth = '240px';
            img.style.maxHeight = '160px';
            img.style.display = 'none';
            img.style.objectFit = 'contain';
            img.style.border = '1px solid #ddd';
            img.style.padding = '4px';

            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.innerHTML = '×';
            closeBtn.title = 'Xóa link ảnh';
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '-8px';
            closeBtn.style.right = '-8px';
            closeBtn.style.background = '#fff';
            closeBtn.style.border = '1px solid #ccc';
            closeBtn.style.borderRadius = '50%';
            closeBtn.style.width = '28px';
            closeBtn.style.height = '28px';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.display = 'none';
            closeBtn.style.padding = '0';

            imgWrap.appendChild(img);
            imgWrap.appendChild(closeBtn);

            var info = document.createElement('div');
            info.id = containerId + '_info';
            info.style.color = '#666';
            info.style.fontSize = '12px';

            container.appendChild(imgWrap);
            container.appendChild(info);

            // insert after input
            input.parentNode.insertBefore(container, input.nextSibling);
        }

        var previewImg = document.getElementById(containerId + '_img');
        var previewInfo = document.getElementById(containerId + '_info');
        var closeBtn = (function(){ var b = document.querySelector('#' + containerId + ' button'); return b; })();

        function isLikelyImageUrl(url) {
            if(!url) return false;
            // basic check: data URI or ends with common image extension or contains common image hosting paths
            if(url.indexOf('data:image/') === 0) return true;
            var ext = url.split('?')[0].split('.').pop().toLowerCase();
            var good = ['jpg','jpeg','png','gif','webp','bmp','svg'];
            if(good.indexOf(ext) !== -1) return true;
            // heuristic: typical CDN or img/ uploads
            if(url.indexOf('/uploads/') !== -1 || url.indexOf('cdn') !== -1 || url.indexOf('img') !== -1) return true;
            return false;
        }

        var loadTimeout = null;
        function updatePreview() {
            var val = input.value && input.value.trim();
            if(!val) {
                // restore input visibility
                input.style.display = '';
                previewImg.style.display = 'none';
                previewImg.removeAttribute('src');
                previewInfo.textContent = '';
                if(closeBtn) closeBtn.style.display = 'none';
                return;
            }

            if(!isLikelyImageUrl(val)) {
                // show input and message
                input.style.display = '';
                previewImg.style.display = 'none';
                previewImg.removeAttribute('src');
                previewInfo.textContent = 'URL không giống ảnh';
                if(closeBtn) closeBtn.style.display = 'none';
                return;
            }

            // show loading state
            previewInfo.textContent = 'Đang tải ảnh...';
            previewImg.style.display = 'none';
            if(closeBtn) closeBtn.style.display = 'none';

            // cancel previous timeout if any
            if(loadTimeout) clearTimeout(loadTimeout);
            loadTimeout = setTimeout(function(){
                previewImg.onload = function(){
                    // hide the URL input and show only the image with close button
                    input.style.display = 'none';
                    previewImg.style.display = 'inline-block';
                    previewInfo.textContent = '';
                    if(closeBtn) closeBtn.style.display = '';
                };
                previewImg.onerror = function(){
                    // show input and error message
                    input.style.display = '';
                    previewImg.style.display = 'none';
                    previewInfo.textContent = 'Không tải được ảnh';
                    if(closeBtn) closeBtn.style.display = 'none';
                };
                previewImg.src = val;
            }, 150);
        }

        // events: input/paste/change
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
        input.addEventListener('paste', function(){ setTimeout(updatePreview, 50); });

        // close button clears the field and restores input
        if(closeBtn) closeBtn.addEventListener('click', function(){
            input.value = '';
            input.style.display = '';
            previewImg.style.display = 'none';
            previewImg.removeAttribute('src');
            previewInfo.textContent = '';
            closeBtn.style.display = 'none';
            // trigger change so form has correct value
            var e = new Event('change');
            input.dispatchEvent(e);
        });

        // initial
        updatePreview();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initThumbnailPreview);
    else initThumbnailPreview();
})();
</script>
PREVIEW;

        // Auto-fill slug from title (client-side) but allow user edits
        echo <<<'SLUGJS'
<script>
;(function(){
    function initSlugAutoFill(){
        var title = document.getElementById('post_title') || document.querySelector('input[name="post_title"]');
        var slug = document.getElementById('slug') || document.querySelector('input[name="slug"]');
        if (!title || !slug) return;

        var slugTouched = false;
        // If user types in slug field, mark touched so auto-fill stops
        slug.addEventListener('input', function(){ slugTouched = true; });

        function slugifyClient(text){
            if(!text) return '';
            // basic normalize: remove diacritics using simple map fallback
            try{
                if (typeof text.normalize === 'function') {
                    text = text.normalize('NFD').replace(/\p{Diacritic}/gu, '');
                }
            } catch(e) {
                // ignore
            }
            text = text.replace(/[^A-Za-z0-9]+/g, '-');
            text = text.replace(/^-+|-+$/g, '');
            return text.toLowerCase();
        }

        var lastGenerated = '';

        title.addEventListener('input', function(){
            if(slugTouched) return;
            var gen = slugifyClient(title.value.trim());
            // only overwrite if user hasn't typed or slug equals last generated
            if(!slug.value || slug.value === lastGenerated) {
                slug.value = gen;
                lastGenerated = gen;
                // fire change so any listeners update
                slug.dispatchEvent(new Event('change'));
            }
        });

        // also run once on load
        if(!slug.value) {
            slug.value = slugifyClient(title.value.trim());
            lastGenerated = slug.value;
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSlugAutoFill);
    else initSlugAutoFill();
})();
</script>
SLUGJS;
        // TinyMCE WYSIWYG editor for post_content (WordPress-like toolbar)
        echo <<<'TINYMCE'
<script>
(function(){
    function initEditor(){
        if (typeof tinymce === 'undefined') return;
        tinymce.init({
            selector: 'textarea[name="post_content"]',
            height: 420,
            menubar: false,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code table paste help wordcount',
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image | removeformat | code',
            branding: false,
            relative_urls: false,
            remove_script_host: false,
            convert_urls: true,
            paste_as_text: false,
            // ensure editor content is saved into textarea on form submit
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

    // load TinyMCE from CDN if not present
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
