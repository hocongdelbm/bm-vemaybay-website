<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetSubPanelConcat extends SugarWidgetField
{
    public function displayList($layout_def)
    {
        $value='';
        if (isset($layout_def['source']) and is_array($layout_def['source']) and isset($layout_def['fields']) and is_array($layout_def['fields'])) {
            foreach ($layout_def['source'] as $field) {
                if (isset($layout_def['fields'][strtoupper($field)])) {
                    $value.=$layout_def['fields'][strtoupper($field)];
                } else {
                    $value.=$field;
                }
            }
        }
        return $value;
    }
}
