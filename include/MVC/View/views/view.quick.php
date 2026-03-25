<?php


/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/MVC/View/views/view.detail.php');

class ViewQuick extends ViewDetail
{
    public $type = 'detail';

    public function __construct()
    {
        parent::__construct();
        $this->options['show_subpanels'] = false;
        $this->options['show_title'] = false;
        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
    }




    public function display()
    {
        $this->dv->showVCRControl = false;
        $this->dv->th->ss->assign('hideHeader', true);
        if (empty($this->bean->id)) {
            global $app_strings;
            sugar_die($app_strings['ERROR_NO_RECORD']);
        }
        $this->dv->process();
        ob_clean();
        echo json_encode(array('title' => $this->bean->name, 'url' => 'index.php?module=' . $this->bean->module_dir . '&action=DetailView&record=' . $this->bean->id, 'html' => $this->dv->display(false)));
    }
}
