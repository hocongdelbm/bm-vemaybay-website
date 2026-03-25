<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/ActivitiesParseRule.php');

class CallsParseRule extends ActivitiesParseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        $panels = parent::preParse($panels, $view);
        if ($view == 'EditView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^direction$/si')) {
                            $panels[$name][$rowCount][$key] = 'status';
                        }
                    } //foreach
                } //foreach
            } //foreach
        } //if
        return $panels;
    }

    public function parsePanels($panels, $view)
    {
        $panels = parent::parsePanels($panels, $view);
        return $panels;
    }
}
