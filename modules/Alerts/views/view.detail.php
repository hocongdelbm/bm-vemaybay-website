<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class AlertsViewDetail extends ViewDetail
{

    function display()
    {
        $this->populateCustomFields();

        parent::display();
        $this->disPlayScript();
    }

    function populateCustomFields()
    {
        global $app_list_strings, $timedate;

        // LBL_FILENAME
        $file_name = '<div class="flex-between alert_fliename">
                        <a href="index.php?entryPoint=download&id=' . $this->bean->parent_alert_id . '&type=Alerts" class="tabDetailViewDFLink" target="_blank">'.$this->bean->filename.'</a>
                        <a href="index.php?preview=yes&entryPoint=download&id=' . $this->bean->parent_alert_id . '&type=Alerts" class="tabDetailViewDFLink" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                            </svg>
                        </a>
                    </div>';
        $this->ss->assign('CUS_ALERT_FILENAME', $file_name);

        // LBL_ALERT_PHOTO
        $alert_photo = '';
        if ($this->bean->alert_photo) {
            $alert_photo .= '<div class="flex-start alert_photo">
                                <img id="alertImage"
                                    src="index.php?entryPoint=download&id=' . $this->bean->parent_alert_id . '_alert_photo&type=Alerts"
                                    style="max-width: 100%; object-fit: contain; cursor: zoom-in;"
                                    width="500" height="auto">
                            </div>';
        }
        $this->ss->assign('CUS_ALERT_PHOTO', $alert_photo);
        //     if($this->bean->alert_photo){
        //         $alert_photo .= '<div class="flex-start alert_photo">
        //                             <img src="index.php?entryPoint=download&id=' . $this->bean->parent_alert_id . '_alert_photo&type=Alerts" style="max-width: 100%; object-fit: contain;" width="500" height="auto">
        //                         </div>';
        //     }
    }

    function disPlayScript() {
        $script = '';
        $script .= '
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>
        <script src="modules/'.$this->bean->module_dir.'/js/view.detail.js?v=1.0.0">';
        echo $script;
    }
}
