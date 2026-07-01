<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class EC_AirlinesViewEdit extends ViewEdit
{
    public function display()
    {
        $this->populateCustomFields();
        parent::display();
        $this->getScripts();
    }

    private function populateCustomFields()
    {
        // LBL_LOGO: preview + upload (vnbackup/NextCloud) + remove
        $logoValue = htmlspecialchars($this->bean->logo, ENT_QUOTES);
        $hasLogo = !empty($this->bean->logo);

        $widget = '<div class="ec-airlines-logo-field">
            <img id="ec_airlines_logo_preview" src="' . $logoValue . '" alt="logo"
                class="img-thumbnail mb-2 ' . ($hasLogo ? '' : 'd-none') . '"
                style="max-width:150px;max-height:80px;object-fit:contain;">
            <input type="hidden" id="logo" name="logo" value="' . $logoValue . '">
            <input type="file" id="ec_airlines_logo_file" accept="image/*" class="d-none">
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="ec_airlines_logo_choose">Chọn ảnh</button>
                <button type="button" class="btn btn-outline-danger btn-sm ' . ($hasLogo ? '' : 'd-none') . '" id="ec_airlines_logo_remove">Xóa</button>
                <span id="ec_airlines_logo_status" class="text-muted ms-2"></span>
            </div>
        </div>';

        $this->ss->assign('CUS_LOGO_EDIT', $widget);
    }

    private function getScripts()
    {
        echo '<script src="modules/EC_Airlines/js/view.edit.js?v=1.0.0"></script>';
    }
}
