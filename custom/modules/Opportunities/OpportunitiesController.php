<?php 
require_once 'include/MVC/Controller/SugarController.php';

class OpportunitiesController extends SugarController
{
    public function action_save()
    {
        // Call parent action_save function
        parent::action_save();
        
        // Refresh session to prevent automatic logout
        $url = "index.php?module=Opportunities&action=EditView&record=".$this->bean->id;
        $this->view = 'edit';
        $this->view_object_map['bean'] = $this->bean;
        $this->set_redirect($url);
        SugarApplication::redirect($url);
    }
}
