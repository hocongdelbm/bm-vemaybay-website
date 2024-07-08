<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetFieldVarchar extends SugarWidgetReportField
{
    public function __construct(&$layout_manager)
    {
        parent::__construct($layout_manager);
    }

    public function queryFilterEquals($layout_def)
    {
        return $this->_get_column_select($layout_def)."='".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }

    public function queryFilterNot_Equals_Str($layout_def)
    {
        return $this->_get_column_select($layout_def)."!='".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }

    public function queryFilterContains(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." LIKE '%".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."%'\n";
    }
    public function queryFilterdoes_not_contain(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." NOT LIKE '%".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."%'\n";
    }

    public function queryFilterStarts_With(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." LIKE '".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."%'\n";
    }

    public function queryFilterEnds_With(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." LIKE '%".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }

    public function queryFilterone_of(&$layout_def)
    {
        foreach ($layout_def['input_name0'] as $key => $value) {
            $layout_def['input_name0'][$key] = DBManagerFactory::getInstance()->quote($value);
        }
        return $this->_get_column_select($layout_def) . " IN ('" . implode("','", $layout_def['input_name0']) . "')\n";
    }

    public function displayInput($layout_def)
    {
        $str = '<input type="text" size="20" value="' . $layout_def['input_name0'] . '" name="' . $layout_def['name'] . '">';
        return $str;
    }
}
