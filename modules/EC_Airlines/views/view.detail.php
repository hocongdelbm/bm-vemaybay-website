<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class EC_AirlinesViewDetail extends ViewDetail
{
    public function display()
    {
        // $this->bean->importFromJsonFileAirlines();
        $this->populateCustomFields();
        parent::display();
    }

    private function populateCustomFields()
    {
        // LBL_LOGO
        if ($this->bean->logo) {
            $logo = '<img src="' . htmlspecialchars($this->bean->logo, ENT_QUOTES) . '" alt="logo"
                class="img-thumbnail" style="max-width:150px;max-height:80px;object-fit:contain;">';
        } else {
            $logo = '<span class="text-muted">&mdash;</span>';
        }
        $this->ss->assign('CUS_LOGO', $logo);
    }
}
