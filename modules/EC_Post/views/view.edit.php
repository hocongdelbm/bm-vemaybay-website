<?php
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class EC_PostViewEdit extends ViewEdit
{
    /**
     * Display the EC_Post edit form
     * All JavaScript logic moved to js/ modules for clean code
     */
    public function display()
    {
        global $current_user;

        // ── Setup data variables ────────────────────────────────────────────
        $postId = isset($this->bean->id) ? $this->bean->id : null;
        $valCategories = isset($this->bean->categories_selector) ? $this->bean->categories_selector : '';
        $valTags = isset($this->bean->tags_selector) ? $this->bean->tags_selector : '';

        // ── 1. Multi-selector widgets ───────────────────────────────────────
        $this->setupMultiSelectorWidgets($valCategories, $valTags, $postId);

        // ── 2. Author field (read-only, auto-filled) ───────────────────────
        $this->setupAuthorField($current_user);

        // ── 3. Render the SugarCRM form ─────────────────────────────────────
        parent::display();
        $this->injectEditorLayoutCss();


        // ── 4. Load JavaScript modules (previously inline) ──────────────────
        $this->loadJavaScriptModules();
    }

    /**
     * Setup multi-selector widgets for categories and tags
     * 
     * @param string $valCategories Selected categories value
     * @param string $valTags Selected tags value
     * @param mixed $postId Post ID
     */
    private function setupMultiSelectorWidgets($valCategories, $valTags, $postId)
    {
        $widgetPath = 'custom/modules/EC_Post/widgets/EC_Post_multiSelectorWidget.php';
        $selectorHtml = '';
        $tagsHtml = '';

        if (file_exists($widgetPath)) {
            require_once($widgetPath);
            if (class_exists('EC_Post_multiSelectorWidget') && method_exists('EC_Post_multiSelectorWidget', 'render')) {
                $selectorHtml = EC_Post_multiSelectorWidget::render('categories_selector', $valCategories, 'categories', $postId);
                $tagsHtml = EC_Post_multiSelectorWidget::render('tags_selector', $valTags, 'tags', $postId);
            }
        }

        $this->ss->assign('CATEGORIES_SELECTOR', $selectorHtml);
        $this->ss->assign('TAGS_SELECTOR', $tagsHtml);
    }

    /**
     * Setup author field (read-only, auto-filled with current user)
     * 
     * IMPORTANT:
     * - CREATE mode: Display name of user creating the post (current_user)
     * - EDIT mode: Display name of ORIGINAL author (from bean->author_id), NOT current_user
     * 
     * @param object $current_user Current logged-in user object
     */
    private function setupAuthorField($current_user)
    {
        $authorHtml = '';

        try {
            $authorId = '';
            $authorName = '';
            $authorUser = null;

            if (!empty($current_user) && is_object($current_user)) {

                // EDIT MODE: Load the ORIGINAL author
                if (!empty($this->bean->id)) {
                    $authorId = $this->bean->created_by;
                    if (!empty($authorId)) {
                        $authorUser = BeanFactory::getBean('Users', $authorId);
                    }
                }

                // CREATE MODE: Use current user as author
                else {
                    $authorId = $current_user->id;
                    $this->bean->author_id = $authorId;
                    $authorUser = $current_user;
                }

                // Get display name
                if (!empty($authorUser) && is_object($authorUser)) {
                    if (method_exists($authorUser, 'get_display_name')) {
                        $authorName = $authorUser->get_display_name();
                    } elseif (!empty($authorUser->full_name)) {
                        $authorName = $authorUser->full_name;
                    } elseif (!empty($authorUser->user_name)) {
                        $authorName = $authorUser->user_name;
                    }
                }
            }

            $authorHtml .= "<div class='ec-author-readonly' style='padding:6px 8px;border:1px solid #e1e1e1;background:#fafafa;border-radius:4px;'>"
                . htmlspecialchars($authorName, ENT_QUOTES)
                . "</div>";

        } catch (Exception $e) {
            $authorHtml = '';
        }

        $this->ss->assign('AUTHOR_FIELD', $authorHtml);
    }
    private function loadJavaScriptModules()
    {
        $jsModuleDir = 'modules/EC_Post/js';

        $modules = array(
            'app.js',
            'taxonomy-ajax.js',
            'tom-select-init.js',
            'quick-add-buttons.js',
            'thumbnail-preview.js',
            'slug-autofill.js',
            'editor-tinymce.js'
        );

        echo "\n<!-- ========== EC_Post JavaScript Modules (Clean Code) ========== -->\n";

        foreach ($modules as $module) {
            $modulePath = $jsModuleDir . '/' . $module;

            if (file_exists($modulePath)) {
                // Use MD5 hash for cache busting
                $fileHash = md5_file($modulePath);
                $version = substr($fileHash, 0, 8);
                $moduleUrl = $modulePath . '?v=' . $version;

                echo "<script src=\"" . htmlspecialchars($moduleUrl, ENT_QUOTES) . "\"></script>\n";
            } else {
                // Log warning if module not found
                echo "<!-- WARNING: Module not found - " . htmlspecialchars($modulePath) . " -->\n";
            }
        }

        echo "<!-- ========== End EC_Post Modules ========== -->\n\n";
    }
    private function injectEditorLayoutCss()
    {
        echo '
    <style>

    /* ===== POST CONTENT FULL WIDTH ===== */

    .label[data-label="LBL_POST_CONTENT"]{
        display:none !important;
    }

    .label[data-label="LBL_POST_CONTENT"]
    + .edit-view-field{
        flex:0 0 100% !important;
        max-width:100% !important;
        width:100% !important;
    }

    .edit-view-field[field="post_content"]{
        flex:0 0 100% !important;
        max-width:100% !important;
        width:100% !important;
    }

    .edit-view-field[field="post_content"] .tox-tinymce{
        min-height:700px !important;
    }

    .edit-view-field[field="post_content"] .tox-editor-container{
        min-height:700px !important;
    }

    /* giống WordPress */

    .edit-view-field[field="post_content"]{
        background:#fff;
        border-radius:6px;
    }

    </style>
    ';
    }
}