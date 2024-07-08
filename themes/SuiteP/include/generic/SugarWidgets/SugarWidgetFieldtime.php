<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
class SugarWidgetFieldTime extends SugarWidgetFieldDateTime
{
    public function displayList(&$layout_def)
    {
        global $timedate;
        // i guess qualifier and column_function are the same..
        if (! empty($layout_def['column_function'])) {
            $func_name = 'displayList'.$layout_def['column_function'];
            if (method_exists($this, $func_name)) {
                return $this->$func_name($layout_def)." \n";
            }
        }
                
        // Get the date context of the time, important for DST
        $layout_def_date = $layout_def;
        $layout_def_date['name'] = str_replace('time', 'date', $layout_def_date['name']);
        $date = $this->displayListPlain($layout_def_date);
                
        $content = $this->displayListPlain($layout_def);
                
        if (!empty($date)) { // able to get the date context of the time
            $td = explode(' ', $timedate->to_display_date_time($date . ' ' . $content));
            return $td[1];
        } else { // assume there is no time context
            return $timedate->to_display_time($content);
        }
    }
}
