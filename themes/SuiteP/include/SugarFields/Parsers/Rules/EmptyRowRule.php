<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



/**
 * EmptyRowRule.php
 *
 * This is a utility base class to provide further refinement when converting
 * pre 5.x files to the new meta-data rules.  This rule goes through the panels
 * and deletes rows for which there are no fields.
 *
 * @author Collin Lee
 */

require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class EmptyRowRule extends BaseRule
{
    public function __construct() {}


    public function parsePanels($panels, $view)
    {
        foreach ($panels as $name => $panel) {
            foreach ($panel as $rowCount => $row) {
                $emptyCount = 0;

                foreach ($row as $key => $column) {
                    if (is_array($column) && (!isset($column['name']) || empty($column['name']))) {
                        $emptyCount++;
                    } else {
                        if (!is_array($column) && (!isset($column) || empty($column))) {
                            $emptyCount++;
                        }
                    }
                } //foreach

                // If we have unset everything, then just remove the whole row entirely
                if ($emptyCount == count($row)) {
                    unset($panels[$name][$rowCount]);
                    continue;
                } else {
                    if (count($row) > 2) {
                        foreach ($row as $key => $column) {
                            if (empty($column) || $column == '') {
                                unset($panels[$name][$rowCount][$key]);
                            }
                        }
                    }
                }
            } //foreach
        } //foreach

        return $panels;
    }
}
