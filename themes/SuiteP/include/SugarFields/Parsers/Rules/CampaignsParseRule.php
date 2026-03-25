<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}


require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class CampaignsParseRule extends BaseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        if ($view == 'EditView') {
            $frequencyAdded = false;
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if (empty($column) && !$frequencyAdded) {
                            //Add the frequency label
                            $panels[$name][$rowCount][$key] = 'frequency';
                            $frequencyAdded = true;
                        } else {
                            if ($this->matches($column, '/^deleted$/')) {
                                //This is to fix the error where the Created By field
                                //in Campaigns EditView.html actually references the deleted field
                                //We will just remove the field since you shouldn't be able to edit this information anyway
                                $panels[$name][$rowCount][$key] = '';
                            }
                        }
                    } //foreach
                } //foreach
            } //foreach

            //If all the row/columns were taken up, then add frequency as a last row
            if (!$frequencyAdded) {
                $panels['default'][][] = 'frequency';
            }
        }
        return $panels;
    }
}
