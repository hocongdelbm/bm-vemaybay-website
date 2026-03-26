<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class ContactsParseRule extends BaseRule
{
    public function __construct() {}





    public function preParse($panels, $view)
    {
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^(last_)?name$/')) {
                            $panels[$name][$rowCount][$key] = 'full_name';
                        }
                    } //foreach
                } //foreach
            } //foreach
        }

        return $panels;
    }

    public function parsePanels(&$panels, $view)
    {
        if ($view == 'EditView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/portal_password1/si')) {
                            $panels[$name][$rowCount][$key] = array('name' => 'portal_password1', 'type' => 'password', 'customCode' => '<input id="portal_password1" name="portal_password1" type="password" size="32" maxlength="32" value="{$fields.portal_password.value}">', 'label' => 'LBL_PORTAL_PASSWORD');
                        }
                    } //foreach
                } //foreach
            } //foreach
        }
        return $panels;
    }
}
