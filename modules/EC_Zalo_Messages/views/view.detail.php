<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_Zalo_MessagesViewDetail extends ViewDetail
{
    public function display()
    {
        global $app_strings;

        $thumb = '';
        $thumb_src = isset($this->bean->thumbnail) ? trim($this->bean->thumbnail) : '';
        if (!empty($thumb_src)) {
            $thumb_esc = htmlspecialchars($thumb_src, ENT_QUOTES, 'UTF-8');
            $thumb = '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#thumbModal" data-thumb-src="' . $thumb_esc . '">Xem ảnh</button>';
            $modal = '<div class="modal fade" id="thumbModal" tabindex="-1" aria-hidden="true">'
                . '<div class="modal-dialog modal-dialog-centered">'
                . '<div class="modal-content">'
                . '<div class="modal-body text-center">'
                . '<img id="thumbModalImg" src="" alt="thumbnail" style="max-width:100%;height:auto;display:inline-block"/>'
                . '</div>'
                . '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . $app_strings['LBL_CLOSE_BUTTON_TITLE'] . '</button></div>'
                . '</div></div></div>';

            $script = '<script>document.addEventListener("DOMContentLoaded", function(){var m=document.getElementById("thumbModal"); if(m){m.addEventListener("show.bs.modal", function(e){var btn=e.relatedTarget; if(btn){var src=btn.getAttribute("data-thumb-src"); var img=m.querySelector("#thumbModalImg"); if(img) img.src=src||"";} }); m.addEventListener("hidden.bs.modal", function(){var img=m.querySelector("#thumbModalImg"); if(img) img.removeAttribute("src"); });}});</script>';

            $thumb .= $modal . $script;
        } else {
           $noThumbTitle = isset($app_strings['LBL_NO_THUMBNAIL']) ? $app_strings['LBL_NO_THUMBNAIL'] : 'Không có ảnh';
            $thumb_esc_title = htmlspecialchars($noThumbTitle, ENT_QUOTES, 'UTF-8');
            $thumb = '<button type="button" class="btn btn-sm btn-thumb" disabled aria-disabled="true" title="' . $thumb_esc_title . '">Xem ảnh</button>';
        }

    $this->ss->assign('CUSTOM_THUMB', $thumb);

        parent::display();
    }   
}
