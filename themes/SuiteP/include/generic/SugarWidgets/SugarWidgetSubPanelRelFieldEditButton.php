<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

//TODO Rename this to edit link
class SugarWidgetSubPanelRelFieldEditButton extends SugarWidgetField
{
    public function displayHeaderCell($layout_def)
    {
        return '&nbsp;';
    }

    public function displayList($layout_def)
    {
        die("<pre>" . print_r($layout_def, true) . "</pre>");

        $rel = $layout_def['linked_field'];
        $module = $layout_def['module'];


        global $app_strings;

        $edit_icon_html = SugarThemeRegistry::current()->getImage(
            'edit_inline',
            'align="absmiddle" alt="' . $app_strings['LNK_EDIT'] . '" border="0"'
        );

        $script = "
        function editRel(name, id, module) {
            editRelPanel = new YAHOO.SUGAR.AsyncPanel('rel_edit', {
                width: 500,
                draggable: true,
                close: true,
                constraintoviewport: true,
                fixedcenter: false
            });
            var a = editRelPanel;
			a.setHeader( 'Edit Properties' );
			a.render(document.body);
			a.params = {
                module: 'Relationships',
                action: 'editfields',
                rel_module: module,
                id: id,
                rel: name,
                to_pdf: 1
            };
            a.load('index.php?' + SUGAR.util.paramsToUrl(a.params));
            a.show();
            a.center();
		}";

        return "<script>$script</script>"
             . '<div onclick="editRel(\'p1_b1_accounts\', \'cac203f3-0380-495f-3231-4cf58f089f00\', \'Accounts\')">'
             . $edit_icon_html . "</div>";
    }
}
