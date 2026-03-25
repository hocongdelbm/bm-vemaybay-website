<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class ActivitiesParseRule extends BaseRule
{
    public function preParse($panels, $view)
    {
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^duration_minutes$/')) {
                            $panels[$name][$rowCount][$key] = 'duration_hours';
                        } else {
                            if ($this->matches($column, '/^time_start$/')) {
                                $panels[$name][$rowCount][$key] = 'date_start';
                            }
                        }
                    } //foreach
                } //foreach
            } //foreach
        }
        return $panels;
    }

    public function parsePanels($panels, $view)
    {
        foreach ($panels as $name => $panel) {
            foreach ($panel as $rowCount => $row) {
                foreach ($row as $key => $column) {
                    if ($this->matches($column, '/^duration_minutes$/si')) {
                        $panels[$name][$rowCount][$key] = '';
                    }
                } //foreach
            } //foreach
        } //foreach
        return $panels;
    }
}
