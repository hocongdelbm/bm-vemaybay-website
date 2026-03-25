<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



/**
 * VariableCleanupRule.php
 *
 * This is a utility base class to provide further refinement when converting
 * pre 5.x files to the new meta-data rules.
 *
 * @author Collin Lee
 */

require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class VariableCleanupRule extends BaseRule
{
    public function __construct() {}

    public function parsePanels($panels, $view)
    {
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        //This converts variable ended with "_c_checked" to just "_c" (for checkboxes in DetailView)
                        if (!is_array($column) && isset($column) && preg_match('/(.*?)_c_checked$/s', $column, $matches)) {
                            if (count($matches) == 2) {
                                $panels[$name][$rowCount][$key] = $matches[1] . "_c";
                            }
                        } else {
                            if ($this->matches($column, '/^parent_id$/si')) {
                                $panels[$name][$rowCount][$key] = '';
                            } else {
                                if ($this->matches($column, '/^assigned_user_id$/si')) {
                                    $panels[$name][$rowCount][$key] = '';
                                }
                            }
                        }
                    } //foreach
                } //foreach
            } //foreach
        } else {
            if ($view == 'EditView') {
                foreach ($panels as $name => $panel) {
                    foreach ($panel as $rowCount => $row) {
                        foreach ($row as $key => $column) {
                            if ($this->matches($column, '/^(.*?)_c\[\]$/s')) {
                                //This converts multienum variables named with [] suffix back to normal and removes custom code
                                $val = $this->getMatch($column, '/^(.*?)_c\[\]$/s');
                                $panels[$name][$rowCount][$key] = $val[1] . '_c';
                            } else {
                                if ($this->matches($column, '/^parent_id$/si')) {
                                    //Remove parent_id field (replaced with parent_name from master copy)
                                    $panels[$name][$rowCount][$key] = '';
                                } else {
                                    if ($this->matches($column, '/^assigned_user_id$/si')) {
                                        //Remove assigned_user_id field (replaced with assigned_user_name from master copy)
                                        $panels[$name][$rowCount][$key] = '';
                                    } else {
                                        if ($this->matches($column, '/^RADIOOPTIONS_/si')) {
                                            //This converts radioenum variables
                                            $val = $this->getMatch($column, '/^RADIOOPTIONS_(.*)?$/si');
                                            $panels[$name][$rowCount][$key] = $val[1];
                                        }
                                    }
                                }
                            }
                        } //foreach
                    } //foreach
                } //foreach
            }
        }

        return $panels;
    }
}
