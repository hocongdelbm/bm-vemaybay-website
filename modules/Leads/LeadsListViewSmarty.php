<?php
require_once('include/ListView/ListViewSmarty.php');
require_once('modules/AOS_PDF_Templates/formLetter.php');


class LeadsListViewSmarty extends ListViewSmarty
{
    public function __construct()
    {
        parent::__construct();
        $this->targetList = true;
    }



    /**
     *
     * @param file $file Template file to use
     * @param array $data from ListViewData
     * @param string $htmlVar the corresponding html public in xtpl per row
     * @return bool|void
     */
    public function process($file, $data, $htmlVar)
    {
        $configurator = new Configurator();
        if ($configurator->isConfirmOptInEnabled()) {
            $this->actionsMenuExtraItems[] = $this->buildSendConfirmOptInEmailToPersonAndCompany();
        }

        $ret = parent::process($file, $data, $htmlVar);

        if (!ACLController::checkAccess($this->seed->module_dir, 'export', true) || !$this->export) {
            $this->ss->assign('exportLink', $this->buildExportLink());
        }

        return $ret;
    }

    public function buildExportLink($id = 'export_link')
    {
        $script = "";
        if (ACLController::checkAccess($this->seed->module_dir, 'export', true)) {
            if ($this->export) {
                $script = parent::buildExportLink($id);
            }
        }

        return formLetter::LVSmarty().$script;
    }
}
