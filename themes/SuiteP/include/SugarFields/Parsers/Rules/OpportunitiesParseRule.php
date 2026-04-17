<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class OpportunitiesParseRule extends BaseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^type$/i')) {
                            $panels[$name][$rowCount][$key] = 'opportunity_type';
                        }
                    } //foreach
                } //foreach
            } //foreach
        } //if
        return $panels;
    }
}
