<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class BugsParseRule extends BaseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^release$/')) {
                            $panels[$name][$rowCount][$key] = 'found_in_release';
                        }
                    } //foreach
                } //foreach
            } //foreach
        } //if
        return $panels;
    }
}
