<?php
require_once('include/MVC/View/SugarView.php');
require_once('include/MVC/Controller/SugarController.php');

class ViewClassic extends SugarView
{
    /**
     * @see SugarView::SugarView()
     */
    public function __construct(
        $bean = null,
        $view_object_map = array()
    ) {
        parent::__construct();
        $this->type = $this->action;
    }

    /**
     * @see SugarView::display()
     */
    public function display()
    {
        if (($this->bean instanceof SugarBean) && isset($this->view_object_map['remap_action']) && !$this->bean->ACLAccess($this->view_object_map['remap_action'])) {
            ACLController::displayNoAccess(true);
            return false;
        }
        // Call SugarController::getActionFilename to handle case sensitive file names
        $file = SugarController::getActionFilename($this->action);
        if (file_exists('custom/modules/' . $this->module . '/' . $file . '.php')) {
            $this->includeClassicFile('custom/modules/' . $this->module . '/' . $file . '.php');
            return true;
        } elseif (file_exists('modules/' . $this->module . '/' . $file . '.php')) {
            $this->includeClassicFile('modules/' . $this->module . '/' . $file . '.php');
            return true;
        }
        return false;
    }
}
