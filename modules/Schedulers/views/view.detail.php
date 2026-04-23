<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


class SchedulersViewDetail extends ViewDetail
{

    protected function _getModuleTitleListParam($browserTitle = false)
    {
        global $mod_strings;

        return "<a href='index.php?module=Schedulers&action=index'>" . $mod_strings['LBL_MODULE_TITLE'] . "</a>";
    }

    /**
     * display
     */
    public function display()
    {
        $this->bean->parseInterval();
        $this->bean->setIntervalHumanReadable();
        $this->ss->assign('JOB_INTERVAL', $this->bean->intervalHumanReadable);
        $this->bean->created_by_name = get_assigned_user_name($this->bean->created_by);
        $this->bean->modified_by_name = get_assigned_user_name($this->bean->modified_user_id);

        parent::display();
    }
}
