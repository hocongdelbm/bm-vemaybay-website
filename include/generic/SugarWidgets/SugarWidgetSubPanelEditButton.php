<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

//TODO Rename this to edit link
class SugarWidgetSubPanelEditButton extends SugarWidgetField
{
    protected static $defs = array();
    protected static $edit_icon_html;

    public function displayHeaderCell($layout_def)
    {
        return '';
    }

    public function displayList(&$layout_def)
    {
        global $app_strings;
        global $subpanel_item_count;
        $unique_id = $layout_def['subpanel_id']."_edit_".$subpanel_item_count; //bug 51512

        if ($layout_def['EditView']) {

            // @see SugarWidgetSubPanelTopButtonQuickCreate::get_subpanel_relationship_name()
            $relationship_name = '';
            if (!empty($layout_def['linked_field'])) {
                $relationship_name = $layout_def['linked_field'];
                $bean = BeanFactory::getBean($layout_def['module']);
                if (!empty($bean->field_defs[$relationship_name]['relationship'])) {
                    $relationship_name = $bean->field_defs[$relationship_name]['relationship'];
                }
            }

            $handler = 'subp_nav(\'' . $layout_def['module'] . '\', \'' . $layout_def['fields']['ID'] . '\', \'e\', this';
            if (!empty($relationship_name)) {
                $handler .= ', \'' . $relationship_name . '\'';
            }
            $handler .= ');';

            return '<a href="#" onmouseover="' . $handler . '" onfocus="' . $handler .
                '" class="listViewTdToolsS1" id="' . $unique_id . '">' . $app_strings['LNK_EDIT'] . '</a>';
        }

        return '';
    }
}
