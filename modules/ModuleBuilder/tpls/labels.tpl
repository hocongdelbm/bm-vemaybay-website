<form name = 'editlabels' id = 'editlabels' onsubmit='return false;'>
<input type='hidden' name='module' value='ModuleBuilder'>
<input type='hidden' name='action' value='saveLabels'>
<input type='hidden' name='view_module' value='{$view_module}'>

{if $view_package}
    <input type='hidden' name='view_package' value='{$view_package}'>
{/if}

<div class="d-flex align-items-center gap-2 mt-2">
    {if $inPropertiesTab}
        <input type='hidden' name='editLayout' value='{$editLayout}'>
    {elseif $mb}
        <input class='btn btn-primary' name = 'saveBtn' id = "saveBtn" type='button' value='{$mod_strings.LBL_BTN_SAVE}' onclick='ModuleBuilder.handleSave("editlabels" );'>
    {else}
        <input class='btn btn-primary' name = 'publishBtn' id = "publishBtn" type='button' value='{$mod_strings.LBL_BTN_SAVEPUBLISH}' onclick='ModuleBuilder.handleSave("editlabels" );'>
        <input class='btn btn-secondary' name = 'renameModBtn' id = "renameModBtn" type='button' value='{$mod_strings.LBL_BTN_RENAME_MODULE}'
        onclick='document.location.href = "index.php?action=wizard&module=Studio&wizard=StudioWizard&option=RenameTabs"'>
    {/if}
    {if !empty($labels_choice)}
        {html_options name='labels' options=$labels_choice selected=$labels_current onchange='this.form.action.value="EditLabels";ModuleBuilder.handleSave("editlabels")'}
    {/if}
</div>

<hr >
<input type='hidden' name='to_pdf' value='1'>

<table class='mbLBL table-edit table-config'>
    <tr>
        <td align="right" style="padding: 0 1em 0 0">
            {$mod_strings.LBL_LANGUAGE}
        </td>
        <td align='left'>
	{html_options name='selected_lang' options=$available_languages selected=$selected_lang onchange='this.form.action.value="EditLabels";ModuleBuilder.handleSave("editlabels")'}
        </td>
	</tr>
    {foreach from=$MOD_LABELS item='label' key='key'}
    <tr>
        <td align="right" style="padding: 0 1em 0 0">{$key}:</td>
        <td><input type='hidden' name='label_{$key}' id='label_{$key}' value='no_change'><input id='input_{$key}' onchange='document.getElementById("label_{$key}").value = this.value; ModuleBuilder.state.isDirty=true;' value='{$label}' size='60'></td>
    </tr>
    {/foreach}

</table>
{if $inPropertiesTab}
    <input class='button' type='button' value='{$APP.LBL_CANCEL_BUTTON_LABEL}' onclick="ModuleBuilder.hidePropertiesTab();">
{/if}
</form>
<script>
    //ModuleBuilder.helpRegisterByID('editlabels', 'a');
    ModuleBuilder.helpRegister('editlabels');
    ModuleBuilder.helpSetup('labelsHelp','default');
</script>
