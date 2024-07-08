<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


class SugarWidgetFieldInt extends SugarWidgetReportField
{
    public function displayList(&$layout_def)
    {
        return $this->displayListPlain($layout_def);
    }

    public function queryFilterEquals(&$layout_def)
    {
        return $this->_get_column_select($layout_def)."= '".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }

    public function queryFilterNot_Equals(&$layout_def)
    {
        return $this->_get_column_select($layout_def)."!='".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }

    public function queryFilterGreater(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." > '".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }

    public function queryFilterLess(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." < '".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }

    public function queryFilterBetween(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." BETWEEN '".DBManagerFactory::getInstance()->quote($layout_def['input_name0']). "' AND '" . DBManagerFactory::getInstance()->quote($layout_def['input_name1']) . "'\n";
    }

    public function queryFilterStarts_With(&$layout_def)
    {
        return $this->queryFilterEquals($layout_def);
    }

    public function displayInput($layout_def)
    {
        return '<input type="text" size="20" value="' . $layout_def['input_name0'] . '" name="' . $layout_def['name'] . '">';
    }
 
    public function display($layout_def)
    {
        //Bug40995
        if (isset($obj->layout_manager->defs['reporter']->focus->field_name_map[$layout_def['name']]['precision'])) {
            $precision=$obj->layout_manager->defs['reporter']->focus->field_name_map[$layout_def['name']]['precision'];
            $layout_def['precision']=$precision;
        }
        //Bug40995
        return parent::display($layout_def);
    }
}
