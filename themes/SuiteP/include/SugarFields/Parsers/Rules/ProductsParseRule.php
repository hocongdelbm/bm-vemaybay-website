<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class ProductsParseRule extends BaseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^url$/i')) {
                            $panels[$name][$rowCount][$key] = 'website';
                        } else {
                            if ($this->matches($column, '/^manufacturer$/i')) {
                                $panels[$name][$rowCount][$key] = 'manufacturer_name';
                            } else {
                                if ($this->matches($column, '/^category$/i')) {
                                    $panels[$name][$rowCount][$key] = 'category_name';
                                } else {
                                    if ($this->matches($column, '/^type$/i')) {
                                        $panels[$name][$rowCount][$key] = 'type_name';
                                    }
                                }
                            }
                        }
                    } //foreach
                } //foreach
            } //foreach
        } //if
        return $panels;
    }
}
