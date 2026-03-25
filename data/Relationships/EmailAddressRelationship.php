<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once("data/Relationships/M2MRelationship.php");

/**
 * Represents a many to many relationship that is table based.
 * @api
 */
class EmailAddressRelationship extends M2MRelationship
{
    /**
     * For Email Addresses, there is only a link from the left side, so we need a new add function that ignores rhs
     *
     * @param SugarBean $lhs left side bean to add to the relationship.
     * @param SugarBean $rhs right side bean to add to the relationship.
     * @param mixed $additionalFields key=>value pairs of fields to save on the relationship
     * @return boolean true if successful
     */
    public function add($lhs, $rhs, $additionalFields = array())
    {
        $lhsLinkName = $this->lhsLink;

        if (empty($lhs->$lhsLinkName) && !$lhs->load_relationship($lhsLinkName)) {
            $lhsClass = get_class($lhs);
            $GLOBALS['log']->fatal("could not load LHS $lhsLinkName in $lhsClass");
            return false;
        }

        if ($lhs->$lhsLinkName->beansAreLoaded()) {
            $lhs->$lhsLinkName->addBean($rhs);
        }

        $this->callBeforeAdd($lhs, $rhs, $lhsLinkName);

        //Many to many has no additional logic, so just add a new row to the table and notify the beans.
        $dataToInsert = $this->getRowToInsert($lhs, $rhs, $additionalFields);

        $this->addRow($dataToInsert);

        if ($this->self_referencing) {
            $this->addSelfReferencing($lhs, $rhs, $additionalFields);
        }

        if ($lhs->$lhsLinkName->beansAreLoaded()) {
            $lhs->$lhsLinkName->addBean($rhs);
        }

        $this->callAfterAdd($lhs, $rhs, $lhsLinkName);

        return true;
    }

    public function remove($lhs, $rhs)
    {
        $lhsLinkName = $this->lhsLink;

        if (!($lhs instanceof SugarBean)) {
            $GLOBALS['log']->fatal("LHS is not a SugarBean object");
            return false;
        }
        if (!($rhs instanceof SugarBean)) {
            $GLOBALS['log']->fatal("RHS is not a SugarBean object");
            return false;
        }
        if (empty($lhs->$lhsLinkName) && !$lhs->load_relationship($lhsLinkName)) {
            $GLOBALS['log']->fatal("could not load LHS $lhsLinkName");
            return false;
        }

        if (empty($_SESSION['disable_workflow']) || $_SESSION['disable_workflow'] != "Yes") {
            if (!empty($lhs->$lhsLinkName)) {
                $lhs->$lhsLinkName->load();
                $this->callBeforeDelete($lhs, $rhs, $lhsLinkName);
            }
        }

        $dataToRemove = array(
            $this->def['join_key_lhs'] => $lhs->id,
            $this->def['join_key_rhs'] => $rhs->id
        );

        $this->removeRow($dataToRemove);

        if ($this->self_referencing) {
            $this->removeSelfReferencing($lhs, $rhs);
        }

        if (empty($_SESSION['disable_workflow']) || $_SESSION['disable_workflow'] != "Yes") {
            if (!empty($lhs->$lhsLinkName)) {
                $lhs->$lhsLinkName->load();
                $this->callAfterDelete($lhs, $rhs, $lhsLinkName);
            }
        }

        return true;
    }

    /**
     * Gets the relationship role column check for the where clause
     * This overload adds additional bean check for the primary_address variable.
     * @param string $table
     * @param bool $ignore_role_filter
     * @return string
     */
    protected function getRoleWhere($table = "", $ignore_role_filter = false)
    {
        $roleCheck = parent::getRoleWhere($table, $ignore_role_filter);

        if (
            $this->def['relationship_role_column'] == 'primary_address' &&
            $this->def["relationship_role_column_value"] == '1'
        ) {
            if (empty($table)) {
                $roleCheck .= " AND bean_module";
            } else {
                $roleCheck .= " AND $table.bean_module";
            }
            $roleCheck .= " = '" . $this->getLHSModule() . "'";
        }

        return $roleCheck;
    }
}
