<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class NotesParseRule extends BaseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        foreach ($panels as $name => $panel) {
            foreach ($panel as $rowCount => $row) {
                foreach ($row as $key => $column) {
                    if ($this->matches($column, '/^related_doc_id$/')) {
                        $panels[$name][$rowCount][$key] = 'related_doc_name';
                    } else {
                        if ($this->matches($column, '/^related_doc_rev_id$/')) {
                            $panels[$name][$rowCount][$key] = ($view == 'EditView') ? 'related_doc_rev_number' : 'related_doc_name';
                        } else {
                            if ($this->matches($column, '/^filelink$/')) {
                                $panels[$name][$rowCount][$key] = 'filename';
                            }
                        }
                    }
                } //foreach
            } //foreach
        } //foreach
        return $panels;
    }
}
