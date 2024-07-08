<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetFieldId extends SugarWidgetReportField
{
    public function queryFilterIs($layout_def)
    {
        return $this->_get_column_select($layout_def)."='".DBManagerFactory::getInstance()->quote($layout_def['input_name0'])."'\n";
    }
}
