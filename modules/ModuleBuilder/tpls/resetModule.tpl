<h3>{sugar_translate label="LBL_REMOVE_CUSTOM"}</h3>
<form name="remove_custom">
<input type="hidden" name="module" value="ModuleBuilder">
<input type="hidden" name="action" value="resetmodule">
<input type="hidden" name="view_module" value="{$module}">
<input type="hidden" name="handle" value="execute">
<ul id="repair_actions">
{foreach from=$actions item='action'}
<li>
    <input type="checkbox" name="{$action.name}" value="{$action.name}" checked="checked" />
    {$action.label}
</li> 
{/foreach}
</ul>
</form>
<button id="execute_repair" onclick="this.disabled = true;
ajaxStatus.showStatus(SUGAR.language.get('ModuleBuilder', 'LBL_AJAX_LOADING'));
ModuleBuilder.submitForm('remove_custom')">{sugar_translate label="LBL_RESET"}</button>
