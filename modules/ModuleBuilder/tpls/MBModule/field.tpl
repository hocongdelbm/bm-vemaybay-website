<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000"></div>
{literal}
<script>
addForm('popup_form');
</script>
{/literal}

<form name='popup_form' id='popup_form_id' onsubmit='return false;'>
<input type='hidden' name='module' value='ModuleBuilder'>
<input type='hidden' name='action' value='{$action}'>
<input type='hidden' name='new_dropdown' value=''>
<input type='hidden' name='to_pdf' value='true'>
<input type='hidden' name='view_module' value='{$module->name}'>
{if isset($package->name)}
    <input type='hidden' name='view_package' value='{$package->name}'>
{/if}
{if $is_update}
	<input type='hidden' name='is_update' value='true'>
{else}
	<input type='hidden' name='is_update' value='false'>
{/if}
	{if $hideLevel < 5}
	<div class="d-flex align-items-center gap-2">
	    <input type='button' class='btn btn-primary' name='fsavebtn' value='{$mod_strings.LBL_BTN_SAVE}' onclick='{literal}if(validate_type_selection() && check_form("popup_form")){ {/literal}{$preSave} {literal}ModuleBuilder.submitForm("popup_form_id"); }{/literal}'>
	    <input type='button' name='cancelbtn' value='{$mod_strings.LBL_BTN_CANCEL}' onclick='ModuleBuilder.tabPanel.removeTab(ModuleBuilder.findTabById("east"));' class='btn btn-danger'>
	    {if !empty($vardef.name)}
	        {if $hideLevel < 3}
	        {literal}
	          <input type='button' class='btn btn-danger' name='fdeletebtn' value='{/literal}{$mod_strings.LBL_BTN_DELETE}{literal}' onclick='if(confirm("{/literal}{$mod_strings.LBL_CONFIRM_FIELD_DELETE}{literal}")){document.popup_form.action.value="DeleteField";ModuleBuilder.submitForm("popup_form_id");}'>
	        {/literal}
	        {/if}
	        {if !$no_duplicate}
	        {literal}
	     	<input type='button' class='btn btn-secondary' name='fclonebtn' value='{/literal}{$mod_strings.LBL_BTN_CLONE}{literal}' onclick='document.popup_form.action.value="CloneField";ModuleBuilder.submitForm("popup_form_id");'>
	        {/literal}
	    {/if}
	</div>
	{/if}
	{else}
	<div class="d-flex align-items-center gap-2">
	     {literal}
	     <input type='button' class='btn btn-primary' name='lsavebtn' value='{/literal}{$mod_strings.LBL_BTN_SAVE}{literal}' onclick='if(check_form("popup_form")){this.form.action.value = "{/literal}{$action}{literal}";ModuleBuilder.submitForm("popup_form_id")};'>
	     {/literal}
	     {literal}
	     <input type='button' class='btn btn-secondary' name='fclonebtn' value='{/literal}{$mod_strings.LBL_BTN_CLONE}{literal}' onclick='document.popup_form.action.value="CloneField";ModuleBuilder.submitForm("popup_form_id");'>
	     {/literal}
		{literal}
	     <input type='button' class='btn btn-danger' name='cancel' value='{/literal}{$mod_strings.LBL_BTN_CANCEL}{literal}' onclick='ModuleBuilder.tabPanel.get("activeTab").close()'>
		{/literal}
	</div>
{/if}
<hr>

<table width="400px" >
<tr>
    <td class="mbLBL" style="width:92px;">{sugar_translate module="DynamicFields" label="COLUMN_TITLE_DATA_TYPE"}:</td>
    <td >{if empty($vardef.name) && $isClone == 0}
                {html_options name="type" id="type"  options=$field_types selected=$vardef.type onchange='ModuleBuilder.moduleLoadField("", this.options[this.selectedIndex].value);'}
                {sugar_help text=$mod_strings.LBL_POPHELP_FIELD_DATA_TYPE FIXX=250 FIXY=80}
            {else}
                {$field_types[$vardef.type]}
            {/if}
            {if empty($field_types[$vardef.type]) && !empty($vardef.type)}({$vardef.type}){/if}
            <input type='hidden' name='type' value={$vardef.type} />
    </td>
</tr>
</table>
{$fieldLayout}
</form>

<script>
{literal}
function validate_type_selection(){
    var typeSel = document.getElementById('type');
    if(typeSel && typeSel.options){
        if(typeSel.options[typeSel.selectedIndex].value == ''){
            alert('{/literal}{sugar_translate module="DynamicFields" label="ERR_SELECT_FIELD_TYPE"}{literal}');
            return false;
        }
    }
    if (document.getElementById("customTypeValidate")){
        return document.getElementById("customTypeValidate").onchange(); 
    }
    return true;
}
{/literal}
ModuleBuilder.helpSetup('fieldsEditor','{$help_group}');
</script>
{* <script>//Need this to work in FF4. Bug where last script isn't executed.</script> *}
