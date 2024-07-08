<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetFieldFloat extends SugarWidgetFieldInt
{
    public function displayList(&$layout_def)
    {
        $vardef = $this->getVardef($layout_def);

        if (isset($vardef['precision'])) {
            $precision = $vardef['precision'];
        } else {
            $precision = null;
        }
        return format_number(parent::displayListPlain($layout_def), $precision, $precision);
    }

    public function displayListPlain($layout_def)
    {
        return $this->displayList($layout_def);
    }
    public function queryFilterEquals(&$layout_def)
    {
        return $this->_get_column_select($layout_def)."= ".DBManagerFactory::getInstance()->quote(unformat_number($layout_def['input_name0']))."\n";
    }
                                                                                 
    public function queryFilterNot_Equals(&$layout_def)
    {
        return $this->_get_column_select($layout_def)."!=".DBManagerFactory::getInstance()->quote(unformat_number($layout_def['input_name0']))."\n";
    }
                                                                                 
    public function queryFilterGreater(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." > ".DBManagerFactory::getInstance()->quote(unformat_number($layout_def['input_name0']))."\n";
    }
                                                                                 
    public function queryFilterLess(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." < ".DBManagerFactory::getInstance()->quote(unformat_number($layout_def['input_name0']))."\n";
    }

    public function queryFilterBetween(&$layout_def)
    {
        return $this->_get_column_select($layout_def)." BETWEEN ".DBManagerFactory::getInstance()->quote(unformat_number($layout_def['input_name0'])). " AND " . DBManagerFactory::getInstance()->quote(unformat_number($layout_def['input_name1'])) . "\n";
    }
}
