<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class ACLRolesController extends SugarController
{
    // Maps action=DisableRole → view.disablerole.php
    public function action_disablerole()
    {
        $this->view = 'disablerole';
    }
}
