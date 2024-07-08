<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/generic/SugarWidgets/SugarWidgetField.php');

class SugarWidgetSubPanelEditSecurityGroupUserButton extends SugarWidgetField
{
    public function displayHeaderCell($layout_def)
    {
        return '&nbsp;';
    }

    public function & displayDetail($layout_def)
    {
        return $this->displayList($layout_def);
    }

    public function displayList(&$layout_def)
    {
        global $app_strings;
        global $image_path;

        $href = 'index.php?module=SecurityGroups'
            . '&action=' . 'SecurityGroupUserRelationshipEdit'
            . '&record=' . $layout_def['fields']['SECURITYGROUP_NONINHERIT_ID']
            . '&return_module=' . $_REQUEST['module']
            . '&return_action=' . 'DetailView'
            . '&return_id=' . $_REQUEST['record'];

        $edit_icon_html = SugarThemeRegistry::current()->getImage('edit_inline', 'align="absmiddle" border="0"', null, null, '.gif', $app_strings['LNK_EDIT']);
        //based on listview since that lets you select records
        if ($layout_def['ListView']) {
            return '<a href="' . $href . '"'
                . 'class="listViewTdToolsS1">' . $edit_icon_html . '&nbsp;' . $app_strings['LNK_EDIT'] .'</a>&nbsp;';
        }
        return '';
    }
}
