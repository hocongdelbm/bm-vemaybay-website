<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



/**
 * UndefinedVardefRule.php
 *
 * This is a utility base class to provide further refinement when converting
 * pre 5.x files to the new meta-data rules.
 *
 * @author Collin Lee
 */

require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class UndefinedVardefRule extends BaseRule
{
    public function __construct() {}


    public function parsePanels($panels, $view)
    {
        foreach ($panels as $name => $panel) {
            foreach ($panel as $rowCount => $row) {
                foreach ($row as $key => $column) {
                    if (is_array($column) && isset($column['name']) && empty($column['name'])) {
                        $panels[$name][$rowCount][$key] = '';
                    } else {
                        if (!is_array($column) && isset($column['name']) && empty($column['name'])) {
                            $panels[$name][$rowCount][$key] = '';
                        }
                    }
                } //foreach
            } //foreach
        } //foreach

        return $panels;
    }
}
