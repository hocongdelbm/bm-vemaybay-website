<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class ContractsParseRule extends BaseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        if ($view == 'EditView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^expiration_notice_date$/')) {
                            $panels[$name][$rowCount][$key] = 'expiration_notice';
                        }
                    } //foreach
                } //foreach
            } //foreach
        } //if
        return $panels;
    }
}
