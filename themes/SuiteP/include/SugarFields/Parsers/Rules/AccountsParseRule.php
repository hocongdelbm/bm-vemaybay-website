<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class AccountsParseRule extends BaseRule
{
    public function preParse($panels, $view)
    {
        if ($view == 'DetailView') {
            foreach ($panels as $name => $panel) {
                foreach ($panel as $rowCount => $row) {
                    foreach ($row as $key => $column) {
                        if ($this->matches($column, '/^parent_id$/')) {
                            $panels[$name][$rowCount][$key] = 'parent_name';
                        } else {
                            if ($this->matches($column, '/_address_(street|country)$/') && is_array($column) && isset($column['customCode'])) {
                                if (preg_match('/\{\$fields\.push_contacts_(billing|shipping)\.value\}/', $column['customCode'], $m)) {
                                    $column['customCode'] = str_replace('{$fields.push_contacts_' . $m[1] . '.value}', '{$custom_code_' . $m[1] . '}', $column['customCode']);
                                    $panels[$name][$rowCount][$key] = $column;
                                }
                            }
                        }
                    } //foreach
                } //foreach
            } //foreach
        }
        return $panels;
    }
}
