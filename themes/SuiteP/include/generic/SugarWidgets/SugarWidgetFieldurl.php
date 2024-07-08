<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class SugarWidgetFieldURL extends SugarWidgetFieldVarchar
{
    /* Display item as link
     * @param array $layout_def definition of field which we want to display as link
     * @return string html code
     */
    public function displayList(&$layout_def)
    {
        $urlValue = trim($this->_get_list_value($layout_def));
        return '<a target="_blank" href="' . $urlValue . '">' . $urlValue . "</a>";
    }
}
