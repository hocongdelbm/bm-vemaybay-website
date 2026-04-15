<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelActivitiesStatusField extends SugarWidgetField
{
    public function displayList(&$layout_def)
    {
        global $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);
        
        $module = empty($layout_def['module']) ? '' : $layout_def['module'];
        
        if (isset($layout_def['varname'])) {
            $key = strtoupper($layout_def['varname']);
        } else {
            $key = $this->_get_column_alias($layout_def);
            $key = strtoupper($key);
        }

        $value = $layout_def['fields'][$key];
        // cn: bug 5813, removing double-derivation of lang-pack value
        return $value;
    }
}
