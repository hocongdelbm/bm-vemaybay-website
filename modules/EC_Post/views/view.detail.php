<?php
require_once('include/MVC/View/views/view.detail.php');

class EC_PostViewDetail extends ViewDetail
{
    /**
     * Display the EC_Post detail view
     * 
     * Loads and displays:
     * - Custom taxonomy display (categories, tags)
     * - Formatted post content (HTML from WYSIWYG editor)
     * - Thumbnail with modal preview
     * - Author name (NOT just ID)
     */
    public function display()
    {
        // ──────── LOAD CUSTOM CSS ────────
        $this->getStyles();

        // Get post ID from bean or URL
        $postId = isset($this->bean->id) ? $this->bean->id : (isset($_GET['record']) ? $_GET['record'] : null);

        // ═══════════════════════════════════════════════════════════
        // DISPLAY CATEGORIES & TAGS
        // ═══════════════════════════════════════════════════════════
        $displayCategories = '';
        $displayTags = '';
        $helperPath = 'custom/modules/EC_Post/DisplayTaxonomyHelper.php';
        if (file_exists($helperPath)) {
            require_once($helperPath);
            if (class_exists('EC_Post_DisplayTaxonomyHelper') && method_exists('EC_Post_DisplayTaxonomyHelper', 'render')) {
                $displayCategories = EC_Post_DisplayTaxonomyHelper::render($postId, 'categories');
                $displayTags = EC_Post_DisplayTaxonomyHelper::render($postId, 'tags');
            }
        }

        $this->ss->assign('CATEGORIES_DISPLAY', $displayCategories);
        $this->ss->assign('TAGS_DISPLAY', $displayTags);

        // ═══════════════════════════════════════════════════════════
        // DISPLAY AUTHOR NAME (NOT just ID) ← NEW!
        // ═══════════════════════════════════════════════════════════
        $this->setupAuthorDisplay();

        // ═══════════════════════════════════════════════════════════
        // DISPLAY POST CONTENT (Formatted HTML)
        // ═══════════════════════════════════════════════════════════
        $rawContent = isset($this->bean->post_content) ? $this->bean->post_content : '';
        $decoded = html_entity_decode($rawContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeContent = preg_replace('#<\s*script[^>]*>.*?<\s*/\s*script\s*>#is', '', $decoded);
        $contentHtml = '<div class="ec-post-content">' . $safeContent . '</div>';
        $this->ss->assign('POST_CONTENT', $contentHtml);

        // ═══════════════════════════════════════════════════════════
        // DISPLAY THUMBNAIL with Modal
        // ═══════════════════════════════════════════════════════════
        $thumb = '';
        $thumb_src = isset($this->bean->thumbnail_url) ? trim($this->bean->thumbnail_url) : '';
        if (!empty($thumb_src)) {
            $thumb_esc = htmlspecialchars($thumb_src, ENT_QUOTES, 'UTF-8');
            $modalId = 'ec_post_thumb_modal';
            $thumb = '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#' . $modalId . '" data-thumb-src="' . $thumb_esc . '">Xem ảnh</button>';
            $modal = '<div class="modal fade" id="' . $modalId . '" tabindex="-1" aria-hidden="true">'
                . '<div class="modal-dialog modal-dialog-centered">'
                . '<div class="modal-content">'
                . '<div class="modal-body text-center">'
                . '<img id="' . $modalId . '_img" src="" alt="thumbnail" style="max-width:100%;height:auto;display:inline-block"/>'
                . '</div>'
                . '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . (isset($GLOBALS['app_strings']['LBL_CLOSE_BUTTON_TITLE']) ? $GLOBALS['app_strings']['LBL_CLOSE_BUTTON_TITLE'] : 'Đóng') . '</button></div>'
                . '</div></div></div>';

            $script = '<script>document.addEventListener("DOMContentLoaded", function(){var m=document.getElementById("' . $modalId . '"); if(m){m.addEventListener("show.bs.modal", function(e){var btn=e.relatedTarget; if(btn){var src=btn.getAttribute("data-thumb-src"); var img=m.querySelector("#' . $modalId . '_img"); if(img) img.src=src||"";} }); m.addEventListener("hidden.bs.modal", function(){var img=m.querySelector("#' . $modalId . '_img"); if(img) img.removeAttribute("src"); });}});</script>';

            $thumb .= $modal . $script;
        } else {
            $noThumbTitle = isset($GLOBALS['app_strings']['LBL_NO_THUMBNAIL']) ? $GLOBALS['app_strings']['LBL_NO_THUMBNAIL'] : 'Không có ảnh';
            $thumb_esc_title = htmlspecialchars($noThumbTitle, ENT_QUOTES, 'UTF-8');
            $thumb = '<button type="button" class="btn btn-sm btn-thumb" disabled aria-disabled="true" title="' . $thumb_esc_title . '">Xem ảnh</button>';
        }

        $this->ss->assign('CUSTOM_THUMB', $thumb);

        parent::display();
    }

    /**
     * Display author name (NOT just author_id)
     * 
     * Loads the user object from author_id and displays the full name
     * Instead of just showing the raw user ID
     */
    private function setupAuthorDisplay()
    {
        $authorDisplay = '';

        try {
            // Get author_id from the post
            $authorId = isset($this->bean->author_id) ? $this->bean->author_id : '';

            if (!empty($authorId)) {
                // Load the user object by ID
                $authorUser = BeanFactory::getBean('Users', $authorId);

                if (!empty($authorUser) && is_object($authorUser)) {
                    // Get display name
                    $authorName = '';
                    
                    if (method_exists($authorUser, 'get_display_name')) {
                        $authorName = $authorUser->get_display_name();
                    } elseif (!empty($authorUser->full_name)) {
                        $authorName = $authorUser->full_name;
                    } elseif (!empty($authorUser->user_name)) {
                        $authorName = $authorUser->user_name;
                    }

                    $authorDisplay = htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8');
                }
            }
        } catch (Exception $e) {
            // Silent fail - if author not found, show empty
            $authorDisplay = '';
        }

        // Assign to Smarty for display in DetailView template
        $this->ss->assign('AUTHOR_DISPLAY', $authorDisplay);
    }

    /**
     * Load custom CSS for EC_Post DetailView
     */
    private function getStyles()
    {
        echo "<link rel='stylesheet' href='modules/EC_Post/css/view.detail.css?v=1.0.0'>";
    }
}