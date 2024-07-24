<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/generic/SugarWidgets/SugarWidgetFieldvarchar.php');

class SugarWidgetFieldText extends SugarWidgetFieldVarchar
{
    public function __construct(&$layout_manager)
    {
        parent::__construct($layout_manager);
    }

    public function queryFilterEquals($layout_def)
    {
        return $this->reporter->db->convert($this->_get_column_select($layout_def), "text2char").
            " = ".$this->reporter->db->quoted($layout_def['input_name0']);
    }

    public function queryFilterNot_Equals_Str($layout_def)
    {
        $column = $this->_get_column_select($layout_def);
        return "($column IS NULL OR ". $this->reporter->db->convert($column, "text2char")." != ".
            $this->reporter->db->quoted($layout_def['input_name0']).")";
    }

    public function queryFilterNot_Empty($layout_def)
    {
        $column = $this->_get_column_select($layout_def);
        return "($column IS NOT NULL AND ".$this->reporter->db->convert($column, "length")." > 0)";
    }

    public function queryFilterEmpty($layout_def)
    {
        $column = $this->_get_column_select($layout_def);
        return "($column IS NULL OR ".$this->reporter->db->convert($column, "length")." = 0)";
    }

    public function displayList(&$layout_def)
    {
        return nl2br(parent::displayListPlain($layout_def));
    }
}
