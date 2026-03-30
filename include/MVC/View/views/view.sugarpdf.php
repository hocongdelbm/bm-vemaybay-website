<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}




require_once('include/Sugarpdf/SugarpdfFactory.php');

class ViewSugarpdf extends SugarView
{
    public $type = 'sugarpdf';
    /**
     * It is set by the "sugarpdf" request parameter and it is use by SugarpdfFactory to load the good sugarpdf class.
     * @var String
     */
    public $sugarpdf = 'default';
    /**
     * The sugarpdf object (Include the TCPDF object).
     * The atributs of this object are destroy in the output method.
     * @var Sugarpdf object
     */
    public $sugarpdfBean = null;


    public function __construct()
    {
        parent::__construct();


        if (isset($_REQUEST["sugarpdf"])) {
            $this->sugarpdf = $_REQUEST["sugarpdf"];
        } else {
            if (!isset($_REQUEST['module'])) {
                LoggerManager::getLogger()->warn('Undefined index: module');
            }

            if (!isset($_REQUEST['record'])) {
                LoggerManager::getLogger()->warn('Undefined index: record');
            }

            header('Location:index.php?module=' . (isset($_REQUEST['module']) ? $_REQUEST['module'] : null) . '&action=DetailView&record=' . (isset($_REQUEST['record']) ? $_REQUEST['record'] : null));
        }
    }




    public function preDisplay()
    {
        $this->sugarpdfBean = SugarpdfFactory::loadSugarpdf($this->sugarpdf, $this->module, $this->bean, $this->view_object_map);

        // ACL control
        if (!empty($this->bean) && !$this->bean->ACLAccess($this->sugarpdfBean->aclAction)) {
            ACLController::displayNoAccess(true);
            sugar_cleanup(true);
        }

        if (isset($this->errors)) {
            $this->sugarpdfBean->errors = $this->errors;
        }
    }

    public function display()
    {
        $this->sugarpdfBean->process();
        $this->sugarpdfBean->Output($this->sugarpdfBean->fileName, 'I');
    }
}
