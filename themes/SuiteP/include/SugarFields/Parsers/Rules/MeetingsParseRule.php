<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/ActivitiesParseRule.php');

class MeetingsParseRule extends ActivitiesParseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        $panels = parent::preParse($panels, $view);
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        //Fix issue of not having parent_name in Meetings vardefs.php in pre 5.x versions
                        if ($this->matches($column, '/^tag.parent$/') || $this->matches($column, '/^parent_id$/')) {
                            $panels[$name][$rowCount][$key] = 'parent_name';
                        }
                    } //foreach
                } //foreach
            } //foreach
        }
        return $panels;
    }
}
