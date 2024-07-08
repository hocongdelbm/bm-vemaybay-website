<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
 
class SugarWidgetFieldSingleEnum extends SugarWidgetFieldEnum
{
    public function displayInput($layout_def)
    {
        global $app_list_strings;

        if (!empty($layout_def['remove_blank']) && $layout_def['remove_blank']) {
            if (is_array($layout_def['options'])) {
                $ops = $layout_def['options'];
            } elseif (isset($layout_def['options']) && isset($app_list_strings[$layout_def['options']])) {
                $ops = $app_list_strings[$layout_def['options']];
                if (array_key_exists('', $app_list_strings[$layout_def['options']])) {
                    unset($ops['']);
                }
            } else {
                $ops = array();
            }
        } else {
            $ops = $app_list_strings[$layout_def['options']];
        }
        
        $str = '<select name="' . $layout_def['name'] . '">';
        $str .= get_select_options_with_id($ops, $layout_def['input_name0']);
        $str .= '</select>';
        return $str;
    }
}
