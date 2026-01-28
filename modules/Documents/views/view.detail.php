<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */



class DocumentsViewDetail extends ViewDetail
{
    /**
     * @see SugarView::_getModuleTitleParams()
     */
    protected function _getModuleTitleParams($browserTitle = false)
    {
        $params = array();
        $params[] = $this->_getModuleTitleListParam($browserTitle);
        $params[] = $this->bean->document_name;

        return $params;
    }

    public function display()
    {
        //check to see if the file field is empty.  This should not occur and would only happen when an error has ocurred during upload, or from db manipulation of record.
        if (empty($this->bean->filename)) {
            //print error to screen
            $this->errors[] = $GLOBALS['mod_strings']['ERR_MISSING_FILE'];
            $this->displayErrors();
        }

        $this->populateCustomCode();
        parent::display();
        $this->getScripts();
    }

    private function populateCustomCode()
    {
        global $sugar_config;

        // Custom filename display with download link via proxy
        $filename_html = '';
        if (!empty($this->bean->filename)) {
            $downloadUrl = 'index.php?entryPoint=NextCloudPreview&id=' . $this->bean->id . '&download=yes';
            $filename_html = '<a href="' . $downloadUrl . '" target="_blank" class="tabDetailViewDFLink">' . $this->bean->filename . '</a>';
        }
        $this->ss->assign('CUSTOM_FILENAME', $filename_html);

        $preview_html = '';

        // Get the document revision to check MIME type
        $revision = BeanFactory::getBean('DocumentRevisions', $this->bean->document_revision_id);
        
        // Check if the file is an image based on MIME type
        if (!empty($revision->id) && !empty($revision->file_mime_type) && strpos($revision->file_mime_type, 'image/') === 0) {
            // This is an image file - display it via proxy
            $previewUrl = 'index.php?entryPoint=NextCloudPreview&id=' . $this->bean->id;
            // Render HTML - Using proxy URL to display the main file as image
            $preview_html = '<div class="preview-photo-container">
                <img id="previewImage" src="' . $previewUrl . '"
                    style="max-width: 70%; max-height: 300px; object-fit: contain; cursor: zoom-in;"
                    alt="Preview Photo"
                    onerror="this.parentElement.innerHTML=\'<div class=\\\'no-preview-photo\\\' style=\\\'color:#999;\\\'>Không thể tải ảnh (Lỗi kết nối)</div>\';">
            </div>';
        } else {
            // Not an image or no revision found - show placeholder
            $preview_html = '<div class="no-preview-photo" style="color:#999;">File không phải là hình ảnh</div>';
        }

        $this->ss->assign('PREVIEW_IMAGE_HTML', $preview_html);
    }
    //Load ViewerJS scripts (for zoom image)
    private function getScripts()
    {
        echo '
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>
            <script src="modules/' . $this->bean->module_dir . '/js/view.detail.js?v=1.0.0">
        ';
    }
}
