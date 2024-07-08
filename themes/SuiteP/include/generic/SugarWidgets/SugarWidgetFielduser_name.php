<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetFielduser_name extends SugarWidgetFieldname
{
    public function displayInput($layout_def)
    {
        $selected_users = empty($layout_def['input_name0']) ? '' : $layout_def['input_name0'];
        $str = '<select multiple="true" size="3" name="' . $layout_def['name'] . '[]">' . get_select_options_with_id(get_user_array(false), $selected_users) . '</select>';
        return $str;
    }
}
