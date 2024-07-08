<?php

class ACLRolesViewList extends ViewList
{
    public function preDisplay()
    {
        //bug #46690: Developer Access to Users/Teams/Roles
        if (!$GLOBALS['current_user']->isAdminForModule('Users') && !$GLOBALS['current_user']->isDeveloperForModule('Users')) {
            sugar_die('No Access');
        }

        $this->lv = new ListViewSmarty();
        $this->lv->export = false;
        $this->lv->showMassupdateFields = false;
    }
}
