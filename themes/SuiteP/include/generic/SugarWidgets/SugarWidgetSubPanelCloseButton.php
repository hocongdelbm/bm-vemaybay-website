<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

//TODO Rename this to close button field
class SugarWidgetSubPanelCloseButton extends SugarWidgetField
{
    public function displayList(&$layout_def)
    {
        global $app_strings;
        global $subpanel_item_count;
        $return_module = $_REQUEST['module'];
        $return_id = $_REQUEST['record'];
        $module_name = $layout_def['module'];
        $record_id = $layout_def['fields']['ID'];
        $unique_id = $layout_def['subpanel_id']."_close_".$subpanel_item_count; //bug 51512

        // calls and meetings are held.
        $new_status = 'Held';
        
        switch ($module_name) {
            case 'Tasks':
                $new_status = 'Completed';
                break;
        }
        
        if ($layout_def['EditView']) {
            $html = "<a id=\"$unique_id\" onclick='SUGAR.util.closeActivityPanel.show(\"$module_name\",\"$record_id\",\"$new_status\",\"subpanel\",\"{$layout_def['subpanel_id']}\");' >".$app_strings['LNK_CLOSE']."</a>";
            return $html;
        } else {
            return '';
        }
    }
}
