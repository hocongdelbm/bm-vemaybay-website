<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



/**
 * ParseRule.php
 *
 * This is a utility base class to provide further refinement when converting
 * pre 5.x files to the new meta-data rules.

 * @author Collin Lee
 */
class ParseRules
{
    public function __construct() {}

    /**
     * getRules
     * Return Array of rules to run
     *
     */
    public function getRules()
    {
        $rules = array();
        $rules[] = array("class" => "UndefinedVardefRule", "file" => "include/SugarFields/Parsers/Rules/UndefinedVardefRule.php");
        $rules[] = array("class" => "VariableCleanupRule", "file" => "include/SugarFields/Parsers/Rules/VariableCleanupRule.php");
        $rules[] = array("class" => "VariableSubstitutionRule", "file" => "include/SugarFields/Parsers/Rules/VariableSubstitutionRule.php");
        $rules[] = array("class" => "AddressRule", "file" => "include/SugarFields/Parsers/Rules/AddressRule.php");
        $rules[] = array("class" => "EmailAddressRule", "file" => "include/SugarFields/Parsers/Rules/EmailAddressRule.php");
        $rules[] = array("class" => "EmptyRowRule", "file" => "include/SugarFields/Parsers/Rules/EmptyRowRule.php");
        return $rules;
    }
}
